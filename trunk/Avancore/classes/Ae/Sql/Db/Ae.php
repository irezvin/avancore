<?php

if (class_exists('Ae_Dispatcher')) Ae_Dispatcher::loadClass('Ae_Sql_Db');
elseif (!class_exists('Ae_Sql_Db')) require('Ae/Sql/Db.php');

class Ae_Sql_Db_Ae extends Ae_Sql_Db {
    
    /**
     * @var Ae_Legacy_Database
     */
    var $_aeDb = false;
    
    /**
     * @param Ae_Legacy_Database$aeDb
     * @return Ae_Sql_Db_Ae
     */
    function Ae_Sql_Db_Ae (& $aeDb = null) {
        if (!$aeDb) {
        	$aeDb = & Ae_Legacy_Database::getDefaultInstance();
        }
    	assert(is_a($aeDb, 'Ae_Legacy_Database'));
        $this->_aeDb = & $aeDb;
    }
    
    function _implValueQuote($value) {
        return $this->_aeDb->Quote($value);
    }
    
    function _implNameQuote($name) {
        return $this->_aeDb->replacePrefix($this->_aeDb->NameQuote($name));
    }
    
    function _implIsNameQuoted($name) {
        return !is_a($name, 'Ae_Sql_Expression') && $this->_aeDb->isNameQuoted($name);
    }
    
    function _implConcatNames($quotedNames) {
        return implode(".", $quotedNames);
    }
        
    function getLimitClause($count, $offset = false, $withLimitKeyword = true) {
        return $this->_aeDb->getLimitsClause($count, $offset, $withLimitKeyword);
    }
    
    function fetchRow($query, $key = false, $keyColumn = false, $withNumericKeys = false, $default = false) {
    	$rows = $this->fetchArray($query, $keyColumn, $withNumericKeys);
        $res = $default;
        if ($key === false) {
            $myRow = array_slice($rows, 0, 1);
            if (count($myRow)) $res = $myRow[0];
        } else {
            if (isset($rows[$key])) $res = $rows[$key];
        }
        return $res;
    }

    function _convert(& $query) {
        if(is_a($query, 'Ae_Sql_Expression')) {
        	if (function_exists('xdebug_time_index')) {
            	if (!isset($GLOBALS['_exprTime'])) $GLOBALS['_exprTime'] = 0;
            	$t = xdebug_time_index(); 
            	$res = $query->getExpression($this);
            	$GLOBALS['_exprTime'] += (xdebug_time_index() - $t);
        	} else {
        		$res = $query->getExpression($this);
        	} 
        } else $res = $query;
        return $res;
    }
    
    function fetchArray($query, $keyColumn = false, $withNumericKeys = false) {
        $res = array();
        $this->_aeDb->setQuery($this->_convert($query));
        if (!$withNumericKeys) $res = $this->_aeDb->loadAssocList($keyColumn);
        else {
            $res = array();
            $r = $this->_aeDb->getResultResource(false);
            while($ass = $this->_aeDb->fetchBoth($r)) {
                if ($keyColumn !== false) $key = $ass[$keyColumn]; else $key = count($res); 
                $res[$key] = $ass;
            }
            $this->_aeDb->freeResultResource($r);
        }
        return $res;
    }
    
    function fetchObjects($query, $keyColumn = false) {
        $res = array();
        $this->_aeDb->setQuery($this->_convert($query));
        $res = $this->_aeDb->loadObjectList();
        return $res;
    }
    
    function fetchColumn($query, $colNo = 0, $keyColumn = false) {
        $this->_aeDb->setQuery($this->_convert($query));
        $res = array();
        foreach ($this->_aeDb->loadAssocList() as $ass) {
            $ass = array_merge($ass, array_values($ass));
            if ($keyColumn !== false) $key = $ass[$keyColumn]; else $key = count($res); 
            $res[$key] = $ass[$colNo];
        }
        return $res;
    }
    
    function fetchValue($query, $colNo = 0, $default = null) {
        $this->_aeDb->setQuery($this->_convert($query));
        $res = $default;
        foreach ($this->_aeDb->loadAssocList() as $ass) {
            $ass = array_merge($ass, array_values($ass));
            $res = $ass[$colNo];
            break;
        }
        return $res;
    }
    
    function query($query) {
        $this->_aeDb->setQuery($this->_convert($query));
        $res = $this->_aeDb->query();
        return $res;
    }
    
    function getLastInsertId() {
        return $this->_aeDb->getLastInsertId();
    }
    
    function applyLimits($statement, $count, $offset = false, $orderBy = false) {
        return $this->_aeDb->applyLimits($statement, $count, $offset, $orderBy);
    }
    
    function getErrorDescr() {
        return $this->_aeDb->getErrorMsg();
    }
    
}

?>