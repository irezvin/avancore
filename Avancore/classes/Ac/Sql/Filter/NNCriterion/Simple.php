<?php

class Ac_Sql_Filter_NNCriterion_Simple extends Ac_Sql_Filter_NNCriterion {
    
    var $leftNNCol = false;
    
    var $rightNNCol = false;
    
    var $tableKey = false;

    function _doApplyToSelect($select) {
        $nnTable = $select->getTable($this->nnTableAlias);
        $tmp = $nnTable->joinsOn;
        $joinsOn = $nnTable->getJoinsOn();
        $nnTable->joinsOn = "(".$joinsOn.") AND (1)";
        $select->joinOverrides[$this->nnTableAlias] = $nnTable->getJoinClausePart();
        $nnTable->joinsOn = $tmp;
        parent::_doApplyToSelect($select);
    }
    
    function _doGetAppliedWhere() {
        parent::_doGetAppliedWhere();
        $res = array();
        if ($this->leftKeys) {
            if (!strlen($this->leftNNCol))
                throw new Ac_E_InvalidUsage("\$leftNNCol property must be set when \$leftValues are provided");
            if (!strlen($this->rightNNCol))
                throw new Ac_E_InvalidUsage("\$rightNNCol property must be set when \$leftValues are provided");
        }
        
        return $res;
    }
    
    function _doGetLeftValuesCriterion() {
        $db = $this->currentSelect->getDb();
        $res = $db->n(array($this->nnTableAlias, $this->leftNNCol)).$db->eqCriterion($this->leftValues);
        return $res;
    }

    function _doGetRightValuesCriterion($rightAlias) {
        $db = $this->currentSelect->getDb();
        $res = $db->n(array($rightAlias, $this->tableKey)).$db->eqCriterion($this->rightValues);
        return $res;
    }
    
    function _doGetLeftNotNullCriterion() {
        $db = $this->currentSelect->getDb();
        $res = $db->n(array($this->nnTableAlias, $this->leftNNCol))." IS NOT NULL";
        return $res;
    }
    
    
}