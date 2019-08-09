<?php

class Ac_Sql_Dbi_Inspector_Canned extends Ac_Sql_Dbi_Inspector implements Ac_I_ArraySerializable {

    var $tableData = array();
    
    function __construct() {
    }
    
    function import(Ac_Sql_Dbi_Inspector $otherInspector, $databaseName = false, $tableNames = false, $append = false) {
        if (!$append) $this->tableData = array();
        if ($databaseName === false) $databaseName = $this->defaultDatabaseName;
        $tables = $otherInspector->listTablesForDatabase($databaseName);
        if (is_array($tableNames)) $tables = array_intersect($tables, $tableNames);
        foreach ($tables as $tableName) {
            $this->tableData[$databaseName][$tableName] = array(
                'columns' => $otherInspector->getColumnsForTable($tableName, $databaseName),
                'indices' => $otherInspector->getIndicesForTable($tableName, $databaseName),
                'relations' => $otherInspector->getRelationsForTable($tableName, $databaseName)
            );
        }
    }

    /**
     * @return array(tableName1, tableName2...)
     */
    function listTablesForDatabase($databaseName = false) {
        if ($databaseName === false) $databaseName = $this->defaultDatabaseName;
        if (isset($this->tableData[$databaseName])) $res = array_keys($this->tableData[$databaseName]);
            else $res = array();
        return $res;
    }
    
    // ----------------------- TABLE-RELATED METHODS ------------------------
    
    /**
     * @return array(columnName => array('type'=>, 'width'=>, 'decimals'=>,'default'=>, 'autoInc'=>true/false, 'comment'=>columnComment/false, 'enumValues'=>array(enumValues)/false), 
     *  columnName2 => array()...)
     */
    function getColumnsForTable($tableName, $databaseName = false) {
        if ($databaseName === false) $databaseName = $this->defaultDatabaseName;
        if (isset($this->tableData[$databaseName]) && isset($this->tableData[$databaseName][$tableName])) 
            $res = $this->tableData[$databaseName][$tableName]['columns'];
            else $res = array();
        return $res;
    }
    
    /**
     * @return array(indexName=>array('primary'=>true/false,'unique'=>true/false,'columns'=>array('fieldName1','fieldName2'))
     */
    function getIndicesForTable($tableName, $databaseName = false) {
        if ($databaseName === false) $databaseName = $this->defaultDatabaseName;
        if (isset($this->tableData[$databaseName]) && isset($this->tableData[$databaseName][$tableName])) 
            $res = $this->tableData[$databaseName][$tableName]['indices'];
            else $res = array();
        return $res;
    }
    
    /**
     * @return array(relationName=>array('table'=>externalTableName,'columns'=>array('thisField1' => 'thatField1', 'thisField2' => 'thatField2'...)))
     */
    function getRelationsForTable($tableName, $databaseName = false) {
        if ($databaseName === false) $databaseName = $this->defaultDatabaseName;
        if (isset($this->tableData[$databaseName]) && isset($this->tableData[$databaseName][$tableName])) 
            $res = $this->tableData[$databaseName][$tableName]['relations'];
            else $res = array();
        return $res;
    }
    
    public function serializeToArray() {
        $res = array_merge(array(
            '__class' => get_class($this)
        ), 
        Ac_Util::getObjectProperty($this, array('tableData', 'defaultDatabaseName')));
        return $res;
    }
    
    public function unserializeFromArray($array) {
        foreach (array('tableData', 'defaultDatabaseName') as $p) $this->$p = $array[$p];
    }
   
}