<?php

class Ac_Sql_Dialect_Mysql extends Ac_Sql_Dialect {
    
    protected $nameQuoteChar = '`';
    
    protected $ifNullFunction = 'IFNULL';
    
    protected $inspectorClass = 'Ac_Sql_Dbi_Inspector_MySql5';
    
    function getConcatExpression(array $expressions) {
        return 'CONCAT('.implode(', ', $expressions).')';
    }
	
	function applyLimits($statement, $count, $offset, $orderBy = false) {
	    return $statement.' '.$this->getLimitsClause($count, $offset);
	}
	
	function ifStatement($if, $then, $else, $close = true) {
	    return "IF($if,$then,$else".($close? ")" : "");
	}
	
	function ifClose() {
		return ")";
	}
	
	function hasToConvertDatesOnLoad() {
	    return false;
	}
	
	function hasToConvertDatesOnStore() {
	    return false;
	}
	
	function convertDates($row, $columnFormats) {
	    foreach ($columnFormats as $column => $format) {
	        if (isset($row[$column])) $row[$column] = Ac_Util::date($row[$column], $format); 
	    } 
	    return $row;
	}
    
    function getLimitsClause($count, $offset = false, $withLimitKeyword = true) {
        if ($withLimitKeyword === false) $l = ''; else $l = ' LIMIT';
        if ($offset === false) $res = " {$l} ".intval($count);
            else $res = " {$l} ".intval($offset).", ".intval($count);
        return $res;
    }
    
}