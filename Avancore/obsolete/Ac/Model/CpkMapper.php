<?php

/**
 * Base class of mapper for models that have compound primary key
 */
class Ac_Model_CpkMapper extends Ac_Model_Mapper {
    
    /**
     * Pk fields with quotes
     * @var array
     */
    var $_qPk = array();
    
    function Ac_Model_CpkMapper($tableName, $recordClass, $pk) {
        parent::Ac_Model_Mapper($tableName, $recordClass, $pk);
        foreach ($pk as $f) $this->_qPk[] = $this->database->NameQuote($f);
    }
    
    function listRecords() {
        $sql = "SELECT ".implode(", ", $this->_qPk)." FROM `$this->tableName`";
        $this->database->setQuery($sql);
        $res = $this->database->loadAssocList();
        return $res;
    }
    
    /**
     * @param single- or multi-dimensional array with pk or pks; returns count of records that exist in the database;
     */
    function recordExists($pks) {
        if (!is_array($pks)) trigger_error('Compound primary key can be given only as an array', E_USER_ERROR);
        
        // One key given? Make an array of key with one element
        if (!is_array($pks[0])) $pks = array($pks);
        
        if ($pks) { 
            $sql = "SELECT COUNT(*) FROM `$this->tableName` WHERE ".$this->_pksCriterias($pks);
            $this->database->setQuery($sql);
            $res = intval($this->database->loadResult());
        } else $res = false;
        return $res;
    }
    
    
    /**
     * Loads record(s) -- id can be key or an array of keys
     */
    function loadRecord($pk) {
        $res = null;
        if (!is_array($pk)) trigger_error('Compound primary key can be given only as an array', E_USER_ERROR);
        $pk = array_slice($pk);
        if (is_array($pk[0])) {
            if (count($pk) == 1) { $many = false; $pk = $pk[0]; }
            else $many = true;
        } else $many = false;
        if (!$many) {
            if ($this->useRecordsCollection && ($res = $this->getFromArrayByPk($this->_recordsCollection, $pk))) {
            } else {
                $sql = "SELECT * FROM {$this->tableName} WHERE ".$this->_pkCriteria($pk);
                $this->database->setQuery($sql);                
                $rows = $this->database->loadAssocList();
                if ($rows && $arrayRow = $rows[0]) {
                    $classPath = $this->getRecordClass ($arrayRow);
                    $className = $classPath;
                    $record = new $className();
                    $record->load ($arrayRow, null, true);
                } else {
                    $record = null;
                }
                $res = $record;
                if ($this->useRecordsCollection && $res) $this->putToArrayByPk($record, $this->_recordsCollection);
            }
        } else {
            $res = $this->loadRecordsArray($pk);
        }
        return $res;
    }
    
    /**
     * @param array $pks - Array of record primary keys
     */
    function loadRecordsArray($pks, $keysToList = false) {
        $pks = array_slice($pks);
        if ($pks) { 
            $where = $this->_pksCriterias($pks);
            $res = $this->loadRecordsByCriteria($where, $keysToList);
        } else {
            $res = array();
        }
        return $res;
    }
    
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        $sql = "SELECT `$this->tableName`.* FROM `$this->tableName` $joins  ";
        if ($where) $sql .= " WHERE ".$where;
        if ($order) $sql .= " ORDER BY ".$order;
        if (is_numeric($limitCount) && !is_numeric($limitOffset)) $limitOffet = 0;
        if (is_numeric($limitCount)) {
            $sql .= " LIMIT ";
            if (is_numeric($limitOffset)) $sql .= $limitOffset. ", ";
            $sql .= $limitCount;
        }
        
        $this->database->setQuery($sql);
        //$objectList = $this->database->loadAssocList();
        
        $result = $this->database->getResultResource();

        $res =  array();
        
        while($row = $this->database->fetchAssoc($result)) {
            if ($this->useRecordsCollection && ($res = $this->getFromArrayByPk($this->_recordsCollection, $pk))) {
            } else {
                $className = $this->dispatcher->loadClass($this->getRecordClass($row));
                $rec = new $className ($this->database);
                $rec->load($row, null, true);
                if ($this->useRecordsCollection) $this->putToArrayByPk($rec, $this->_recordsCollection);
            }
            
            if ($keysToList) {
                $this->putToArrayByPk($rec, $res);
            } else {
                $res[] = $rec;
            }
        }
        $this->database->freeResultResource($result);
        
