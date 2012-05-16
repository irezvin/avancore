<?php

class Ac_Param_Filter_Array extends Ac_Param_Filter {
    
    protected $keyFilters = array();
    
    protected $keyConditions = array();
    
    protected $filters = array();
    
    protected $conditions = array();
    
    protected $maxDepth = 0;
    
    protected $flatten = false;
    
    protected $toArray = true;
    
    protected $convertEmptyToNull = true;
    
    protected $stripKeys = false;
    
    function getKeyFilters() {
        return $this->keyFilters;
    }
    
    function setKeyFilters(array $keyFilters) {
        $this->keyFilters = Ac_Autoparams::factoryCollection($keyFilters, 'Ac_I_Param_Filter');
    }
    
    function getKeyConditions() {
        return $this->keyConditions;
    }   

    function setKeyConditions(array $keyConditions) {
        $this->keyConditions = Ac_Autoparams::factoryCollection($keyConditions, 'Ac_I_Param_Condition');
    }
    
    function getFilters() {
        return $this->filters;
    }
    
    function setFilters(array $filters) {
        $this->filters = Ac_Autoparams::factoryCollection($filters, 'Ac_I_Param_Filter');
    }
    
    function getConditions() {
        return $this->conditions;
    }   

    function setConditions(array $conditions) {
        $this->conditions = Ac_Autoparams::factoryCollection($conditions, 'Ac_I_Param_Condition');
    }
    
    function setMaxDepth($maxDepth) {
        $this->maxDepth = (int) $maxDepth;
    }

    function getMaxDepth() {
        return $this->maxDepth;
    }

    function setFlatten($flatten) {
        $this->flatten = (bool) $flatten;
    }

    function getFlatten() {
        return $this->flatten;
    }
    
    function setToArray($toArray) {
        $this->toArray = $toArray;
    }

    function getToArray() {
        return $this->toArray;
    }
    
    function setConvertEmptyToNull($convertEmptyToNull) {
        $this->convertEmptyToNull = $convertEmptyToNull;
    }

    function getConvertEmptyToNull() {
        return $this->convertEmptyToNull;
    }

    function setStripKeys($stripKeys) {
        $this->stripKeys = $stripKeys;
    }

    function getStripKeys() {
        return $this->stripKeys;
    }
    
    function filter($value, Ac_Param $param = null) {
        $res = $value;
        if (!is_array($res)) {
            if ($this->toArray) $res = Ac_Util::toArray($res);
            else $res = null;
        }
        if (is_array($res)) {

            $hv = ($this->conditions || $this->filters);
            $hk = ($this->keyConditions || $this->keyFilters);
            
            if ($hk || $hv) {
                $tmp = $res;
                $res = array();
                foreach ($tmp as $k => $v) {
                    if ($this->stripKeys) $k = count($res);
                    if ($hk) {
                        $k = Ac_Param::applyConditionsAndFilters($k, $this->keyConditions, $this->keyFilters, $keyOk);
                        if (is_null($k)) $keyOk = false;
                    } else {
                        $keyOk = true;
                    }
                    if ($keyOk) {
                        if ($hv) $v = Ac_Param::applyConditionsAndFilters($v, $this->conditions, $this->filters, $vOk);
                            else $vOk = true;
                        if ($vOk) {
                            $res[$k] = $v;
                        }
                    }
                }
            }            
            
            if (!count($res) && $this->convertEmptyToNull) $res = null;
        }
        
        return $res;
    }
    
}