<?php

class Ac_Legacy_Database_Native extends Ac_Legacy_Database {

    var $dialectClass = 'Ac_Sql_Dialect_Mysql';
    
    protected $inspectorClass = 'Ac_Sql_Dbi_Inspector_MySql5';
    
    var $_sql = false;
    
    var $_connection = false;
    var $_host = false;
    var $_user = false;
    var $_password = false;
    var $_db = false;
    var $_charset = false;
    var $_timezone = false;
    var $_prefix = false;
    
    var $queryCount = 0;
    var $queries = array();
    
    var $throwExceptions = false;
    
    function _doGetAccess() {
        return array(
            'user' => $this->_user, 
            'password' => $this->_password, 
            'host' => $this->_host, 
            'db' => $this->_db, 
            'prefix' => $this->_prefix,
        );
    }
        
    function _doInitialize(array $options = array()) {
        foreach (array('host', 'user', 'password', 'db', 'charset', 'timezone', 'prefix') as $param) {
            if (isset($options[$param])) {
                $myParam = '_'.$param;
                $this->$myParam = $options[$param];
            }
        }
        $this->_getConnection();
    }
    
    function Quote($value) {
        if (is_string($value)) $res = "'".mysql_real_escape_string($value)."'";
        elseif (is_int($value) || is_float($value)) $res = ''.$value;
        elseif (is_null($value)) $res = 'null';
        elseif (is_bool($value)) $res = $value? 'true' : 'false';
        elseif (is_a($value, 'Ac_Sql_Expression')) $res = $value->getExpression($this);
        else $res = '\''.mysql_real_escape_string($value, $this->_connection).'\'';
        return $res;
    }
    
    function NameQuote($string) {
        if (is_a($string, 'Ac_Sql_Expression')) return $string->nameQuote($this);
            else return "`".str_replace("`", "``", $string)."`";
    }
    
    function setQuery($query, $offset = 0, $limit = 0, $prefix = '#__') {
//        echo("<p><b>$query</b></p>");
        $this->_sql = $query;
//        $f = fopen('log.txt', 'a'); fputs($f, "\n".$query); fclose($f);
        if (($limit > 0 || $offset > 0) && is_numeric($offset) && is_numeric($limit)) {
            $this->_sql .= "\nLIMIT $offset, $limit";
        }
        if ($this->_prefix !== false) $this->_sql = str_replace($prefix, $this->_prefix, $this->_sql);
    }
    
    function _doQuery() {
        $this->queryCount++;
        $res = mysql_query($this->_sql, $this->_getConnection());
        if ($res === false) error_log(($e = mysql_error($this->_getConnection())).' in '.$this->_sql);
        if ($res === false && !$this->throwExceptions) var_dump($e, $this->_sql);
        return $res;
    }
    
    function query($query = false) {
        if ($query) $this->setQuery($query);
        $this->_debugBeforeQuery($this->_sql);        
        $res = $this->_doQuery();
        if ($res === false) {
            $this->handleError();
        }
        $this->_debugAfterQuery($this->_sql, false, false);
        return $res;
    }
    
    function loadResult() {
        $res = false;
        $this->_debugBeforeQuery($this->_sql);        
        if ($mres = $this->_doQuery()) {
            if ($row = mysql_fetch_row($mres)) $res = $row[0];
            mysql_free_result($mres);  
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
            while ($row = mysql_fetch_row($mres)) $res[] = $row[$numInArray];
            mysql_free_result($mres);  
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
            while ($row = mysql_fetch_assoc($mres)) {
                if (!$id) $key = count($res); else $key = $row[$id];
                $res[$key] = $row;
            }
            mysql_free_result($mres);  
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
            while ($row = mysql_fetch_object($mres)) {
                if (!$id) $key = count($res); else $key = $row->$id;
                $res[$key] = $row;
            }
            mysql_free_result($mres);  
        } else {
            $this->handleError();
        }
        $this->_debugAfterQuery($this->_sql, count($res));
        return $res;
    }
    
    function handleError() {
        if ($this->throwExceptions) {
            throw new Ac_E_Database (array(mysql_error($this->_connection), $this->_sql), mysql_errno($this->_connection));
        } else {
            error_log('Wrong query: '.$this->_sql."; error is '".mysql_error($this->_connection)."'");
            trigger_error('Cannot run query', E_USER_ERROR);
        }
    }
    
    function getLastInsertId() {
        return mysql_insert_id($this->_getConnection());
    }

    function _getConnection() {
        if ($this->_connection === false) {
            $this->_connection = mysql_connect($this->_host, $this->_user, $this->_password, true);
            if (!$this->_connection) {
                if ($this->throwExceptions) {
                    throw new Ac_E_Database ("Cannot connect to database");
                } else {
                    trigger_error ("Cannot connect to database", E_USER_ERROR);
                }
            }
            if ($this->_db) {
                if (!mysql_select_db($this->_db, $this->_connection)) {
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
        static $rc = 0;
        $this->_debugBeforeQuery($this->_sql);
        $res = $unbuffered? mysql_unbuffered_query($this->_sql, $this->_getConnection()) : $this->_doQuery();
        $this->_debugAfterQuery($this->_sql, false, true);
        if ($res === false) {
            $this->handleError();
        }
//        var_dump("Resource acquired: ".$rc++);
        return $res;
    }
    
    function fetchAssoc($resultResource) {
        return mysql_fetch_assoc($resultResource);
    }
    
    function fetchObject($resultResource, $className = null) {
        return $className? mysql_fetch_object($resultResource, $className) : mysql_fetch_object($resultResource);
    }
    
    function fetchBoth($resultResource) {
        return mysql_fetch_array($resultResource, MYSQL_BOTH);
    }
    
    function freeResultResource($resultResource) {
        static $rc = 0;
//        var_dump("Resource freed: ".$rc++);
        return mysql_free_result($resultResource);
    }

    function supportsUnbufferedQueries() {
        return true;
    }
    
    function _pushQuery() {
        $this->_qBuf[] = $this->_sql;
    }
    
    function _popQuery() {
        $c = count($this->_qBuf);
        $this->_sql = $this->_qBuf[$c-1];
        unset($this->_qBuf[$c-1]);
    }
    
    function __sleep() {
        return array_diff(array_keys(get_object_vars($this)), array('_connection'));
    }

    function canCopyToDest($db) {
    	if (is_a($db, 'Ac_Legacy_Database_Native') && ($db->_db != $this->_db)) {
    		return true;
    	} else return false;
    }
    
	function _doCopyToDest($db) {
		$cmd = 'mysqldump '.$this->getMysqlArgs(true).' | mysql '.$db->getMysqlArgs(true);
		$res = exec($cmd, $out, $return);
		return !$return;
	}
    
    function getAffectedRows() {
        return mysql_affected_rows($this->_connection);
    }
    
}


?>