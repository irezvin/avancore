<?php

class Ac_Sql_Dbi_Database extends Ac_Sql_Dbi_Object {
    
    var $_tables = false;
    
    /**
     * @var string Table prefix to replace in table names, if any (i.e. "asm_")
     */
    var $tablePrefix = false;
    
    /**
     * @var string Placeholder to replace table prefix with (i.e. "#__")
     */
    var $replacePrefixWith = '#__';

    /**
     * Prototypes of user-defined elements of database schema
     * @var array 
     */
    var $_extras = array();

    protected function setExtras(array $extras = []) {
        $this->_extras = $extras;
    }
    
    function listTables() {
        if ($this->_tables === false) {
            $this->_tables = array();
            foreach ($this->_inspector->listTablesForDatabase($this->name) as $tableName) {
                $this->_tables[$this->prefixizeTable($tableName)] = false;    
            }
        }
        return array_keys($this->_tables);
    }
    
    /**
     * @return Ac_Sql_Dbi_Table
     */
    function getTable($tableName) {
        if (!in_array($tableName, $this->listTables())) trigger_error('No such table: \''.$tableName.'\' in db \''.$this->name.'\'', E_USER_ERROR);
        if ($this->_tables[$tableName] === false) {
            if (isset($this->_extras['tables']) && is_array($this->_extras['tables']) && isset($this->_extras['tables'][$tableName]) && is_array($this->_extras['tables'][$tableName])) $extras = $this->_extras['tables'][$tableName];
                else $extras = array();
            $this->_tables[$tableName] = new Ac_Sql_Dbi_Table([
                'inspector' => $this->_inspector, 
                'name' => $tableName, 
                'database' => $this,
                'extras' => $extras
            ]);
        }
        return $this->_tables[$tableName];
    }
    
    function prefixizeTable($tableName) {
        if ($l = strlen($this->tablePrefix)) {
            if (!strncmp($tableName, $this->tablePrefix, $l)) {
                $tableName = $this->replacePrefixWith.substr($tableName, $l);
            }
        }
        return $tableName;
    }
    
    function deprefixizeTable($tableName) {
        if ($l = strlen($this->replacePrefixWith)) {
            if (!strncmp($tableName, $this->replacePrefixWith, $l)) {
                $tableName = $this->tablePrefix.substr($tableName, $l);
            }
        }
        return $tableName;
    }
    
}

