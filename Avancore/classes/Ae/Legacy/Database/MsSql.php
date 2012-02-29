<?php

Ae_Dispatcher::loadClass('Ae_Legacy_Database');

class Ae_Legacy_Database_MsSql extends Ae_Legacy_Database {
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
    
    
    var $skipNextError = false;
    
    var $_useSqlSrv = 'auto';
        
    function _doGetAccess() {
        return array(
            'user' => $this->_user, 
            'password' => $this->_password, 
            'host' => $this->_host, 
            'port' => $this->_port, 
        	'db' => $this->_db, 
            'prefix' => $this->_prefix,
        );
    }
        
    function _doInitialize($options) {
        foreach (array('host', 'port', 'user', 'password', 'db', 'charset', 'timezone', 'prefix', 'useSqlsrv') as $param) {
            if (isset($options[$param])) {
                $myParam = '_'.$param;
                $this->$myParam = $options[$param];
            }
        }
    	if ($this->_useSqlSrv === 'auto') {
    		$this->_useSqlSrv = function_exists('sqlsrv_connect');
    	}
        $this->_getConnection();
    }

    function Quote($value) {
        if (is_a($value, 'Ae_Sql_Expression')) $res=$value->getQuoted($this);
        elseif (is_a($value, 'Ae_Pg_Value')) $res = $value->getQuoted();
	    elseif (is_a($value, 'DateTime')) $res = "'".Ae_Util::date($value, "m.d.Y H:i:s")."'";
        elseif (is_null($value)) $res = 'null';
        elseif (is_int($value) || is_float($value)) $res = $value;
        //elseif (is_bool($value)) $res = $value? 'true' : 'false';
        else $res = "'".str_replace("'", "''", $value)."'";
        return $res;
    }
    
    function NameQuote($string) {
        if (is_a($string, 'Ae_Sql_Expression')) return $string->nameQuote($this);
        return '['.str_replace('[', '[[', str_replace(']', ']]', $string)).']';
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
        if ($this->_useSqlSrv) {
        	$mres = sqlsrv_query($this->_getConnection(), $this->_sql);
        	if (($mres === false) && !$this->skipNextError) {
        		var_dump($this->_sql, $this->_getSqlSrvErrors());
        	}
        } else {
        	$mres = mssql_query($this->_sql, $this->_getConnection());
        	if (($mres === false) && !$this->skipNextError) {
            	var_dump($this->_sql, mssql_get_last_message());
        	}
        }
        if ($mres === false && $this->skipNextError) $mres = true;
        $this->skipNextError = false;
        return $mres;
    }
    