        return $res;
    }
    
    // --------------------- Functions that work with keys and indices -------------------
    
    function getKeysCriterion($keys, $tableAlias = false, $default = '0') {
        if (!is_array($keys)) trigger_error ("Compound primary key can be given as array only", E_USER_ERROR);
        if (!count($keys)) return $default;
        $keys = array_slice($keys);
        if (!is_array($keys[0])) $keys = array($keys);
        $fieldNames = $this->_qPk;
        if ($tableAlias !== false) foreach ($fieldNames as $k => $n) $fieldNames[$k] = $this->database->NameQuote($tableAlias).'.'.$n;
        $crits = array();
        $c = count($this->pk);
        foreach ($keys as $k) {
            $cr = array();
            $k = array_slice($k);
            for ($i = 0; $i < $c; $i++) if ($k[$i] !== false) $cr[] = $fieldNames[$i].' = '.$this->database->Quote($k[$i]);
            if (count($cr) < $c) trigger_error ("Partial compound PK given: only ".count($cr)." of $c parts provided", E_USER_ERROR);
            $crits[] = implode(' AND ', $cr);            
        }
        $res = '('.implode(') OR (', $crits).')';
        return $res;
    }

    // -------------------------- supplementary private functions ----------------------------
    
    function _pkCriteria($pkValue) {
        $c = count($this->pk);
        $pkv = array_slice($pkValue, $c);
        if (count($pkValue) < $c) trigger_error('Full primary key must be given, only '.count($pkValue).' of '.$c.' values provided', E_USER_ERROR);
        $res = $this->_qPk[0].'='.$this->database->Quote($pkv[0]);
        for ($i = 1; $i < $c; $i++) {
            if ($pkv[$i] === false) trigger_error ('Primary key part(s) cannot be FALSE, but '.$this->pk[$i].' is', E_USER_ERROR); 
            $res.= ' AND '.$this->_qPk[$i].'='.$this->database->Quote($pkv[$i]);
        }
        return $res;
    }
    
    function _pksCriterias($pksValues) {
        $c = count($pksValues);
        if (!$c) $res = '1'; else {
            $pksv = array_slice($pksValues);
            $res = '('.$this->_pkCriteria($pksv[0]).')';
            for ($i = 0; $i < $c; $i++) {
                $res .= ' OR ('.$this->_pkCriteria($pksv[$i]).')';
            }
        }
        return $res;
    }
    
    
    // ----------------- Functions that work with arrays of records -------------

    /**
     * @param Ac_Model_Object $record
     * @param array $dest
     */
    function putToArrayByPk(& $record, $dest) {
        Ac_Util::simpleSetArrayByPath($dest, $record->getPrimaryKey(), $record, true);
    }
    
    /**
     * @param array $src
     * @param array $pk
     * @return Ac_Model_Object or $default if it is not found
     */
    function getFromArrayByPk($src, $pk, $default = null) {
        $res = Ac_Util::simpleGetArrayByPath($src, $pk, $default);
        return $res;        
    }
    
    /**
     * @param array $src
     * @return array(array($pk1, & $rec1), array($pk2, & $rec2), ...) 
     */
    function getFlatArrayWithPks($src) {
        $res = array();
        Ac_Model_CpkMapper::_iterateRecursive($res, $src, array(), count($this->pk));
        return $res;
    }
    
    function _iterateRecursive(& $res, $src, $prefix, $height) {
        $height--;
        if ($height <= 0) {
             foreach(array_keys($src) as $k) {
                 $fullKey = $prefix;
                 $fullKey[] = $k;
                 $res[] = array($fullKey, & $src[$k]);
             }
        } else {
            foreach(array_keys($src) as $k) {
                $partialKey = $prefix;
                $partialKey[] = $k;
                Ac_Model_CpkMapper::_iterateRecursive($res, $src[$k], $partialKey, $height);
            }
        }
    }
     
}

?>