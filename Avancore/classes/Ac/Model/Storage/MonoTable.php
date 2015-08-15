<?php

class Ac_Model_Storage_MonoTable extends Ac_Model_Storage_Sql {
    
    /**
     * name of SQL table that contains records
     */
    protected $tableName = false;

    /**
     * class of the record
     */
    protected $recordClass = false;

    /**
     * primary key of the table
     */
    protected $primaryKey = false;

    /**
     * name of auto-increment table field
     */
    protected $autoincFieldName = false;
    
    /**
     * @var array
     */
    protected $sqlColumns = false;

    /**
     * Sets name of SQL table that contains records
     */
    function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    /**
     * Returns name of SQL table that contains records
     */
    function getTableName() {
        return $this->tableName;
    }
    
    function getIdentifier($object) {
        $res = $object->{$this->primaryKey};
        return $res;
    }

    /**
     * Sets class of the record
     */
    function setRecordClass($recordClass) {
        $this->recordClass = $recordClass;
    }

    /**
     * Returns class of the record
     */
    function getRecordClass() {
        return $this->recordClass;
    }

    /**
     * Sets primary key of the table
     */
    function setPrimaryKey($primaryKey) {
        $this->primaryKey = $primaryKey;
    }

    /**
     * Returns primary key of the table
     */
    function getPrimaryKey() {
        return $this->primaryKey;
    }

    /**
     * Sets name of auto-increment table field
     */
    function setAutoincFieldName($autoincFieldName) {
        $this->autoincFieldName = $autoincFieldName;
    }

    /**
     * Returns name of auto-increment table field
     */
    function getAutoincFieldName() {
        return $this->autoincFieldName;
    }
    
    function createRecord($typeId = false) {
        if ($typeId !== false)
            throw new Ac_E_InvalidCall("Only default \$typeId (FALSE) "
                . "is supported by ".__CLASS__."; '$typeId' given");
        $className = $this->recordClass;
        $res = new $className($this->mapper);
        return $res;
    }
    
    function listRecords() {
        $ord = $this->mapper->getDefaultOrdering();
        if (strlen($ord)) $orderClause = "\nORDER BY $ord";
        else $orderClause = "";
        $res = $this->db->fetchColumn(
            "SELECT ".$this->db->n($this->primaryKey)
            ."\nFROM ".$this->db->n($this->tableName)
            .$orderClause
        );
        return $res;
    }
    
    function recordExists($idOrIds) {
        $res = (int) $this->db->fetchValue("SELECT COUNT(".$this->db->n($this->primaryKey).") FROM ".$this->db->n($this->tableName)." WHERE $this->primaryKey ".$this->db->eqCriterion($idOrIds));
        return $res;
    }
    
    function loadRecord($id) {
        $sql = "SELECT * FROM ".$this->db->n($this->tableName)." WHERE ".$this->db->n($this->primaryKey)." = ".$this->db->q($id)." LIMIT 1";
        $rows = $this->db->fetchArray($sql);
        if (count($rows)) {
            $objects = $this->loadFromRows($rows);
            $res = array_pop($objects);
        }
        return $res;
    }
    
