<?php

class Ac_Etl_Operation_Batch extends Ac_Etl_Operation {
    
    protected $callback = false;
    
    protected $recordsPerTurn = 1000;
    
    protected $recordsPerWrite = false;
    
    protected $keyColumns = false;
    
    protected $tableIsTarget = null;
    
    protected $sqlTableName = false;

    /**
     * @var bool
     */
    protected $useInsertToWriteBack = true;
    
    protected $update = array();
    
    protected $hangProtection = true;
    
    protected $callbackLog = false;
    
    protected $nRecords = 0;
    
    protected $useOffset = false;
    
    /**
     * Write records using series of UPDATE statements (SLOW)
     */
    const updateMethodUpdate = 'update';
    
    /**
     * Write records using REPLACE statement
     */
    const updateMethodReplace = 'replace';
    
    /**
     * Write records using INSERT...ON DUPLICATE KEY UPDATE method (fastest)
     */
    const updateMethodInsertUpdate = 'insertUpdate';
    
    protected $updateMethod = self::updateMethodInsertUpdate;
    
    /**
     * Table to work on
     * 
     * Either internal tableId or dest table SQL name
     * (depends on getTableIsTarget())
     * 
     * @param string $tableId
     */
    function setTableId($tableId) {
        parent::setTableId($tableId);
    }

    function getTableId() {
        return parent::getTableId();
    }    

    /**
     * Whether $this->tableId property means SQL name of destination table
     * @param type $tableIsTarget
     */
    function setTableIsTarget($tableIsTarget) {
        $this->tableIsTarget = (bool) $tableIsTarget;
    }

    /**
     * Whether $this->tableId property means SQL name of destination table
     * 
     * If setTableIsTarget() is not called, will try to guess - if there is table with
     * given ID in current importer, will return FALSE, else will return TRUE
     * @return bool
     */
    function getTableIsTarget() {
        if ($this->tableIsTarget === null) {
            if (strlen($this->sqlTableName)) $res = true;
            else $res = !in_array($this->tableId, array_keys($this->import->getTables()));
            return $res;
        }
        return $this->tableIsTarget;
    }    
    
    function setCallback($callback) {
        if ($callback !== false) {
            if (!($callback instanceof Ac_Etl_I_BatchCallback || is_callable($callback))) {
                throw new Exception("\$callback should be either false, a callable or Ac_Etl_I_BatchCallback");
            }
        }
        $this->callback = $callback;
    }

    function getCallback() {
        return $this->callback;
    }    
    
    function setRecordsPerTurn($recordsPerTurn) {
        if (!is_numeric($recordsPerTurn) || ((int) $recordsPerTurn < 0)) throw new Exception("\$recordPerTurn should be a number greater than or equal to zero");
        $this->recordsPerTurn = (int) $recordsPerTurn;
    }

    function getRecordsPerTurn() {
        return $this->recordsPerTurn;
    }    

    /**
     * Number of records in the write group
     * Max is whole group (usually will not have more records than in read group)
     * FALSE defaults to $recordsPerTurn
     * 
     * @param int|false $recordsPerWrite
     * @throws Exception
     */
    function setRecordsPerWrite($recordsPerWrite) {
        if ($recordsPerWrite !== false) {
            if (!(is_numeric($recordsPerWrite) && ((int) $recordsPerWrite >= 0))) {
                throw new Exception("\$recordPerWrite should be either FALSE or a number greater than or equal to 0");
            }
            $recordsPerWrite = (int) $recordsPerWrite;
        }
        $this->recordsPerWrite = $recordsPerWrite;
    }

    function getRecordsPerWrite() {
        return $this->recordsPerWrite;
    }
    
    function setKeyColumns($keyColumns) {
        if ($keyColumns !== false) $keyColumns = Ac_Util::toArray ($keyColumns);
        $this->keyColumns = $keyColumns;
    }

