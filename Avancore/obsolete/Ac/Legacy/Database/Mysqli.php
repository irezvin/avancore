<?php

class Ac_Legacy_Database_Mysqli extends Ac_Legacy_Database_Native {

    function Quote($value) {
        if (is_string($value)) $res = "'".mysqli_real_escape_string($this->_getConnection(), $value)."'";
        elseif (is_int($value) || is_float($value)) $res = ''.$value;
        elseif (is_null($value)) $res = 'null';
        elseif (is_bool($value)) $res = $value? 'true' : 'false';
        elseif (is_object($value) && $value instanceof Ac_I_Sql_Expression) $res = $value->getExpression($this);
        else $res = '\''.mysqli_real_escape_string($this->_getConnection(), $value).'\'';
        return $res;
    }
    
    function _doQuery() {
        $this->queryCount++;
        $res = mysqli_query($this->_getConnection(), $this->_sql);
        if ($res === false) error_log(($e = mysqli_error($this->_getConnection())).' in '.$this->_sql);
        if ($res === false && !$this->throwExceptions) var_dump($e, $this->_sql);
        return $res;
    }
    
    function loadResult() {
        $res = false;
        $this->_debugBeforeQuery($this->_sql);        
        if ($mres = $this->_doQuery()) {
            if ($row = mysqli_fetch_row($mres)) $res = $row[0];
            mysqli_free_result($mres);  
        } else {
            $this->handleError();
        }
        $this->_debugAfterQuery($this->_sql, count($res));
        return $res;
    }
    
    function loadResultArray($numInArray = 0) {
        $res = false;
        $this->_debugBeforeQuery($this->_sql);        
        if ($mres = $this->_doQuery()) {
            $res = array();
            while ($row = mysqli_fetch_row($mres)) $res[] = $row[$numInArray];
            mysqli_free_result($mres);  
        } else {
            $this->handleError();
        }
        $this->_debugAfterQuery($this->_sql, count($res));
        return $res;
    }
    
    function loadAssocList($id = '') {
        $res = false;
        $this->_debugBeforeQuery($this->_sql);
        if ($mres = $this->_doQuery()) {
            $res = array();
            while ($row = mysqli_fetch_assoc($mres)) {
                if (!$id) $key = count($res); else $key = $row[$id];
                $res[$key] = $row;
            }
            mysqli_free_result($mres);  
        } else {
            $this->handleError();
        }
        $this->_debugAfterQuery($this->_sql, count($res));
        return $res;
    }
    
    function loadObjectList($id = '') {
        $res = false;
        $this->_debugBeforeQuery($this->_sql);
        if ($mres = $this->_doQuery()) {
            $res = array();
            while ($row = mysqli_fetch_object($mres)) {
                if (!$id) $key = count($res); else $key = $row->$id;
                $res[$key] = $row;
            }
            mysqli_free_result($mres);  
        } else {
            $this->handleError();
        }
        $this->_debugAfterQuery($this->_sql, count($res));
        return $res;
    }
    
    function handleError() {
        if ($this->throwExceptions) {
            throw new Ac_E_Database (array(mysqli_error($this->_connection), $this->_sql), mysqli_errno($this->_connection));
        } else {
            error_log('Wrong query: '.$this->_sql."; error is '".mysqli_error($this->_connection)."'");
            trigger_error('Cannot run query', E_USER_ERROR);
        }
    }
    
    function getLastInsertId() {
        return mysqli_insert_id($this->_getConnection());
    }

    function _getConnection() {
        if ($this->_connection === false) {
            $this->_connection = mysqli_connect($this->_host, $this->_user, $this->_password);
            if (!$this->_connection) {
                if ($this->throwExceptions) {
                    throw new Ac_E_Database ("Cannot connect to database");
                } else {
                    trigger_error ("Cannot connect to database", E_USER_ERROR);
                }
            }
            if ($this->_db) {
                if (!mysqli_select_db($this->_connection, $this->_db)) {
                    if ($this->throwExceptions) {
                        throw new Ac_E_Database ("Cannot select db '{$this->_db}'");
                    } else {
                        trigger_error ("Cannot select db '{$this->_db}", E_USER_ERROR);
                    }
                }
            }
            if ($this->_charset) {
                $this->setQuery('SET NAMES '.$this->_charset);
                $this->query();
            }
            if ($this->_timezone) $this->query('SET time_zone='.$this->Quote($this->_timezone));
        }
        return $this->_connection;
    }
    
    function getResultResource($unbuffered = false) {
        $this->_debugBeforeQuery($this->_sql);
        $res = $unbuffered? mysqli_unbuffered_query($this->_getConnection(), $this->_sql) : $this->_doQuery();
        $this->_debugAfterQuery($this->_sql, false, true);
        if ($res === false) {
            $this->handleError();
        }
        return $res;
    }
    
    function fetchAssoc($resultResource) {
        return mysqli_fetch_assoc($resultResource);
    }
    
    function fetchObject($resultResource, $className = null) {
        return $className? mysqli_fetch_object($resultResource, $className) : mysqli_fetch_object($resultResource);
    }
    
    function fetchBoth($resultResource) {
        return mysqli_fetch_array($resultResource, MYSQLI_BOTH);
    }
    
    function freeResultResource($resultResource) {
        return mysqli_free_result($resultResource);
    }

    function getAffectedRows() {
        return mysqli_affected_rows($this->_connection);
    }
    
    function getFieldsInfo($resultResource, $noTableKey = 0) {
        $res = array();
        $tmp = 0;
        while ($finfo = $resultResource->fetch_field()) {
            $res[] = array($finfo->table, $finfo->name);
            $tmp++;
        }
        return $res;
    }
    
    function fetchAssocByTables($resultResource, $fieldsInfo = false) {
        $row = mysqli_fetch_array($resultResource, MYSQLI_NUM);
        if (!$row) return $row;
        foreach ($fieldsInfo as $i => $fi) {
            $res[$fi[0]][$fi[1]] = $row[$i];
        }
        return $res;
    }
    

}
