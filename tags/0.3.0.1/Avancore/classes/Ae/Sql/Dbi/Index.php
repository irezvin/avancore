<?php

Ae_Dispatcher::loadClass('Ae_Sql_Dbi_Object');

class Ae_Sql_Dbi_Index extends Ae_Sql_Dbi_Object {
    
    /**
     * @var Ae_Sql_Dbi_Table
     */
    var $_table = false;
    
    var $primary = false;
    var $unique = false;
    var $columns = false;
    
    function listColumns() {
        return Ae_Util::array_values($this->columns);
    }
    
    function getColumn($name) {
        $res = $this->_table->getColumn($name);
        return $res;
    }
    
    function Ae_Sql_Dbi_Index(& $inspector, $name, & $table, $data) {
        //Ae_Util::simpleBind($data, $this);
        parent::Ae_Sql_Dbi_Object($inspector, $name);
    	$data['name'] = $name;
        $this->_assignProperties($data);
        $this->_table = $table;
    }
    
}

?>