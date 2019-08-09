<?php

class Ac_Sql_Filter_Simple extends Ac_Sql_Filter {
    
    var $where = false;
    var $having = false;

    /**
     * @access protected
     */
    function _doBind($input) {
        $this->applied = (bool) $input;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedWhere() {
        $res = array();
        if (strlen($this->where)) $res[] = $this->where;        
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedHaving() {
        $res = array();
        if (strlen($this->having)) $res[] = $this->having;      
        return $res;
    }
    
}

