<?php

class Ac_Param_Block extends Ac_Autoparams {
    
    protected $params = array();
    
    protected $source = false;
    
    var $overrides = array();
    
    private $overridesAssigned = array();
    
    function hasPublicVars() {
        return true;
    }
    
    function setParams(array $params) {
        $this->params = $params;
        if ($this->source) $this->assignSource($params);
    }

    function getParams() {
        $res = array();
        foreach ($this->params as $i => $p) {
            if (! $p instanceof Ac_I_Param) {
                $p = $this->getParam($i);
            }
            $res[$i] = $p;
        }
        return $res;
    }
    
    function listParams() {
        return array_keys($this->params);
    }
    
    function getValues($list = false) {
        if (!is_array($list)) $list = $this->listParams();
        $res = array();
        foreach ($list as $i) {
            $res[$i] = $this->getParam($i)->getValue();
        }
        return $res;
    }
    
    function setSource(Ac_I_Param_Source $source = null) {
        if ($source !== ($oldSource = $this->source)) {
            $this->source = $source;
            $this->assignSource($this->params, $oldSource);
        }
    }

    /**
     * @return Ac_I_Param_Source
     */
    function getSource() {
        return $this->source;
    }
    
    protected function assignSource ($params, $oldSource = null) {
        foreach ($params as $param) {
            if (is_object($param) && $param instanceof Ac_I_Param_WithSource) {
                $oldSrc = $param->getSource();
                if ((!$oldSrc && !$oldSource) || ($oldSrc === $oldSource)) $param->setSource($this->source);
            }
        }
    }

    /**
     * @return Ac_I_Param
     */
    function getParam($id) {
        $res = null;
        if (isset($this->params[$id])) {
            if (!is_object($this->params[$id])) {
                 if (!isset($this->params[$id]['class'])) $this->params[$id]['class'] = 'Ac_Param';
                 if (!isset($this->params[$id]['id'])) $this->params[$id]['id'] = $id;
                 if (array_key_exists($id, $this->overrides)) $this->params[$id]['value'] = $this->overrides[$id];
                 $this->params[$id] = Ac_Autoparams::factory($this->params[$id], 'Ac_I_Param');
                 if ($this->source && $this->params[$id] instanceof Ac_I_Param_WithSource && !$this->params[$id]->getSource()) {
                     $this->params[$id]->setSource($this->source);
                 }
            } else {
                if (array_key_exists($id, $this->overrides) && !array_key_exists($id, $this->overridesAssigned)) {
                    $this->overridesAssigned[$id] = true;
                    $this->params[$id]->setValue($this->overrides[$id]);
                } 
            }
            $res = $this->params[$id];
        }
        if (is_null($res)) throw new Exception("No such param: '{$id}'");
        return $res;
    }
    
    function getValue($id) {
        if (array_key_exists($id, $this->overrides)) $res = $this->overrides[$id]; 
            else $res = $this->getParam($id)->getValue();
        return $res;
    }
    
    function __get($key) {
        return $this->getValue($key);
    }
    
}