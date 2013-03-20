<?php

abstract class Ac_Sql_Dialect {
    
    protected $nameQuoteChar = '"';
    
    protected $ifNullFunction = 'COALESCE';
    
    protected $dateStoreFormats = array('date' => 'Y-m-d', 'time' => 'H:i:s', 'dateTime' => 'Y-m-d H:i:s');
    
    protected $zeroDates = array('date' => '0000-00-00', 'time' => '00:00', 'dateTime' => '0000-00-00 00:00:00');
    
    protected $inspectorClass = false;
    
    function hasPublicVars() {
        return false;
    }
    
    function nameQuote($name) {
        return $this->nameQuoteChar.str_replace($this->nameQuoteChar, "\\".$this->nameQuoteChar, $name).$this->nameQuoteChar;
    }
    
    function nameUnquote($name) {
        if ($this->isNameQuoted($name)) {
            return str_replace("\\".$this->nameQuoteChar, $this->nameQuoteChar, substr($name, 1, strlen($name) - 2));
        } else {
            return $name;
        }
    }
    
    function getNameQuoteChar() {
        return $this->nameQuoteChar;
    }
    
    function isNameQuoted($name) {
        return $name{0} == $this->nameQuoteChar && $name{strlen($name) - 1} == $this->nameQuoteChar;
    }
    
    function returnsLastInsertId() {
        return false;
    }
    
    function getLastInsertId(Ac_Sql_Db $db) {
        return null;
    }
    
    function getIfNullFunction() {
        return $this->ifNullFunction;
    }
	
	function hasToConvertDatesOnLoad() {
	    return false;
	}
	
	function hasToConvertDatesOnStore() {
	    return false;
	}
	
	function getDateStoreFormats() {
	    return $this->dateStoreFormats;
	}
	
	function getZeroDates() {
	    return $this->zeroDates;
	}
	
	function convertDates($row, $columnFormats) {
	    foreach ($columnFormats as $column => $format) {
	        if (isset($row[$column])) $row[$column] = Ac_Util::date($row[$column], $format); 
	    } 
	    return $row;
	}
	
	function convertDateForStore($date, $type) {
	    $dsf = $this->getDateStoreFormats();
	    $zd = $this->getZeroDates();
	    if (isset($dsf[$type])) {
	        if (is_null($date) || $date === false) $res = null;
	        else {
	            $ts = Ac_Util::date($date);
	            if ($ts === 0 && isset($zd[$type])) $res = $zd[$type];
	                else $res = Ac_Util::date($date, $dsf[$type]);
	        }
	    }
	    else {
	        throw new Ac_E_InvalidCall("\$type should be of ".implode('|', array_keys($dsf)));
	    }
	    return $res;
	}
    
    /**
     * @return string 
     */
    abstract function getConcatExpression(array $expressions);
	
    /**
     * @return string 
     */
	abstract function applyLimits($statement, $count, $offset, $orderBy = false);
	
    /**
     * @return string 
     */
    abstract function ifStatement($if, $then, $else, $close = true);
	
    /**
     * @return string 
     */
	abstract function ifClose();
    
    /**
     * @return Ac_Sql_Dbi_Inspector
     */
    function createInspector(Ac_Sql_Db $db) {
        $c = $this->inspectorClass;
        $dbi = new $c ($db, $db->getDbName());
        return $dbi;
    }
    
}