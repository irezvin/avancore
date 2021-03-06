<?php

/**
 * 
 */

/**
 * Retrieves records from the mapper while assuming that foreign key matches record primary key (in relational terms, 
 * or "record identifier" in our terms)
 */
class Ac_Model_Relation_Provider_Mapper_Pk extends Ac_Model_Relation_Provider_Mapper_Abstract {

    protected $unique = true;
        
    function doGetWithValues (array $destValues, $byKeys = true, array $srcValues = array()) {
        if (!$this->mapper) throw new Ac_E_InvalidUsage("setMapper() first");
        $vv = $this->extractSingleValues($destValues);
        if (count($vv)) {
            $query = $this->query;
            $query[Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION] = $vv;
            if ($byKeys) {
                $idx = $this->unique? true : array($this->mapper->getIdentifierPublicField(), false);
            } else {
                $idx = false;
            }
            $res = $this->mapper->find($query, $idx, $this->sort);
        } else {
            $res = array();
        }
        return array($res, array());
    }
    
    function doCountWithValues (array $values, $byKeys = true, array $nnValues = array()) {
        if (!$this->mapper) throw new Ac_E_InvalidUsage("setMapper() first");
        $vv = $this->extractSingleValues($destValues);
        if (count($vv)) {
            $groupMode = $byKeys? Ac_Model_Mapper::GROUP_VALUES : Ac_Model_Mapper::GROUP_ORDER;
            $res = $this->mapper->countWithValues($this->mapper->getIdentifierPublicField(), $vv, $groupMode);
        } else {
            $res = array();
        }
        return array($res, array());
    }

}