<?php

if (class_exists('Ae_Dispatcher')) Ae_Dispatcher::loadClass('Ae_Sql_Filter');
elseif (!class_exists('Ae_Sql_Filter_Equals')) require('Ae/Sql/Filter/Equals.php');

class Ae_Sql_Filter_String extends Ae_Sql_Filter_Equals {

    var $caseSensitive = true;
    
    function _colCriteria() {
        $cn = $this->colName;
        if (!$this->caseSensitive) $cn = 'LCASE('.$cn.')';
        $crit = array();
        foreach ($this->values as $v) {
            $v = $this->_db->q($v);
            if (!$this->caseSensitive) $v = 'LCASE('.$v.')';
            $crit[] = $cn.' = '.$v;
        }
        if (count($crit) === 1) $sql = $crit[0];
            else $sql = implode(' OR ', $crit[0]);
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