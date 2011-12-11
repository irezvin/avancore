<?php

if (class_exists('Ae_Dispatcher')) Ae_Dispatcher::loadClass('Ae_Sql_Part');
elseif (!class_exists('Ae_Sql_Part')) require('Ae/Sql/Part.php');

class Ae_Sql_Filter extends Ae_Sql_Part {

    /**
     * This filter should be placed into HAVING... clause instead of WHERE... clause of SQL select statement
     */
    var $isHaving = false;
    
    function getAppliedWhere() {
        $res = array();
        if ($this->doesApply()) $res = $this->_doGetAppliedWhere();
        $res = $this->_applyPrefix($res);
        return $res;
    }
    
    function getAppliedHaving() {
        $res = array();
        if ($this->doesApply()) $res = $this->_doGetAppliedHaving();
        $res = $this->_applyPrefix($res);
        return $res;
    }
    
    // ---------------------------------- template methods ------------------------------
    
    /**
     * @access protected
     */
    function _doGetAppliedWhere() {
        return array();
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedHaving() {
        return array();
    }
    
    /**
     * @access protected
     * @param Ae_Sql_Select $select
     */
    function _doApplyToSelect(& $select) {
        parent::_doApplyToSelect($select);
        $select->where = array_merge($select->where, $this->getAppliedWhere());
        $select->having = array_merge($select->having, $this->getAppliedHaving());
    }
    
}

?>