    function getKeyColumns($withAlias = false) {
        if ($this->keyColumns === false) {
            if ($this->getTableIsTarget()) {
                $dbi = $this->import->getTargetDbi();
                $dbiTable = $dbi->getTable($this->sqlTableName);
                $this->keyColumns = $dbiTable->listPkFields();
            } else {
                $t = $this->import->getTable($this->tableId);
                $dbi = $this->import->getImporterDbi();
                $dbiTable = $dbi->getTable($t->sqlTableName);
                $this->keyColumns = $dbiTable->listPkFields();
            }
        }
        if ($withAlias) {
            $res = array();
            foreach ($this->keyColumns as $col) {
                $res[] = $this->getDb()->n(array('t', $col));
            }
        } else {
            $res = $this->keyColumns;
        }
        return $res;
    }
    
    /**
     * Field => value pairs to be applied before callback runs
     * @param array $update
     */
    function setUpdate(array $update) {
        $this->update = $update;
    }

    /**
     * Field => value pairs to be applied before callback runs
     * @return array
     */
    function getUpdate() {
        return $this->update;
    }

    /**
     * Whether Exception will be raised if records-to-process number will not change after batch cycle
     * @param bool $hangProtection
     */
    function setHangProtection($hangProtection) {
        $this->hangProtection = $hangProtection;
    }

    /**
     * Whether Exception will be raised if records-to-process number will not change after batch cycle
     * @return bool
     */
    function getHangProtection() {
        return $this->hangProtection;
    }    
    
    /**
     * Sets method used to write records back
     * Should be one of self::updateMethodUpdate, self::updateMethodReplace or self::updateMethodInsertUpdate
     * @param type $updateMethod
     */
    function setUpdateMethod($updateMethod) {
        if (!in_array($updateMethod, $a = array(self::updateMethodInsertUpdate, self::updateMethodReplace, self::updateMethodUpdate))) {
            throw new Exception("\$updateMethod should be one of ".implode("/", $a)." values");
        }
        $this->updateMethod = $updateMethod;
    }

    /**
     * Returns method used to write records back
     * Will be one of self::updateMethodUpdate, self::updateMethodReplace or self::updateMethodInsertUpdate
     */
    function getUpdateMethod() {
        return $this->updateMethod;
    }

    function doProcess() {
        $this->getDb();
        
        $select = new Ac_Sql_Select($this->db, $this->getSelectPrototype(true));
        
        $select->distinct = true;
        if (!$select->columns) $select->columns = 't.*';
        
        $num = false;
        
        if ($this->recordsPerTurn && $this->hangProtection) {
            $numSelect = new Ac_Sql_Select($this->db, $this->getSelectPrototype(true));
            $numSelect->columns = 'COUNT(DISTINCT '.implode(", ", $this->getKeyColumns(true)).')';
        }

        //if ($this->useOffset) $max = $this->db->fetchValue ("SELECT COUNT(*) FROM (".$select.")");
        
        if ($this->recordsPerTurn) $select->limitCount = $this->recordsPerTurn;
        
        $idPath = $this->getIdPath();
        
        $nRecords = 0;
        
        do {
            
            $finish = false;
            $this->db->setNextTags(array("operations/{$idPath}/fetch"));
            $records = $this->db->fetchArray($select);
            
            if ($records) $this->processRecords($records);
            $nRecords += count($records);
            
            if ($this->recordsPerTurn) {
                
                if (!$records) {
                    
                    $finish = true;
                } else {
                    if ($this->hangProtection) {
                        $oldNum = $num;
                        
                        $this->db->setNextTags(array("operations/{$idPath}/stats/recordsLeft"));
                        $num = $this->db->fetchValue($numSelect);
                        if ($oldNum !== false && $oldNum == $num) {
                            throw new Exception("Number of to-process records didn't change after record processing of the group");
                        }
                        $this->import->logItem(new Ac_Etl_Log_Item("{$num} records left to process", 'debug', array('chrono')));
                        if (!$num) $finish = true;
                    }
                }
                
            } else {
                $finish = true;
            }
            
            if ($this->useOffset) $select->limitOffset += $this->recordsPerTurn;
            
            
        } while (!$finish);
        
        if ($this->callbackLog) {
            $this->callbackLog->message .= '; records: '.$nRecords.';'
                .' T/record='.(round($this->callbackLog->spentTime/$nRecords, 4))
                .'; M/record='.round($this->callbackLog->spentMemory/$nRecords, 4);
            $this->import->logItem($this->callbackLog);
            $this->callbackLog = false;
        }
        
    }
    
