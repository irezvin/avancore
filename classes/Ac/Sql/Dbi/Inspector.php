<?php

/**
 * Strategy for retrieval of an information about database objects
 */

class Ac_Sql_Dbi_Inspector extends Ac_Prototyped {
	
    /**
     * @var Ac_Sql_Db
     */
    var $_db = false;
    
    protected $dbVersion = false;
    
    protected $isMariaDb102plus = null;
    
    var $defaultDatabaseName = false;
    
    function hasPublicVars() {
        return true;
    }

    protected function setDb(Ac_Sql_Db $db) {
        $this->_db = $db;
    }
    
    // --------------------- DATABASE-RELATED METHODS ----------------------
    
    /**
     * @return array(tableName1, tableName2...)
     */
    function listTablesForDatabase($databaseName = false) {
        $dbName = $this->_getDbName($databaseName);
        $res = array();
        foreach ($this->_db->fetchColumn('SHOW TABLES FROM '.$databaseName) as $tableName) {
            $res[] = $tableName; 
        }
        return $res;
    }
    
    function getDbVersion() {
        if ($this->dbVersion === false)
            $this->dbVersion = $this->_db->fetchValue("SELECT VERSION()");
        return $this->dbVersion;
    }
    
    function getIsMariaDb102plus() {
        if ($this->isMariaDb102plus !== null) 
            return $this->isMariaDb102plus;
        
        $this->isMariaDb102plus = false;
        
        $v = strtolower($this->getDbVersion());
        if (strpos($v, '-mariadb') !== false) {
            $vv = explode('-', $v, 2);
            $this->isMariaDb102plus = (version_compare($vv[0], '10.2') >= 0);
        }
        return $this->isMariaDb102plus;
    }
    
    // ----------------------- TABLE-RELATED METHODS ------------------------
    
    /**
     * @return array(columnName => array('type'=>, 'width'=>, 'decimals'=>,'default'=>, 'autoInc'=>true/false, 'comment'=>columnComment/false, 'enumValues'=>array(enumValues)/false), 
     *  columnName2 => array()...)
     */
    function getColumnsForTable($tableName, $databaseName = false) {
        $res = array();
        $databaseName = $this->_getDbName($databaseName);
        foreach ($this->_db->fetchArray('SHOW COLUMNS FROM '.$this->_getQTableName($databaseName, $tableName), 'Field') as $fieldName => $fieldData)  {
            $fi = array_merge($this->_parseType($fieldData['Type']));
            $fi['nullable'] = !strcasecmp($fieldData['Null'], 'yes');
            $fi['default'] = $this->processDefault($fieldData['Default']);
            if (strpos($fieldData['Extra'],'auto_increment') !== false) {
                $fi['autoInc'] = true;
            }
            $res[$fieldName] = $fi;
        }
        return $res;
    }
    
    /**
     * @return array(indexName=>array('primary'=>true/false,'unique'=>true/false,'columns'=>array('fieldName1','fieldName2'))
     */
    function getIndicesForTable($tableName, $databaseName = false) {
        $dbName = $this->_getDbName($databaseName);
        $res = array();
        foreach ($this->_db->fetchArray('SHOW INDEX FROM '.$this->_getQTableName($dbName, $tableName)) as $idxData) {
            $idxName = $idxData['Key_name'];
            if (!isset($res[$idxName])) $res[$idxName] = array(
                'primary' => ($idxName === 'PRIMARY'), 
                'unique' => (!$idxData['Non_unique']), 
                'columns' => array(),
            );
            $res[$idxName]['columns'][$idxData['Seq_in_index']] = $idxData['Column_name'];
        }
        return $res;
    }
    
    /**
     * @return array(relationName=>array('table'=>externalTableName,'columns'=>array('thisField1' => 'thatField1', 'thisField2' => 'thatField2'...)))
     */
    function getRelationsForTable($tableName, $databaseName = false) {
        return array(); // returning information about relations is not implemented in this class 
    }
    

    // converts default value returned by DB engine
    protected function processDefault($default) {
        if ($this->getIsMariaDb102plus() && $default === 'current_timestamp()') $default = 'CURRENT_TIMESTAMP';
        return $default;
    }
    
    
    // --------------------------- PRIVATE METHODS --------------------------
    
    function _getDbName($dbName) {
        if (strlen($dbName)) $res = $dbName;
        else {
            if (!strlen($this->defaultDatabaseName)) trigger_error ('Default database name not specified', E_USER_ERROR);
            $res = $this->defaultDatabaseName;
        }
        return $res;
    }
    
    function _getQDbName($dbName) {
        $res = $this->_db->n($this->_getDbName($dbName));
        return $res;
    }
    
    function _getQTableName($dbName, $tableName) {
        $res = $this->_db->n(array($dbName, $tableName));
        return $res;
    }
    
    /**
     * @var string $fieldType Type field from SHOW COLUMNS sql statement: int(11), char(1), enum('foo','bar') etc
     * @return array('type' => type, 'width'=>false/width, 'decimals'=> false/decimals, 'enumValues' => false/enumValues)
     */
    function _parseType($fieldType) {
        $res = array('type' => $fieldType);
        $u = ' unsigned';
        if (substr(strtolower($fieldType), -strlen($u)) == $u) {
            $fieldType = substr($fieldType, 0, -strlen($u));
            $res['unsigned'] = true;
        }
        if(preg_match('/^(\\w+)\\s*(\\(.+\\))?$/', $fieldType, $typeDetails)) {
            $res['type'] = $typeDetails[1];
            if (isset($typeDetails[2]) && strlen($td = $typeDetails[2]) > 2) { // '(foo,bar)'
                $td =  substr($td, 1, strlen($td) - 2); // get rid of parenthesis
                if ($td[0] == '\'') { // with enum values are started with single quote... 
                    $td = substr($td, 1, strlen($td) - 2); // get rid of outer quotes
                    foreach(preg_split('/(?<!\\\\)\',\'/', $td) as $enumVal) $enumVals[] = stripslashes($enumVal);
                    $res['enumValues'] = $enumVals;
                } else {
                    $wd = explode(',', $td); // we have width and decimals here
                    $res['width'] = $wd[0];
                    if (count($wd) > 1) $res['decimals'] = $wd[1];
                }
            }
        }
        return $res;
        
    }
    
}

