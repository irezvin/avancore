<?php

Ae_Dispatcher::loadClass('Ae_Database');

class Ae_Pg_Database extends Ae_Database {
    var $_sql = false;
    
    var $_connection = false;
    var $_host = false;
    var $_user = false;
    var $_port = false;
    var $_password = false;
    var $_db = false;
    var $_charset = false;
    var $_timezone = false;
    var $_prefix = false;
    
    var $queryCount = 0;
    var $queries = array();
    var $trackQueries = false;
        
    function _doGetAccess() {
        return array(
            'user' => $this->_user, 
            'password' => $this->_password, 
            'host' => $this->_host, 
            'db' => $this->_db, 
            'prefix' => $this->_prefix,
        );
    }
        
    function _doInitialize($options) {
        foreach (array('host', 'user', 'password', 'db', 'charset', 'timezone', 'prefix') as $param) {
            if (isset($options[$param])) {
                $myParam = '_'.$param;
                $this->$myParam = $options[$param];
            }
        }
        $this->_getConnection();
    }

    function Quote($value) {
        if (is_a($value, 'Ae_Pg_Value')) $res = $value->quote;
        elseif (is_null($value)) $res = 'null';
        elseif (is_int($value) || is_float($value)) $res = $value;
        elseif (is_bool($value)) $res = $value? 'true' : 'false';
        elseif (is_object($value) && $value instanceof Ae_Sql_Expression) {
            $res = $value->quote($this);
        }
        else $res = "'".pg_escape_string($value)."'";
        return $res;
    }
    
    function NameQuote($string) {
        if (is_object($string) && $string instanceof Ae_Sql_Expression) {
            return $string->nameQuote($this);
        } else return '"'.$string.'"';
    }    
    
    function setQuery($query, $offset = 0, $limit = 0, $prefix = '#__') {
        $this->_sql = $query;
        if (($limit > 0 || $offset > 0) && is_numeric($offset) && is_numeric($limit)) {
            $this->_sql .= "\n".$this->getLimitsClause($limit, $offset);
        }
        if ($this->_prefix !== false) $this->_sql = str_replace($prefix, $this->_prefix, $this->_sql);
    }
    
    function _doQuery() {
        $this->queryCount++;
        if ($this->trackQueries) $this->queries[] = $this->_sql;
        $mres = pg_query($this->_getConnection(), $this->_sql);
        if ($mres === false) {
            var_dump($this->_sql, pg_errormessage());
        }
        //var_dump($this->_sql);
        return $mres;
    }
    
    function query($query = false) {
        if ($query) $this->setQuery($query);
        $this->_debugBeforeQuery($this->_sql);
        $res = $this->_doQuery();
        if ($res === false) {
            $this->handleError();
        }
        $this->_debugAfterQuery($this->_sql, false);
        return $res;
    }
    
    function loadResult() {
        $res = false;
        $this->_debugBeforeQuery($this->_sql);
        if ($mres = $this->_doQuery()) {
            if ($row = pg_fetch_row($mres)) $res = $row[0];
            pg_free_result($mres);  
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
            while ($row = pg_fetch_row($mres)) $res[] = $row[$numInArray];
            pg_free_result($mres);  
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
            while ($row = pg_fetch_assoc($mres)) {
                if (!$id) $key = count($res); else $key = $row[$id];
                $res[$key] = $row;
            }
            pg_free_result($mres);  
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
            while ($row = pg_fetch_object($mres)) {
                if (!$id) $key = count($res); else $key = $row->$id;
                $res[$key] = $row;
            }
            pg_free_result($mres);  
        } else {
            $this->handleError();
        }
        $this->_debugAfterQuery($this->_sql, count($res));
        return $res;
    }
    
    function handleError() {
        error_log('Wrong query: '.$this->_sql."; error is '".pg_errormessage()."'");
        trigger_error('Cannot run query', E_USER_ERROR);
    }
    
    function getLastInsertId() {
        return pg_last_oid($this->_getConnection());
    }

    function getConnectionString() {
        //$this->_host, $this->_user, $this->_password
        $res = "";
        if (strlen($this->_host)) $res .= " host={$this->_host}";
        if (strlen($this->_port)) $res .= " port={$this->_port}";
        if (strlen($this->_user)) $res .= " user={$this->_user}";
        if (strlen($this->_db)) $res .= " dbname={$this->_db}";
        if (strlen($this->_password)) $res .= " password={$this->_password}";
        return $res;
    }
    
    function _getConnection() {
        if ($this->_connection === false) {
            $this->_connection = pg_connect($this->getConnectionString());
            if (!$this->_connection) trigger_error ("Cannot connect to database", E_USER_ERROR);
            if ($this->_charset) {
                pg_set_client_encoding($this->_connection, $this->_charset);
            }
            //if ($this->_timezone) $this->query('SET time_zone='.$this->Quote($this->_timezone));
        }
        return $this->_connection;
    }
    
    function getResultResource($unbuffered = false) {
        static $rc = 0;
        $this->_debugBeforeQuery($this->_sql);
        $res = $this->_doQuery();
        if ($res === false) {
            $this->handleError();
        }
//        var_dump("Resource acquired: ".$rc++);
        $this->_debugAfterQuery($this->_sql, false, true);
        return $res;
    }
    
    function fetchAssoc($resultResource) {
        return pg_fetch_assoc($resultResource);
    }
    
    
    function fetchObject($resultResource, $className = null) {
        return pg_fetch_object($result, null, $className);
    }
    
    function freeResultResource($resultResource) {
        return pg_free_result($resultResource);
    }

    function supportsUnbufferedQueries() {
        return false;
    }
    
    function _pushQuery() {
        $this->_qBuf[] = $this->_sql;
    }
    
    function _popQuery() {
        $c = count($this->_qBuf);
        $this->_sql = $this->_qBuf[$c-1];
        unset($this->_qBuf[$c-1]);
    }
    
    function getLimitsClause($count, $offset = false, $withLimitKeyword = true) {
        $l = $withLimitKeyword? " LIMIT " : "";
        $res = $l.intval($count);
        if ($offset !== false)  $res .= " OFFSET ".intval($offset);
        return $res;
    }

    function __sleep() {
        $res = array_keys(get_object_vars($this));
        $res = array_diff($res, array('_connection'));
        return $res;
    }
    
    function getConcatExpression($expressions) {
        return '('.implode(' || ', $expressions).')';
    }

    function isNameQuoted($name) {
        return strlen($name) > 2 && $name{0} == '"';
    }
    
    function getMysqlArgs($withDbName = false) {
        extract($this->getAccess());
        $res = array();
        
        if (strlen($user)) $res[] = "-U ".escapeshellarg($user);
        if (count($hostPort = explode(':', $host)) == 2) {
            list($host, $port) = $hostPort;
            $res[] = "-p ".escapeshellarg($port);
        }
        if (strlen($host)) $res[] = "-h ".escapeshellarg($host);
        if ($withDbName) {
            if ($withDbName === true) $withDbName = strlen($db)? $db : false;
        }
        if (strlen($withDbName)) $res[] = escapeshellarg($withDbName);
        $cmd = implode(" ", $res);
        return $cmd; 
    }
    
    
}

?>