<?php

class Ac_Accessor_Strategy_UseMethod extends Ac_Prototyped implements Ac_I_AccessorStrategy {
    
    protected $getMethodName = false;

    protected $setMethodName = false;

    protected $hasMethodName = false;

    protected $listMethodName = false;
    
    
    
    function getPropertyOf($object, $name) {
        if (!$this->getMethodName) throw new Ac_E_InvalidUsage("Cannot read from a write-only property");
        return $object->{$this->getMethodName} ($name);
    }
    
    function testPropertyOf($object, $name) {
        return $object->{$this->hasMethodName} ($name);
    }
    
    function setPropertyOf($object, $name, $value) {
        if (!$this->setMethodName) throw new Ac_E_InvalidUsage("Cannot write to a read-only property");
        return $object->{$this->setMethodName} ($name);
    }
    
    function listPropertiesOf($object) {
        return $object->{$this->listMethodName}();
    }

    
    
    protected function setGetMethodName($getMethodName) {
        $this->getMethodName = $getMethodName;
    }

    function getGetMethodName() {
        return $this->getMethodName;
    }

    protected function setSetMethodName($setMethodName) {
        $this->setMethodName = $setMethodName;
    }

    function getSetMethodName() {
        return $this->setMethodName;
    }

    protected function setHasMethodName($hasMethodName) {
        $this->hasMethodName = $hasMethodName;
    }

    function getHasMethodName() {
        return $this->hasMethodName;
    }    
    
    protected function setListMethodName($listMethodName) {
        $this->listMethodName = $listMethodName;
    }

    function getListMethodName() {
        return $this->listMethodName;
    }    
}