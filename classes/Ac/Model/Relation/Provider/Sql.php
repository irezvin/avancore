<?php

abstract class Ac_Model_Relation_Provider_Sql extends Ac_Model_Relation_Provider {
    

    /**
     * database adapter
     * @var Ac_Sql_Db
     */
    protected $db = false;

    /**
     * Sets database adapter
     */
    function setDb(Ac_Sql_Db $db) {
        $this->db = $db;
    }

    /**
     * Returns database adapter
     * @return Ac_Sql_Db
     */
    function getDb() {
        return $this->db;
    }
    
    /**
     * Makes SQL criteria to select multiple records with given key values and names.
     * @param array $values Array of single or composite keys. Note: composite keys are expected to be numeric arrays ordered as $keyFields. No checks are performed!
     * @param array|string $keyFields Name of key field(s). If $keyfields is string, $values elements should be scalars, otherwise arrays are expected
     * @param string alias Table alias
     * @param mixed $default Crtieria to return when $values is an empty array
     * @return string
     **/
    protected function makeSqlCriteria($values, $keyFields, $alias = '', $default = '0') {
        if (!count($values)) return $default;
        // TODO: Optimization 1: remove duplicates from values! (how??? sort keys??? make a tree???)
        // TODO: Optimization 2: make nested criterias depending on values cardinality
        $values = Ac_Util::array_unique($values); 
        $db = $this->db;
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
                    $crit[] = $c.$qKeyFields[$bKeyFields].' = '
                        .$db->q($valArray[$bKeyFields]);
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