<?php

class Ae_Param_Parent extends Ae_Param {

    protected $params = array();
    
    /**
     * @param $child
     * @param $childSource
     * @param $childPath
     * 
     * @return Ae_I_Param_Source
     */
    function getSourceForChild(Ae_Param $child, & $childSource, & $childPath) {
        return $childSource? $childSource : $this->source;
    }
    
    /**
     * @param $child
     * @param $childDest
     * @param $childPath
     * 
     * @return Ae_I_Param_Destination
     */
    function getDestinationForChild(Ae_Param $child, & $childDest, & $childPath) {
        return $childDest? $childDest : $this->destination;
    }
    
    function setParams(array $params) {
        if ($this->params) {
            $tmp = $this->params;
            $this->params = array();
            foreach ($tmp as $param) $tmp->setParent(null);
        }
        Ae_Autoparams::factoryCollection($params, 'Ae_Param', array('parent' => $this), 'id', 'id', true, $this->params);
    }
    
    function listParams() {
        return array_keys($this->params);
    }
    
    function addParam(Ae_Param $param) {
        $id = $param->getId();
        if (isset($this->params[$id]) && $this->params[$id] !== $param) {
            throw new Exception("Param '{$newId}' is already registered in parent '".$this->getIdPath()."'");
        }
        if (!in_array($param, $this->params, $this)) $this->params[$param->getId()] = $param;
        if ($param->getParent() !== $this) $param->setParent($this);
    }
    
    function getParam($id) {
        if (!isset($this->params[$id])) throw new Exception("No such child param '{$id}'");
        else 
            $res = $this->params[$id];
        return $res;
    }
    
    function hasParam(Ae_Param $param) {
        $res = $this->params && isset($this->params[$id = $param->getId()]) && $this->params[$id] == $param;
        return $res;
    }
    
    function renameParam($oldId, $newId, Ae_Param $param) {
        if (isset($this->params[$oldId]) && $this->params[$oldId] === $param) unset($this->params[$oldId]);
        if (!isset($this->params[$newId])) $this->params[$newId] = $param;
        else if ($this->params[$newId] !== $param) 
            throw new Exception("Param '{$newId}' is already registered in parent '".$this->getIdPath()."'");
        if ($param->getId() !== $newId) $param->setId($this->id);
    }
    
    function removeParam(Ae_Param $param) {
        if ($this->hasParam($param)) {
            unset($this->params[$param->getId()]);
            if ($param->getParent() === $this) $param->setParent(null);
            $res = true;
        } else $res = false;
        return $res;
    }       
    
}