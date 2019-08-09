<?php

class Ac_Etl_Table extends Ac_Prototyped {

    var $id = false;
    
    var $sqlTableName = false;
    
    var $restriction = array();
    
    var $tableGroupId = '';
    
    /**
     * @var Ac_Etl_Import
     */
    protected $import = false;
    
    protected $dbiTable = false;
    
    protected $maxLengths = false;

    protected $loadedRecords = array();
    
    var $loadedChunkSize = 3000;
    
    protected $loadData = 0;
    
    protected $checkOnly = false;
    
    protected $defaults = array();
    
    protected $hasLineNo = null;
    
    function setDefaults(array $defaults) {
        $this->defaults = $defaults;
    }

    function getDefaults() {
        return $this->defaults;
    }    
    
    function beginLoadData($checkOnly = false) {
        //if ($this->loadData) throw new Exception("beginLoadData() was already called without corresponding endLoadData()");
        $checkOnly = (bool) $checkOnly;
        if ($this->loadData && $this->checkOnly !== $checkOnly) 
            throw new Exception("Cannot change \$checkOnly value before endLoadData()");
        $this->checkOnly = $checkOnly;        
        $this->loadData++;
    }
    
    function endLoadData() {
        if (!$this->loadData) throw new Exception("Call to endLoadData() without former beginLoadData()");
        $this->loadData--;
        if (!$this->loadData) $this->write();
    }
    
    function appendRecords(array $records, $lineNo = false) {
        foreach ($records as $record) {
            $this->appendRecord($record, $lineNo);
            if ($lineNo !== false) $lineNo++;
        }
    }
    
    function appendRecord($record, $lineNo = false) {
        if (!$this->loadData) $this->beginLoadData();
        if ($lineNo !== false && !isset($record['lineNo']))
            $record['lineNo'] = $lineNo;
        if (!$this->hasLineNo()) unset($record['lineNo']);
        $this->loadedRecords[] = $record;
        if (count($this->loadedRecords) >= $this->loadedChunkSize) $this->write();
    }
    
    protected function write() {
        if ($this->loadedRecords) {
            $log = new Ac_Etl_Log_Item("Table '{$this->id}'.write(): writing ".count($this->loadedRecords)." items", "debug", array('chrono'), array(), true);
            $errors = $this->getTmpDataErrors($this->loadedRecords);
            $okRecords = $this->loadedRecords;
            if ($errors) foreach ($errors as $k => $err) {
                $lineNo = isset($this->loadedRecords[$k]['lineNo'])? $this->loadedRecords[$k]['lineNo'] : false;
                foreach ($err as $col => $msg) {
                    $item = new Ac_Etl_Log_Item($msg, 'error', array('error/data'), array('lineNo' => $lineNo, 'col' => $col));
                    $this->import->logItem($item);
                }
                unset($okRecords[$k]);
            }
            if ($okRecords) $this->writeTmpData ($okRecords);
            if (($okCount = count($okRecords)) != count($this->loadedRecords)) {
                $log->message .= " (actually wrote $okCount records)";
            }
            $this->import->logItem($log);
            $this->loadedRecords = array();
        }
    }
    
    function hasPublicVars() {
        return true;
    }

    function setImport(Ac_Etl_Import $import) {
        $this->import = $import;
    }
    
    /**
     * @return Ac_Etl_Import
     */
    function getImport() {
        return $this->import;
    }
    
    /**
     * @return Ac_Sql_Dbi_Table
     */
    function getImporterDbiTable() {
        if ($this->dbiTable === false) {
            $this->dbiTable = $this->import->getImporterDbi()->getTable($this->sqlTableName);
        }
        return $this->dbiTable;
    }
    
    function hasLineNo() {
        if ($this->hasLineNo === null) {
            $this->hasLineNo = in_array('lineNo', $this->getImporterDbiTable()->listColumns());
        }
        return $this->hasLineNo;
    }
    
    function getMaxLengths() {
        if ($this->maxLengths === false) {
            $this->maxLengths = array();
            foreach ($this->getImporterDbiTable()->listColumns() as $name) {
                $width = $this->getImporterDbiTable()->getColumn($name)->width;
                if (!strlen($width)) $width = false;
                $this->maxLengths[$name] = $width;
            }
        }
        return ($this->maxLengths);
    }

    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        return $this->getImport()->getDb();
    }
    
    function getTmpDataErrors(array $records) {
        $res = array();
        $t = $this->getImporterDbiTable();
        $ml = $this->getMaxLengths();
        foreach ($records as $k => $v) {
            if ($this->hasLineNo() && !isset($v['lineNo'])) $res[$k]['lineNo'] = 'Missing lineNo column';
            foreach ($v as $col => $val) {
                if (!isset($ml[$col])) $res[$k][$col] = "No such column in importer table {$this->id}: {$col}";
                elseif ($ml[$col] && mb_strlen(''.$val, 'utf-8') > $ml[$col]) {
                    $res[$k][$col]['maxLength'] = "Max length exceeded ({$ml[$col]})";
                }
            }
        } 
        return $res;
    }
    
    protected function writeTmpData(array $records) {
        foreach ($records as & $record) {
            $record['importId'] = $this->import->getImportId(true);
            if ($this->restriction) Ac_Util::ms($record, $this->restriction);
            if ($this->defaults) $record = array_merge($this->defaults, $record);
        }
        $sql = $this->getDb()->insertStatement($this->tableName('object'), $this->getDb()->unifyInsertData($records), true);
        if (!$this->checkOnly) $res = $this->getDb()->query("-- tags: load/insert/{$this->id}\n".$sql);
            else {
                $this->import->appendSql($sql);
                $res = true;
            }
        return $res;
    }
    
    function cleanTmpData($wholeTable = false, $forAllImports = false) {
        
        $log = new Ac_Etl_Log_Item("Table '{$this->id}': cleaning tmp data ".($wholeTable? " for whole table " : "").($forAllImports? ", all imports" : ""),
            'debug', array('chrono'), array(), true);
        $crit = array();
        if (!$wholeTable) $crit = $this->restriction;
        if (!$forAllImports) $crit['importId'] = $this->import->getImportId (true);
        $res = $this->getDb()->query(
            "-- tags: load/clear/{$this->id}\n".
            'DELETE FROM '.$this->tableName('string').($crit? ' WHERE '.$this->getDb()->valueCriterion($crit) : ''));
        $this->import->logItem($log);
        return $res;
    }
    
    function tableName($kind = 'array') {
        $res = $this->import->tableOfImporterDb($this->sqlTableName, $kind);
        return $res;
    }
    
    function getCheckOnly() {
        return $this->checkOnly;
    }
    
    function getLoadData() {
        return $this->loadData;
    }
    
}
