<?php

class Ac_Sql_Dbi_Column extends Ac_Sql_Dbi_Object {

    /**
     * @var Ac_Sql_Dbi_Table
     */
    var $_table = false;
    
    var $type = false;
    var $width = false;
    var $decimals = false;
    var $default = false;
    var $autoInc = false;
    var $enumValues = false;
    var $comment = false;
    var $unsigned = false;
    var $nullable = false;
    
    function __construct(& $inspector, $name, $table, $data) {
        $data['name'] = $name;
    	//Ac_Util::simpleBind($data, $this);
    	$this->_assignProperties($data);
        parent::__construct($inspector, $name);
        $this->_table = $table;
    }
    
    /**
     * @return Ac_Sql_Dbi_Column 
     */
    function pointsToSingleForeignColumn() {
        $fcs = array();
        foreach ($this->_table->listRelations() as $r) {
            $rel = $this->_table->getRelation($r);
            if (isset($rel->columns[$this->name])) {
                $fcs[$rel->table][$rel->columns[$this->name]] = true;
            }
        }
        
        $res = false;
        
        if (count($fcs) == 1) {
            $ks = array_keys($fcs);
            if (count($fcs[$ks[0]]) == 1) {
                 $kks = array_keys($fcs[$ks[0]]);
                 $ft = $this->_table->_database->getTable($ks[0]);
                 $res = $ft->getColumn($kks[0]);
            }
        }
        
        return $res;
    }
    
    function isInRelation() {
        foreach ($this->_table->listRelations() as $r) {
            $rel = $this->_table->getRelation($r);
            if (isset($rel->columns[$this->name])) return true;
        }
        return false;
    }
    
    function isInPk() {
    	return in_array($this->name, $this->_table->listPkFields());
    }
    
    function isPk() {
        $pkf = $this->_table->listPkFields();
        $res = (count($pkf) == 1) && $pkf[0] == $this->name;
        return $res; 
    }
    
    function isUnique() {
        $res = false;
        if ($this->isPk()) {
            $res = true;
        } else {
            $res = (bool) $this->_table->findIndicesByColumns(array($this->name), Ac_Sql_Dbi_Table::INDEX_ACCEPT_SAME, true);
        }
        return $res;
    }
    
}