    function _getSqlSrvErrors() {
    	$errs = sqlsrv_errors();
    	if ($errs) {
    		foreach (array_keys($errs) as $k) $errs[$k] = implode(' / ', $errs[$k]);
    		$res = implode ("\n", $errs); 
    	}
    	else $res = false;
    	return $res;
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
            if ($this->_useSqlSrv) {
            	if ($row = sqlsrv_fetch_array($mres)) $res = $row[0];
            	sqlsrv_free_stmt($mres);
            } else {
        		if ($row = mssql_fetch_row($mres)) $res = $row[0];
            	mssql_free_result($mres);
            }  
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
            if ($this->_useSqlSrv) {
            	while ($row = sqlsrv_fetch_array($mres, SQLSRV_FETCH_BOTH)) $res[] = $row[$numInArray];
            	sqlsrv_free_stmt($mres);
            } else {
            	while ($row = mssql_fetch_array($mres, MSSQL_BOTH)) $res[] = $row[$numInArray];
            	mssql_free_result($mres);
            }  
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
            if ($this->_useSqlSrv) {
	            while ($row = sqlsrv_fetch_array($mres, SQLSRV_FETCH_ASSOC)) {
	                if (!$id) $key = count($res); else $key = $row[$id];
	                $res[$key] = $row;
	            }
	            sqlsrv_free_stmt($mres);
            } else {
	            while ($row = mssql_fetch_assoc($mres)) {
	                if (!$id) $key = count($res); else $key = $row[$id];
	                $res[$key] = $row;
	            }
                mssql_free_result($mres);
            }  
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
            if ($this->_useSqlSrv) {
	            while ($row = sqlsrv_fetch_object($mres)) {
	                if (!$id) $key = count($res); else $key = $row->$id;
	                $res[$key] = $row;
	            }
	            sqlsrv_free_stmt($mres);
            } else {
	            while ($row = mssql_fetch_object($mres)) {
	                if (!$id) $key = count($res); else $key = $row->$id;
	                $res[$key] = $row;
	            }
	            mssql_free_result($mres);
            }  
        } else {
            $this->handleError();
        }
        $this->_debugAfterQuery($this->_sql, count($res));
        return $res;
    }
    
    function handleError() {
        error_log('Wrong query: '.$this->_sql."; error is '".($this->_useSqlSrv? $this->_getSqlSrvErrors() : mssql_get_last_message())."'");
        trigger_error('Cannot run query', E_USER_ERROR);
    }
    
    function getLastInsertId() {
    	$res = false;
    	$sql = 'SELECT @@IDENTITY';
        $this->_debugBeforeQuery($sql);
    	if ($this->_useSqlSrv) {
			$r = sqlsrv_query($this->_getConnection(), $sql);
	        while ($row = sqlsrv_fetch_array($r, SQLSRV_FETCH_ASSOC)) {
				$kk = array_keys($row);
				$res = $row[$kk[0]];
			}
    		sqlsrv_free_stmt($r);
    	} else {
    		$r = mssql_query($sql, $this->_getConnection());
    		$row = mssql_fetch_row($r);
    		$res = $row[0];
    		mssql_free_result($r);
    	}
        $this->_debugAfterQuery($sql, count($res));
    	return $res;
    }
    
    function _getConnection() {
        if ($this->_connection === false) {
        	$serverName = $this->_host;
        	if (strlen($this->_port)) $serverName .= ",".$this->_port;
        	if ($this->_useSqlSrv) {
	            $params = array(
	            	'UID' => $this->_user,
	            	'PWD' => $this->_password,
	            );
	            if ($this->_charset) $params['CharacterSet'] = $this->_charset;
	            if ($this->_db) $params['Database'] = $this->_db;
        		$this->_connection = sqlsrv_connect($serverName, $params);
        		if (!$this->_connection) trigger_error ("Cannot connect to database: ".$this->_getSqlSrvErrors(), E_USER_ERROR);
        	} else {
	            $this->_connection = mssql_connect($serverName, $this->_user, $this->_password);
	            if (!$this->_connection) trigger_error ("Cannot connect to database", E_USER_ERROR);
	            if (strlen($this->_db)) mssql_select_db($this->_db);
        	}

			$tmp = $this->_sql;
			
            $this->setQuery('SET DATEFORMAT mdy;');
		    $this->query();
		
		    $this->setQuery('SET ANSI_NULLS ON;');
		    $this->query();
			
			$this->_sql = $tmp;
		
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
        $this->_debugAfterQuery($this->_sql, false, true);        
        return $res;
    }
    
    function fetchAssoc($resultResource) {
    	if ($this->_useSqlSrv) {
    		$res = sqlsrv_fetch_array($resultResource, SQLSRV_FETCH_ASSOC);	
			return $res;
    	} else {
    		return mssql_fetch_assoc($resultResource);
    	}
    }
    
    function fetchBoth($resultResource) {
    	if ($this->_useSqlSrv) {
    		return sqlsrv_fetch_array($resultResource, SQLSRV_FETCH_BOTH);	
    	} else {
    		return mssql_fetch_array($resultResource, MSSQL_BOTH);
    	}
    }
    
    function _gotoNextResultset($resultResource) {
    	if ($this->_useSqlSrv) {
    		return sqlsrv_next_result($resultResource);	
    	} else {
    		return mssql_next_result($resultResource);
    	}
    }
    
    function freeResultResource($resultResource) {
    	if ($this->_useSqlSrv) {
        	return sqlsrv_free_stmt($resultResource);
    	} else {
    		return mssql_free_result($resultResource);	
    	}
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
        return ($l = strlen($name)) > 2 && $name{0} == '[' && $name{$l-1} == ']';
    }
	
	function getIfnullFunction() {
	    return 'COALESCE';
	}
	
	function applyLimits($statement, $count, $offset, $orderBy = false) {
	    // Find keywords after the SELECT clause
	    $rx = "/(?P<select>SELECT)(?P<allOrDistinct>\s+(ALL)|(DISTINCT))?(?P<top>\s+TOP\s+[0-9.,]+(\s+PERCENT)?(\s+WITH\s+TIES)?)?/imu";
	    if (preg_match($rx, $statement, $matches)) {
    	    $hasTop = isset($matches['top']) && strlen($matches['top']);
    	    $count1 = $count + $offset;
    	    if (!$offset) {
    	    }
    	    if (0 && strlen($orderBy)) { // Yes, We Can do it with ROW_NUMBER()
    	        $newSql = "
    	        ";
    	    } else {
                $offset = (int) $offset;	
                $count1 = $count + $offset; 
                $newSql = $statement;
                $pos = strpos($statement, $matches[0]);
                if (!isset($matches['allOrDistinct'])) $matches['allOrDistinct'] = '';
                if (!$hasTop) {
                    $newSql1 = substr_replace($statement, $matches['select'].$matches['allOrDistinct'].' TOP '.$count1.' ', $pos, strlen($matches[0]));
                    $newSql2 = substr_replace($statement, $matches['select'].$matches['allOrDistinct'].' TOP '.$offset.' ', $pos, strlen($matches[0]));
            	    $newSql1 = "
                    	SELECT * FROM (
                    	    $newSql1
                    	) AS s_limit_1
                    ";
            	    $newSql2 = "
                    	SELECT * FROM (
                    	    $newSql2
                    	) AS s_limit_2
                    ";
                } else {
            	    $newSql1 = "
                    	SELECT TOP $count1 * FROM (
                    	    $statement
                    	) AS s_limit_1
                    ";
            	    $newSql2 = "
                    	SELECT TOP $offset * FROM (
                    	    $statement
                    	) AS s_limit_2
                    ";
                }
                $res = $newSql1;
                if ($offset) 
                    $res .= "
                		EXCEPT
                			$newSql2
                	";
    	    }
	    } else {
	        trigger_error("Cannot properly detect beginning of SELECT clause in staement '$statement'", E_USER_NOTICE);
	        $res = $statement;
	    }
	    return $res;
        
	}
	
	function ifStatement($if, $then, $else, $close = true) {
	    return "CASE WHEN $if THEN $then ELSE $else".($close? " END" : "");
	}
	
	function ifClose() {
		return " END";
	}
	
	function hasToConvertDatesOnLoad() {
	    return true;
	}
	
	function hasToConvertDatesOnStore() {
	    return true;
	}
	
	function getDateStoreFormats() {
	    return array('date' => 'm/d/Y', 'time' => 'H:i:s', 'dateTime' => 'm/d/Y H:i:s');
	}
	
}

?>