    function loadRecordsArray(array $ids) {
        $where = $this->db->n($this->primaryKey)." ".$this->db->eqCriterion($ids);
        $res = $this->loadRecordsByCriteria($where, true);
        return $res;
    }
    
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        $arr = $this->loadRecordsByCriteria($where, $order, $joins, $limitOffset, 1, $tableAlias);
        if (count($arr)) $res = array_shift($arr);
            else $res = null;
        return $res;
    }
    
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        $arr = $this->loadRecordsByCriteria($where, $order, $joins, $limitOffset, 2, $tableAlias);
        if (count($arr) == 1) $res = array_shift($arr);
            else $res = null;
        return $res;
    }
    
    function loadRecordsByCriteria($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        $sql = "SELECT ".$this->db->n($this->tableName).".* FROM ".$this->db->n($this->tableName)." $joins  ";
        if ($where) {
            if (is_array($where)) $where = $this->db->valueCriterion($where);
            $sql .= " WHERE ".$where;
        }
        if ($order) $sql .= " ORDER BY ".$order;
        if (is_numeric($limitCount) && !is_numeric($limitOffset)) $limitOffset = false;
        if (is_numeric($limitCount)) {
            $sql = $this->db->applyLimits($sql, $limitCount, $limitOffset, strlen($order)? $order : false);
        }
        $res = $this->loadFromRows($this->db->fetchArray($sql));
        return $res;
    }
    
    function groupRowsByIdentifiers(array $rows) {
        $pkData = array();
        $uniqueRows = array();
        foreach ($rows as $i => $row) {
            $pk = $row[$this->primaryKey];
            $pkData[$i] = $pk;
            if (!isset($uniqueRows[$pk])) {
                $uniqueRows[$pk] = $row;
            }
        }
        return array($pkData, $uniqueRows);
    }
    
    protected function ungroupRowsByIdentifiers(array $pkData, array $records) {
        $res = array();
        foreach ($pkData as $id => $pk) {
            $res[$id] = $records[$pk];
        }
        return $res;
    }
    
    function peConvertForLoad($object, $hyData) {
        $res = $hyData;
        $d = $this->db->getDialect();
        if ($d->hasToConvertDatesOnLoad()) {
            $res = $this->convertDates($oid, $this->getDateFormats()); 
        }
        return $res;
    }
    
    function peConvertForSave($object, $hyData) {
        $res = $hyData;
        $d = $this->db->getDialect();
        $df = $this->getDateFormats();
        if ($df) {
            foreach ($df as $prop => $type) {
                if (array_key_exists($prop, $res)) {
                    $res[$prop] = $d->convertDateForStore($res[$prop], $type);
                }
            }
        }
        return $res;
    }
    
    function peReplaceNNRecords($object, $rowProto, $rows, $midTableName, & $errors = array()) {
        $res = true;
        if (count($rowProto)) {
            $sqlDb = $this->db;
            if (!$sqlDb->query('DELETE FROM '.$sqlDb->n($midTableName).' WHERE '.$sqlDb->valueCriterion($rowProto))) {
                $errors['idsDelete'] = 'Cannot clear link records';
                $res = false;
            }
            if (count($rows)) {
                if (!$sqlDb->query($sqlDb->insertStatement($midTableName, $rows, true))) {
                    $errors['idsInsert'] = 'Cannot store link records';
                    $res = false;
                }
            }
        }
        return $res;
    }
    
    function peLoad($object, $identifier, & $error = null) {
        $sql = 'SELECT * FROM '.$this->db->n($this->tableName).' WHERE '.$this->primaryKey.' = '.$this->db->q($identifier);
        $res = $this->db->fetchRow($sql);
        return $res;
    }
    
    function peSave($object, & $hyData, & $exists = null, & $error = null, & $newData = array()) {
        // leave only existing columns
        $dataToSave = array_intersect_key($hyData, array_flip($this->listSqlColumns()));
        
        if (is_null($exists)) $exists = array_key_exists($this->primaryKey, $dataToSave);
        
        if ($exists) {
            $query = $this->db->updateStatement($this->tableName, $dataToSave, $this->primaryKey, false);
            if ($this->db->query($query) !== false) $res = $dataToSave;
            else {
                $descr = $this->db->getErrorDescr();
                if (is_array($descr)) $descr = implode("; ", $descr);
                $error = $this->db->getErrorCode().': '.$descr;
                $res = false;
            }
        } else {
            $query = $this->db->insertStatement($this->tableName, $dataToSave);
            if ($this->db->query($query)) {
                if (strlen($ai = $this->getAutoincFieldName()) && !isset($dataToSave[$ai])) {
                    if (!is_array($newData)) $newData = array();
                    $newData[$ai] = $this->getLastGeneratedId();
                }
                $res = true;
            } else {
                $descr = $this->db->getErrorDescr();
                if (is_array($descr)) $descr = implode("; ", $descr);
                $error = $this->db->getErrorCode().':'.$descr;
                $res = false;
            }
        }
        return $res;
    }
    
    protected function getLastGeneratedId() {
        return $this->db->getLastInsertId();
    }
    
    /**
     * @param type $hyData 
     * @return bool
     */
    function peDelete($object, $hyData, & $error = null) {
        $key = $hyData[$this->primaryKey];
        $res = (bool) $this->db->query($sql = "DELETE FROM ".$this->db->n($this->tableName)." WHERE "
            .$this->db->n($this->primaryKey)." ".$this->db->eqCriterion($key));
        return $res;
    }

    function setSqlColumns(array $sqlColumns) {
        $this->sqlColumns = $sqlColumns;
    }

    /**
     * @return array
     */
    function getSqlColumns() {
        return $this->sqlColumns;
    }    
    
    function listSqlColumns() {
        return $this->getSqlColumns();
    }
    
}