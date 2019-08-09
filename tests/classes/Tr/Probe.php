<?php

abstract class Tr_Probe {

    protected $key = false;
        
    /**
     * @var Tr_Probe_List
     */
    protected $probeList = false;
    
    /**
     * @var array
     */
    protected $dependencies = array();
    
    function setProbeList(Tr_Probe_List $probeList) {
        if ($this->probeList !== false && ($this->probeList !== $probeList))
            throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->probeList = $probeList;
    }

    /**
     * @return Tr_Probe_List
     */
    function getProbeList() {
        return $this->probeList;
    }
    
    function setKey($key) {
        if ($this->key !== false && ($this->key !== $key))
            throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->key = $key;
    }
    
    function getKey() {
        return $this->key;
    }

    function setDependencies(array $dependencies, $add = false) {
        if ($add) foreach ($dependencies as $k => $v) $this->dependencies[$k] = $v;
        else $this->dependencies = $dependencies;
    }

    /**
     * @return array
     */
    function getDependencies() {
        return $this->dependencies;
    }    
    
    function getSource() {
        return $this->getProbeList()->getSource();
    }
    
    function getIsApplicable() {
        $res = true;
        foreach ($this->getDependencies() as $probeName => $callback) {
            if (is_numeric($probeName) && is_string($callback)) {
                $probeName = $callback;
                $callback = array('resultEquals', true);
            }
            if (is_string($callback)) $callback = array($callback);
            $callback = array_values($callback);
            if (!is_array($callback[0])) {
                $hasProbe = $this->getProbeList() && in_array($callback[0], $this->getProbeList()->listProbes());
                if (!$hasProbe) {
                    $res = false;
                    break;
                }
                $callback[0] = array($this->getProbeList()->getProbe($callback[0]));
            }
            if (!call_user_func_array($callback[0], array_slice($callback, 1))) {
                $res = false;
                break;
            }
        }
        return $res;
    }
    
    function resultEquals($value, $strict = false) {
        $r = $this->getResult($wasApplicable);
        if (!$wasApplicable) $res = false;
        else {
            if ($strict) $res = $r === $value;
                else $res = $r == $value;
        }
        return $res;
    }

    function getResult(& $wasApplicable = false) {
        if ($wasApplicable = $this->getIsApplicable()) $res = $this->doGetResult ();
        else $res = null;
        return $res;
    }
    
    abstract function doGetResult();
    
}