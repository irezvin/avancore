<?php

/**
 * Base class of models that have compound primary key
 */
class Ae_Model_CpkObject extends Ae_Model_Object {
    
    function getPrimaryKey() {
        $res = array();
        foreach ($this->_pk as $fieldName) $res[] = $this->$fieldName;
        return $res;
    }

    function _legacyLoad($oid, $prefix) {
        if ($oid !== null) {
            if (!is_array($oid) || count($oid) !== count($this->_pk)) 
                trigger_error("Primary key must be an array with ".count($this->_pk)." elements", E_USER_ERROR);
            $this->_setPk($oid);
        } else {
            $oid = $this->getPrimaryKey();    
        }

        if (!$this->_isFullPk($oid)) {
            return false;
        }
        
        $this->reset();
        
        $query = "SELECT * FROM {$this->_tableName} WHERE ".$this->_pkCrit($oid);
        
        $this->_db->setQuery( $query );
        $props = $this->listOwnProperties();
        $rows = $this->_db->loadAssocList();
        if (count($rows)) {
            $res = true;
            $row = $rows[0];
            foreach ($this->listOwnProperties() as $propName) {
                if (isset($row[$propName])) {
                    $this->$propName = $row[$propName];
                }
            }
        } else {
            $res = false;
        }
        return $res;
    }
    
    function _legacyStore($updateNulls) {
        
        $replace = false;
        $useAutoinc = false;
        $canStore = true;
        
        $pk = $this->getPrimaryKey();
        $c = count($this->_pk);
        $missingPk = array();
        
        for ($i = 0; $i < $c; $i++) {
            if ($pk[$i] === false) $missingPk[] = $this->_pk[$i];
        }
        
        if (!$missingPk) $replace = true;
        else {
            $replace = false;
            $mapper = & $this->getMapper();
            if ((count($missingPk) == 1) && $missingPk[0] === $mapper->getAutoincFieldName()) $useAutoinc = $mapper->getAutoincFieldName();
            else $canStore = false;
        }
        
        if ($canStore) {
            if ($replace) {
                $kv = array();
                foreach ($this->listOwnProperties() as $propName) {
                    if (($propVal = $this->$propName) !== false) $kv[] = $this->_db->NameQuote($propName).'='.$this->_db->Quote($propVal);
                }
                $sql = "REPLACE ".$this->_db->NameQuote($this->_tableName)." SET ".implode(", ", $kv)." WHERE ".$this->_pkCrit($pk);
                $this->_db->setQuery($sql);
                $res = $this->_db->query();
            } else {
                $kv = array();
                foreach ($this->listProperties() as $propName) if (($propVal = $this->$propName)) {
                    $kv[$this->_db->NameQuote($propName)] = $this->_db->Quote($propVal);
                }
                $sql = "INSERT INTO ".$this->_db->NameQuote($this->_tableName)." (".implode(", ", array_keys($kv)).") VALUES (".implode(", ", $kv).")";
                $this->_db->setQuery($sql);
                if ($res = $this->_db->query()) {
                    if ($useAutoinc) $this->$useAutoinc = $this->_db->getLastInsertId();
                } else {
                }
            }
    
            if( !$res ) {
                $this->_error = strtolower(get_class( $this ))."::store failed <br />" . $this->_db->getErrorMsg();
            }
        } else {
            $this->_error = 'Cannot store() cpk record because of incomplete primary key'; 
            $res = false;
        }
        return $res;
    }
    
    function _legacyDelete($oid = false) {
        if ($oid !== null) {
            if (!is_array($oid) || count($oid) !== count($this->_pk)) 
                trigger_error("Primary key must be an array with ".count($this->_pk)." elements", E_USER_ERROR);
            $this->_setPk($oid);
        } else {
            $oid = $this->getPrimaryKey();    
        }

        if (!$this->_isFullPk($oid)) {
            $this->_error = "Cannot delete record with incomplete compound primary key";
            return false;
        }
        
        $query = "DELETE FROM {$this->_tableName} WHERE ".$this->_pkCrit($oid);
        
        $this->_db->setQuery( $query );

        if ($this->_db->query()) {
            return true;
        } else {
            $this->_error = $this->_db->getErrorMsg();
            return false;
        }
    }
    
    function _setPk($pk) {
        $pk = array_slice($pk);
        $c = count($this->_pk);
        for ($i = 0; $i < $c; $i++) $this->{$this->_pk[$i]} = $pk[$i];
    }
    
    function _hasFullPk() {
        foreach ($this->_pk as $f) if (($this->$f === false) || is_null($this->$f)) return false;
            return true;
    }
    
    function _isFullPk($pk) {
        foreach ($pk as $v) if ($v === false) return false;
            return true;
    }
    
    function _pkCrit($pk = null) {
        if (is_null($pk)) $pk = $this->getPrimaryKey();
        $pk = array_slice($pk);
        $c = count($this->_pk);
        $res = $this->_db->NameQuote($this->_pk[0]).' = '.$this->_db->Quote($this->{$this->_pk[0]});
        for ($i = 1; $i < $c; $i++) 
            $res .= ' AND '.$this->_db->NameQuote($this->_pk[0]).' = '.$this->_db->Quote($this->{$this->_pk[0]});
    }
    
    function matchesPk($oneOrMorePks) {
        if (!is_array($oneOrMorePks)) trigger_error ("Compound primary key can be given as array only", E_USER_ERROR);
        if (!count($oneOrMorePks)) return false;
        $oneOrMorePks = array_slice($oneOrMorePks, 0);
        if (!is_array($oneOrMorePks[0])) $oneOrMorePks = array($oneOrMorePks);
        $pk = $this->getPrimaryKey();
        $cpk = count($pk);
        foreach ($oneOrMorePks as $k) if (count($k) == $c) {
            $ok = true;
            $k = array_slice($k);
            while ($i < $cpk && ($ok = $ok && ($k[$i] == $pk[$i]))) $i++;
            if ($ok) return true; 
        }
        return false;
    }
    
    function isPersistent() {
        return $this->_hasFullPk();
    }
    
}

?>