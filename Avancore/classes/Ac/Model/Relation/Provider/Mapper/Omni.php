<?php

class Ac_Model_Relation_Provider_Mapper_Omni extends Ac_Model_Relation_Provider_Mapper_Abstract {

    /**
     * keys that are used to identify the records
     */
    protected $keys = array();

    /**
     * Sets keys that are used to identify the records
     */
    function setKeys($keys) {
        $this->keys = array_values(Ac_Util::toArray($keys));
        if (count($keys) != 1) throw new Ac_E_InvalidUsage("At the moment, only one element of \$keys is supported");
    }

    /**
     * Returns keys that are used to identify the records
     * @return array
     */
    function getKeys() {
        return $this->keys;
    }    
    
    function doGetWithValues (array $values, $byKeys = true, array $nnValues = array()) {
        if (!$this->mapper) throw new Ac_E_InvalidUsage("setMapper() first");
        $vv = $this->extractSingleValues($values);
        if (count($vv)) {
            $query = $this->query;
            $query[$this->keys[0]] = $vv;
            $res = $this->mapper->find($query, $byKeys? $this->keys[0] : false, $this->sort);
        } else {
            $res = array();
        }
        return array($res, array());
    }
    
    function doCountWithValues (array $values, $byKeys = true, array $nnValues = array()) {
        if (!$this->mapper) throw new Ac_E_InvalidUsage("setMapper() first");
        $vv = $this->extractSingleValues($values);
        if (count($vv)) {
            $groupMode = $byKeys? Ac_Model_Mapper::GROUP_KEYS : Ac_Model_Mapper::GROUP_ORDER;
            $res = $this->mapper->countWithValues($this->keys[0], $vv, $groupMode);
        } else {
            $res = array();
        }
        return array($res, array());
    }
    
}