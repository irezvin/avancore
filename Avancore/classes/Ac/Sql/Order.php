<?php
/**
 * @package
 * @copyright    (c) 2008 Ilya Rezvin
 * @author         Ilya Rezvin <ilya@rezvin.com>
 * @version        $Id$
 */

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
        $select->orderBy = array_merge($select->orderBy, $this->getAppliedOrderBy());
    }
    
}
