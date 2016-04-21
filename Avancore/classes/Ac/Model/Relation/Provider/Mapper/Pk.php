<?php

/**
 * 
 */

/**
 * Retrieves records from the mapper while assuming that foreign key matches record primary key (in relational terms, 
 * or "record identifier" in our terms)
 */
class Ac_Model_Relation_Provider_Mapper_Pk extends Ac_Model_Relation_Provider_Mapper_Abstract {
    
    function getWithValues (array $values, $byKeys = true, array $nnValues = array()) {
        if (!$this->mapper) throw new Ac_E_InvalidUsage("setMapper() first");
        $vv = $this->extractSingleValues($values);
        if (count($vv)) {
            $query = $this->query;
            $query[Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION] = $vv;
            $res1 = $this->mapper->find($query, (bool) $byKeys, $this->sort);
        } else {
            $res1 = array();
        }
        if (count($nnValues)) {
            $res2 = $this->getWithValues($nnValues, $byKeys);
        } else {
            $res2 = array();
        }
        return array($res1, $res2);
    }
    
    function countWithValues (array $values, $byKeys = true, array $nnValues = array()) {
        if (!$this->mapper) throw new Ac_E_InvalidUsage("setMapper() first");
        if ($values && $nnValues && $byKeys) {
            throw Ac_E_InvalidUsage("Cannot use both \$values and \$nnValues with \$byKeys === true");
            
        }
    }
    
    
}