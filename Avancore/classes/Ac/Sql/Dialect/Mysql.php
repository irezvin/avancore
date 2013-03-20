<?php

class Ac_Sql_Dialect_Mysql extends Ac_Sql_Dialect {
    
    protected $nameQuoteChar = '`';
    
    protected $ifNullFunction = 'IFNULL';
    
    protected $dateStoreFormats = array('date' => 'Y-m-d', 'time' => 'H:i:s', 'dateTime' => 'Y-m-d H:i:s');
    
    protected $zeroDates = array('date' => '0000-00-00', 'time' => '00:00', 'dateTime' => '0000-00-00 00:00:00');
    
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
	
	function getDateStoreFormats() {
	    return array('date' => 'Y-m-d', 'time' => 'H:i:s', 'dateTime' => 'Y-m-d H:i:s');
	}
	
	function getZeroDates() {
	    return array('date' => '0000-00-00', 'time' => '00:00', 'dateTime' => '0000-00-00 00:00:00');
	}
	
	function convertDates($row, $columnFormats) {
	    foreach ($columnFormats as $column => $format) {
	        if (isset($row[$column])) $row[$column] = Ac_Util::date($row[$column], $format); 
	    } 
	    return $row;
	}
    
}