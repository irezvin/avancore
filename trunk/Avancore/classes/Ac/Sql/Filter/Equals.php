<?php

class Ac_Sql_Filter_Equals extends Ac_Sql_Filter {

    var $colName = false;
    var $inputMultipleValues = true;
    var $values = array();
    
    var $convertFalseToNull = false;
    var $convertEmptyToNull = false;
    
    var $convertNullToZero = false;
    var $invert = false;
    
    function _colCriteria() {
        
        $vv = array();
        $hasNulls = false;
        foreach ($this->values as $k => $v) {
            if ($v === false && $this->convertFalseToNull) $v = null;
            elseif (!strlen($v) && $this->convertEmptyToNull) $v = null;
            
            if (is_null($v) && $this->convertNullToZero) $v = 0;
            
            if (is_null($v)) $hasNulls = true; else $vv[] = $v;
            
        }
        
        
        if (count($vv)) {
            if (count($vv) == 1) $val = $vv[0];
                else $val = $vv;
                
            $sql = $this->colName.' '.$this->_db->eqCriterion($val);
        } else {
            $sql = '';
        }
        
        if ($hasNulls) {
            if (strlen($sql)) $sql .= ' OR ';
            $sql .= "{$this->colName} IS NULL";
        } 
        
        if ($this->invert) $sql = "NOT ({$sql})";
        
        return $sql;
    }
    
    // ---------------------------------- template methods ------------------------------

    /**
     * @access protected
     */
    function _doBind($input) {
        $values = array();
        if (!is_array($input)) $input = array($input);
        $n = 0;
        foreach ($input as $item) {
            $values[] = $item;
        }
        if (!$this->inputMultipleValues) $values = array_slice($values, 0, 1);
        if (!count($values)) $this->applied = false;
        $this->values = $values;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedWhere() {
        if (!$this->isHaving && count($this->values) && $this->colName) {
            $res = array($this->_colCriteria());
        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedHaving() {
        if ($this->isHaving && count($this->values) && $this->colName) {
            $res = array($this->_colCriteria());
        } else {
            $res = array();
        }
        return $res;
    }
    
}
?>