    protected function processRecords ($records) {
        $hashes = array();
        $idPath = $this->getIdPath();
        foreach ($records as $i => $record) {
            $hashes[$i] = md5(serialize($record));
            foreach ($this->update as $k => $v) $record[$k] = $v;
        }
        if ($this->callback) {
            if (!$this->callbackLog) {
                $this->callbackLog = new Ac_Etl_Log_Item("Callback processing", 'profile', array("operations/{$idPath}/callback"));
            }
            $this->callbackLog->beginProfiling();
            if ($this->callback instanceof Ac_Etl_I_BatchCallback) {
                $this->callback->modifyRecords($records, $this);
            } else {
                call_user_func($this->callback, $records, $this);
            }
            $this->callbackLog->endProfiling();
        }
        foreach ($records as $i => $record) {
            if (md5(serialize($record)) == $hashes[$i]) unset($records[$i]);
        }
        $recordsPerWrite = $this->recordsPerWrite;
        // Should I write to the log if not all records are changed?
        if ($recordsPerWrite === false) $recordsPerWrite = $this->recordsPerTurn;
        elseif (!$recordsPerWrite) $recordPerWrite = count($records);
        while(count($r = array_splice($records, 0, $recordsPerWrite))) {
            switch ($this->updateMethod) {
                case self::updateMethodInsertUpdate:
                    $this->writeInsertUpdate($r); break;
                case self::updateMethodReplace:
                    $this->writeReplace($r); break;
                case self::updateMethodUpdate:
                    $this->writeUpdate($r); break;
            }
        }
    }
    
    protected function writeInsertUpdate(array $records) {
        $records = $this->db->unifyInsertData($records);
        $ff = array_keys($records[0]);
        $uCols = array();
        foreach ($ff as $f) $uCols[] = $this->db->n($f)." = VALUES(".$this->db->n($f).")";
        $idPath = $this->getIdPath();
        $query = $this->db->insertStatement($this->getDestSqlName(), $records, true)." ON DUPLICATE KEY UPDATE ".implode(", ", $uCols);
        $this->db->setNextTags(array("operations/{$idPath}/modify"));
        $this->db->query($query);
        $this->addAffected("modify");
    }
    
    protected function writeReplace(array $records) {
        $records = $this->db->unifyInsertData($records);
        $query = $this->db->insertStatement($this->getDestSqlName(), $records, true);
        $idPath = $this->getIdPath();
        $this->db->setNextTags(array("operations/{$idPath}/modify"));
        $this->db->query($query);
        $this->addAffected("modify");
    }
    
    protected function writeUpdate(array $records) {
        $s = $this->getDestSqlName();
        $k = $this->getKeyColumns();
        $idPath = $this->getIdPath();
        foreach ($records as $record) {
            $query = $this->db->updateStatement($s, $record, $k);
            $this->db->setNextTags(array("operations/{$idPath}/modify"));
            $this->db->query($query);
            $this->addAffected("modify");
        }
    }
    
    protected function getDestSqlName() {
        if ($this->getTableIsTarget()) $res = $this->import->tableOfTargetDb (strlen($this->sqlTableName)? $this->sqlTableName : $this->tableId, 'array');
        else $res = $this->import->tableOfImporterDb($this->import->getTable($this->tableId)->sqlTableName, 'array');
        return $res;
    }
    
   
    function getSelectPrototype($full = true, $alias = 't') {
        
        if (!$full || !$this->getTableIsTarget()) $res = parent::getSelectPrototype ($full, $alias);
        else {
            $res = $this->selectPrototype;
            $res['tables'][$alias]['name'] = $this->getDestSqlName();
        }
        return $res;
    }

    function setSqlTableName($sqlTableName) {
        $this->sqlTableName = $sqlTableName;
    }

    function getSqlTableName() {
        return $this->sqlTableName;
    }    
 
    function setUseOffset($useOffset) {
        $this->useOffset = $useOffset;
    }

    function getUseOffset() {
        return $this->useOffset;
    }    
   
    
}