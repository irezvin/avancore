<?php

class Ac_Sql_Dbi_Index extends Ac_Sql_Dbi_Object {
    
    /**
     * @var Ac_Sql_Dbi_Table
     */
    var $_table = false;
    
    var $primary = false;
    var $unique = false;
    var $columns = false;
    
    function listColumns() {
        return Ac_Util::array_values($this->columns);
    }
    
    function getColumn($name) {
        $res = $this->_table->getColumn($name);
        return $res;
    }
    
    protected function setTable(Ac_Sql_Dbi_Table $table) {
        $this->_table = $table;
    }
    
}

