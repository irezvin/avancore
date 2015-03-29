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
    
    function Ac_Sql_Dbi_Index(& $inspector, $name, $table, $data) {
        //Ac_Util::simpleBind($data, $this);
        parent::Ac_Sql_Dbi_Object($inspector, $name);
    	$data['name'] = $name;
        $this->_assignProperties($data);
        $this->_table = $table;
    }
    
}

