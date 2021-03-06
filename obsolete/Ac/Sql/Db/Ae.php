<?php

class Ac_Sql_Db_Ae extends Ac_Sql_Db {
    
    /**
     * @var Ac_Legacy_Database
     */
    var $_aeDb = false;
    
    protected $dialect = false;
    
    protected $isInit = false;
    
    /**
     * @param Ac_Legacy_Database$aeDb
     * @return Ac_Sql_Db_Ae
     */
    function __construct ($aeDb = null) {
        if (!$aeDb) {
        	$aeDb = Ac_Legacy_Database::getDefaultInstance();
        }
    	assert(is_a($aeDb, 'Ac_Legacy_Database'));
        $this->_aeDb = $aeDb;
        $this->dialect = new $this->_aeDb->dialectClass;
    }
    
    protected function init() {
        if ($this->isInit) {
            $this->isInit = true;
            $this->runInitCommands();
        }
    }
    
    protected function implValueQuote($value) {
        return $this->_aeDb->Quote($value);
    }
    
    protected function implNameQuote($name) {
        return $this->_aeDb->replacePrefix($this->_aeDb->NameQuote($name));
    }
    
    protected function implNameUnquote($name) {
        throw new Exception("Not implemented");
    }
    
    function getErrorCode() {
        return $this->_aeDb->getErrorCode();
    }
    
    protected function implIsNameQuoted($name) {
        return !is_object($name) && $name instanceof Ac_I_Sql_Expression && $this->_aeDb->isNameQuoted($name);
    }
    
    protected function implConcatNames($quotedNames) {
        return implode(".", $quotedNames);
    }
        
    function getLimitClause($count, $offset = false, $withLimitKeyword = true) {
        return $this->_aeDb->getLimitsClause($count, $offset, $withLimitKeyword);
    }
    
    function fetchRow($query, $key = false, $keyColumn = false, $withNumericKeys = false, $default = false) {
        if (is_array($keyColumn)) throw new Exception("Array \$keyColumn is not supported by ".__METHOD__);
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

    function fetchArray($query, $keyColumn = false, $withNumericKeys = false) {
        $this->init();
        $res = array();
        $this->_aeDb->setQuery($this->intPreProcessQuery($query));
        if (!$withNumericKeys) $res = $this->_aeDb->loadAssocList($keyColumn);
        else {
            $res = array();
            $r = $this->_aeDb->getResultResource(false);
            while($ass = $this->_aeDb->fetchBoth($r)) {
                if ($keyColumn !== false && !is_array($keyColumn)) 
                    $key = $ass[$keyColumn]; 
                else $key = count($res); 
                $res[$key] = $ass;
            }
            $this->_aeDb->freeResultResource($r);
        }
        if (is_array($keyColumn)) $res = $this->indexRows($keyColumn);
        return $res;
    }
    
    function fetchObjects($query, $keyColumn = false) {
        $this->init();
        $res = array();
        $this->_aeDb->setQuery($this->intPreProcessQuery($query));
        $res = $this->_aeDb->loadObjectList();
        if ($keyColumn !== false) $res = $this->indexRows($res, is_array($keyColumn)? $keyColumn : array($keyColumn, true));
        return $res;
    }
    
    function fetchColumn($query, $colNo = 0, $keyColumn = false) {
        $this->init();
        $this->_aeDb->setQuery($this->intPreProcessQuery($query));
        $res = array();
        if (!is_array($keyColumn)) {
            foreach ($this->_aeDb->loadAssocList() as $ass) {
                $ass = array_merge($ass, array_values($ass));
                if ($keyColumn !== false) $key = $ass[$keyColumn]; else $key = count($res); 
                $res[$key] = $ass[$colNo];
            }
        } else {
            $res = $this->indexRows($this->_aeDb->loadAssocList(), $keyColumn, $colNo);
        }
        return $res;
    }
    
    function fetchValue($query, $colNo = 0, $default = null) {
        $this->init();
        $this->_aeDb->setQuery($this->intPreProcessQuery($query));
        $res = $default;
        foreach ($this->_aeDb->loadAssocList() as $ass) {
            $ass = array_merge($ass, array_values($ass));
            $res = $ass[$colNo];
            break;
        }
        return $res;
    }
    
    function query($query) {
        $this->init();
        $this->_aeDb->setQuery($this->intPreProcessQuery($query));
        $res = $this->_aeDb->query();
        return $res;
    }
    
    function applyLimits($statement, $count, $offset = false, $orderBy = false) {
        return $this->_aeDb->applyLimits($statement, $count, $offset, $orderBy);
    }
    
    function getErrorDescr() {
        return $this->_aeDb->getErrorMsg();
    }
    
    function getIfnullFunction() {
        return $this->_aeDb->getIfnullFunction();
    }
    
    function getAffectedRows() {
        return $this->_aeDb->getAffectedRows();
    }
    
    function getLastInsertId() {
        return $this->_aeDb->getLastInsertId();
    }
    
    function getDbPrefix() {
        return $this->_aeDb->getPrefix();
    }
    
    function getDbName() {
        return $this->_aeDb->getAccess('db');
    }
    
    function getDialect() {
        if ($this->dialect === false) {
            if ($this->_aeDb instanceof Ac_Legacy_Database_Joomla) $this->dialect = new Ac_Sql_Dialect_Mysql;
            elseif ($this->_aeDb instanceof Ac_Legacy_Database_Native) $this->dialect = new Ac_Sql_Dialect_Mysql;
            elseif ($this->_aeDb instanceof Ac_Legacy_Database_Pg) $this->dialect = new Ac_Sql_Dialect_Pgsql;
            elseif ($this->_aeDb instanceof Ac_Legacy_Database_MsSql) $this->dialect = new Ac_Sql_Dialect_Mssql();
        } 
        return $this->dialect;
    }
    
    /**
     * @return Ac_Sql_Dbi_Inspector
     */
    function getInspector() {
        if ($this->inspector === false) {
            $this->inspector = $this->_aeDb->getInspector();
        }
        return $this->inspector;
    }
    
    function getResultResource($query) {
        $this->init();
        $this->_aeDb->setQuery($this->intPreProcessQuery($query));
        $res = $this->_aeDb->getResultResource();
        return $res;
    }
    
    function resultGetFieldsInfo($resultResource) {
        return $this->_aeDb->getFieldsInfo($resultResource);
    }
    
    function resultFetchAssocByTables($resultResource, array & $fieldsInfo = null) {
        if (!$fieldsInfo) $fieldsInfo = $this->resultGetFieldsInfo ($resultResource);
        return $this->_aeDb->fetchAssocByTables($resultResource, $fieldsInfo);
    }
    
    function resultFetchAssoc($resultResource) {
        return $this->_aeDb->fetchAssoc($resultResource);
    }
    
    function resultFreeResource($resultResource) {
        return $this->_aeDb->freeResultResource($resultResource);
    }
    
    
    
    
}
