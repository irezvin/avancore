<?php

class Ac_Model_Relation_Provider_Mapper_Omni extends Ac_Model_Relation_Provider_Mapper_Abstract {

    /**
     * keys that are used to identify the records
     */
    protected $keys = array();

    protected $n = false;
    
    /**
     * name of mapper criterion to search items
     * @var string
     */
    protected $criterionName = false;
    
    /**
     * Sets keys that are used to identify the records
     */
    function setKeys($keys) {
        $this->keys = array_values(Ac_Util::toArray($keys));
        $this->n = count($keys);
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
        if ($this->n == 1) {
            $vv = $this->extractSingleValues($values);
            if (count($vv)) {
                $query = $this->query;
                if (strlen($this->criterionName)) {
                    $query[$this->criterionName] = $vv;
                } else {
                    $query[$this->keys[0]] = $vv;
                }
                $res = $this->mapper->find($query, $byKeys? $this->keys[0] : false, $this->sort);
            } else {
                $res = array();
            }
        } else {
            $vv = $this->extractMultiValues($values);
            if (count($vv)) {
                $query = $this->query;
                if (strlen($this->criterionName)) {
                    $query[$this->criterionName] = $vv;
                } else {
                    $query[implode('_', $this->keys)] = new Ac_Model_Criterion_MultiField(array(
                        'fields' => $this->keys,
                        'values' => $vv,
                    ));
                }
                $res = $this->mapper->find($query, $this->keys[0], $this->sort);
            } else {
                $res = array();
            }
        }
        return array($res, array());
    }
    
    function doCountWithValues (array $values, $byKeys = true, array $nnValues = array()) {
        if (!$this->mapper) throw new Ac_E_InvalidUsage("setMapper() first");
        $vv = $this->n == 1? $this->extractSingleValues($values) : $this->extractMultiValues($values);
        if (count($vv)) {
            $groupMode = $byKeys? Ac_Model_Mapper::GROUP_VALUES : Ac_Model_Mapper::GROUP_ORDER;
            $query = $this->query;
            if ($this->criterionName) {
                $query[$this->criterionName] = $vv;
                $useQueryOnly = true;
            } else {
                $useQueryOnly = false;
            }
            $res = $this->mapper->countWithValues($this->keys, $vv, $groupMode, $query, $useQueryOnly);
        } else {
            $res = array();
        }
        return array($res, array());
    }
    
    protected function extractMultiValues(array $values) {
        $res = array();
        foreach ($values as $k => $v) {
            if (!is_array($v)) $v = array($v);
            if (is_array($v)) {
                $v = array_values($v);
                if (($vCount = count($v)) == $this->n) {
                    $res[$k] = $v;
                } else {
                    throw new Exception("Number of values in foreign key must match number of elements in \$keys property,"
                        . " but item '{$k}' has {$vCount} items instead of {$this->n}");
                }
            } else {
                $res[$k] = $v;
            }
        }
        return $res;
    }

    /**
     * Sets name of mapper criterion to search items
     * @param string $criterionName
     */
    function setCriterionName($criterionName) {
        $this->criterionName = $criterionName;
    }

    /**
     * Returns name of mapper criterion to search items
     * @return string
     */
    function getCriterionName() {
        return $this->criterionName;
    }
    
}