<?php

class Ac_Sql_Filter_NNCriterion_Omni extends Ac_Sql_Filter_NNCriterion {
    
    var $midSrcKeys = false;
    
    var $midDestKeys = false;
    
    var $tableKeys = false;

    function _doGetAppliedWhere() {
        parent::_doGetAppliedWhere();
        $res = array();
        if ($this->srcKeys) {
            if (!$this->midSrcKeys)
                throw new Ac_E_InvalidUsage("\$midSrcKeys property must be set when \$srcValues are provided");
            if (!$this->midDestKeys)
                throw new Ac_E_InvalidUsage("\$midDestKeys property must be set when \$srcValues are provided");
        }
        
        return $res;
    }
    
    function _doGetSrcValuesCriterion() {
        $res = $this->makeSqlCriteria($this->srcValues, $this->midSrcKeys, $this->midTableAlias);
        return $res;
    }
    
    function _doGetDestValuesCriterion($destAlias) {
        /*$keys = array();
        $db = $this->currentSelect->getDb();
        foreach ($this->midDestKeys as $k) $keys[] = $db->n(array($destAlias, $this->tableKeys));*/
        $res = $this->makeSqlCriteria($this->destValues, $this->midDestKeys, $this->midTableAlias);
        return $res;
    }
    
    function _doGetSrcNotNullCriterion() {
        $db = $this->currentSelect->getDb();
        $a = array();
        foreach (Ac_Util::toArray($this->midSrcKeys) as $col) {
            $a[] = $res = $db->n(array($this->midTableAlias, $col))." IS NOT NULL";
        }
        $res = implode(" AND ", $a);
        return $res;
    }
    
    // Copied from Ac_Model_Relation_Provider_Omni without major changes
    protected function makeSqlCriteria($values, $keyFields, $alias = '', $default = '0') {
        if (!count($values)) return $default;
        $db = null;
        if ($this->currentSelect) {
            $db = $this->currentSelect->getDb();
        }
        if (!$db) {
            $db = Ac_Sql_Db::getDefaultInstance();
        }
        // TODO: Optimization 1: remove duplicates from values! (how??? sort keys??? make a tree???)
        // TODO: Optimization 2: make nested criterias depending on values cardinality
        $values = Ac_Util::array_unique($values); 
        $qAlias = strlen($alias)? $alias.'.' : $alias;
        if (is_array($keyFields)) {
            if (count($keyFields) === 1) {
                $qValues = array();
                $qKeyField = $db->n($keyFields[0]);
                foreach ($values as $val) {
                    $qValues[] = $db->q(is_array($val)? $val[0] : $val);
                }
                $qValues = array_unique($qValues);
                if (count($qValues) === 1) $res = $qAlias.$qKeyField.' = '.$qValues[0];
                    else $res = $qAlias.$qKeyField.' IN('.implode(",", $qValues).')';
            } else {
                $cKeyFields = count($keyFields);
                $bKeyFields = $cKeyFields - 1;
                $qKeyFields = array();
                foreach ($keyFields as $keyField) $qKeyFields[] = $qAlias.$db->n($keyField);
                $crit = array();
                foreach ($values as $valArray) {
                    $c = '';
                    for ($i = 0; $i < $bKeyFields; $i++) {
                        $c .= $qKeyFields[$i].'='.$db->q($valArray[$i]).' AND ';
                    }
                    $crit[] = $c.$qKeyFields[$bKeyFields].' = '.$db->q($valArray[$bKeyFields]);
                }
                $res = '('.implode(') OR (', $crit).')';
            }
        } else {
            $qValues = array();
            $qKeyField = $db->NameQuote($keyFields);
            foreach ($values as $val) $qValues[] = $db->Quote($val);
            if (count($qValues) === 1) $res = $qAlias.$qKeyField.' = '.$qValues[0];
                else $res = $qAlias.$qKeyField.' IN('.implode(",", $qValues).')';
        }
        return $res;
    }
    
}