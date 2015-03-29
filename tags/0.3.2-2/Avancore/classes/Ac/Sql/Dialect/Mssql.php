<?php

class Ac_Sql_Dialect_Mssql extends Ac_Sql_Dialect {
    
    protected $dateStoreFormats = array('date' => 'm/d/Y', 'time' => 'H:i:s', 'dateTime' => 'm/d/Y H:i:s');
    
    protected $zeroDates = array('date' => '0000-00-00', 'time' => '00:00', 'dateTime' => '0000-00-00 00:00:00');
    
    protected $inspectorClass = 'Ac_Sql_Dbi_Inspector_MsSql';
    
    function returnsLastInsertId() {
        return true;
    }
    
    function getLastInsertId(Ac_Sql_Db $db) {
    	return $db->fetchValue('SELECT @@IDENTITY');
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
    
    function getConcatExpression($expressions) {
        return '('.implode(' || ', $expressions).')';
    }
	
	function ifStatement($if, $then, $else, $close = true) {
	    return "CASE WHEN $if THEN $then ELSE $else".($close? " END" : "");
	}
	
	function ifClose() {
		return " END";
	}
    
    function getSupportsLimitClause() {
        return false;
    }
    
}