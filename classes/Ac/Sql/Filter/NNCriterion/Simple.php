<?php

class Ac_Sql_Filter_NNCriterion_Simple extends Ac_Sql_Filter_NNCriterion {
    
    var $midSrcKey = false;
    
    var $midDestKey = false;
    
    var $tableKey = false;

    function _doApplyToSelect($select) {
        $midTable = $select->getTable($this->midTableAlias);
        $tmp = $midTable->joinsOn;
        $joinsOn = $midTable->getJoinsOn();
        $midTable->joinsOn = "(".$joinsOn.") AND (1)";
        $select->joinOverrides[$this->midTableAlias] = $midTable->getJoinClausePart();
        $midTable->joinsOn = $tmp;
        parent::_doApplyToSelect($select);
    }
    
    function _doGetAppliedWhere() {
        parent::_doGetAppliedWhere();
        $res = array();
        if ($this->srcKeys) {
            if (!strlen($this->midSrcKey))
                throw new Ac_E_InvalidUsage("\$midSrcKey property must be set when \$srcValues are provided");
            if (!strlen($this->midDestKey))
                throw new Ac_E_InvalidUsage("\$midDestKey property must be set when \$srcValues are provided");
        }
        
        return $res;
    }
    
    function _doGetSrcValuesCriterion() {
        $db = $this->currentSelect->getDb();
        $res = $db->n(array($this->midTableAlias, $this->midSrcKey)).$db->eqCriterion($this->srcValues);
        return $res;
    }

    function _doGetDestValuesCriterion($destAlias) {
        $db = $this->currentSelect->getDb();
        $res = $db->n(array($destAlias, $this->tableKey)).$db->eqCriterion($this->destValues);
        return $res;
    }
    
    function _doGetSrcNotNullCriterion() {
        $db = $this->currentSelect->getDb();
        $res = $db->n(array($this->midTableAlias, $this->midSrcKey))." IS NOT NULL";
        return $res;
    }
    
    
}