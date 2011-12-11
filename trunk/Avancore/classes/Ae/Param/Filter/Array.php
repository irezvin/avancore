<?php

class Ae_Param_Filter_Array extends Ae_Param_Filter {
    
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
        $this->keyFilters = Ae_Autoparams::factoryCollection($keyFilters, 'Ae_I_Param_Filter');
    }
    
    function getKeyConditions() {
        return $this->keyConditions;
    }   

    function setKeyConditions(array $keyConditions) {
        $this->keyConditions = Ae_Autoparams::factoryCollection($keyConditions, 'Ae_I_Param_Condition');
    }
    
    function getFilters() {
        return $this->filters;
    }
    
    function setFilters(array $filters) {
        $this->filters = Ae_Autoparams::factoryCollection($filters, 'Ae_I_Param_Filter');
    }
    
    function getConditions() {
        return $this->conditions;
    }   

    function setConditions(array $conditions) {
        $this->conditions = Ae_Autoparams::factoryCollection($conditions, 'Ae_I_Param_Condition');
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
    
    function filter($value, Ae_Param $param = null) {
        $res = $value;
        if (!is_array($res)) {
            if ($this->toArray) $res = Ae_Util::toArray($res);
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
                        $k = Ae_Param::applyConditionsAndFilters($k, $this->keyConditions, $this->keyFilters, $keyOk);
                        if (is_null($k)) $keyOk = false;
                    } else {
                        $keyOk = true;
                    }
                    if ($keyOk) {
                        if ($hv) $v = Ae_Param::applyConditionsAndFilters($v, $this->conditions, $this->filters, $vOk);
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