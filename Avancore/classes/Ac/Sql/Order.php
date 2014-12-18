<?php

class Ac_Sql_Order extends Ac_Sql_Part {

    function getAppliedOrderBy() {
        $res = array();
        if ($this->doesApply()) $res = $this->_doGetAppliedOrderBy();
        $res = $this->_applyPrefix($res);
        return $res;
    }
    
    // ---------------------------------- template methods ------------------------------
    
    /**
     * @access protected
     */
    function _doGetAppliedOrderBy() {
        return array();
    }
    
    /**
     * @access protected
     * @param Ac_Sql_Select $select
     */
    function _doApplyToSelect($select) {
        parent::_doApplyToSelect($select);
        $select->orderBy = Ac_Util::m($select->orderBy, $this->getAppliedOrderBy());
    }
    
}
