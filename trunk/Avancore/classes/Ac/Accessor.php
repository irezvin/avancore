<?php

// TODO: implement $convertToPaths (foo__bar -> array('foo', bar'); check Ac_Autoparams static methods to work with paths)
// TODO: some sane logic on trying to get write-only properties or to write read-only properties
// TODO: errors & exceptions handling (how?)
// TODO: add __unset() support and iterator for listable objects
class Ac_Accessor {
    
    protected $src = null;

    protected $convertToPaths = false;
    
    protected $strategy = false;
    
    function getSrc() {
        return $this->src;
    }
    
    function __construct($src, $convertToPaths = false, $strategy = false) {
        $this->src = $src;
        $this->convertToPaths = $convertToPaths;
        if ($strategy) $this->strategy = Ac_Autoparams::factoryOnce($strategy, 'Ac_I_AccessorStrategy');
    }
    
    function getProperty($name) {
        if ($this->convertToPaths) $name = $this->convertToPath($name);
        if ($this->strategy) return $this->strategy->getPropertyOf($this->src, $name);
            else return Ac_Autoparams::getObjectProperty($this->src, $name);
    }
    
    function hasProperty($name) {
        if ($this->convertToPaths) $name = $this->convertToPath($name);
        if ($this->strategy) return $this->strategy->testPropertyOf($this->src, $name);
        return Ac_Autoparams::objectPropertyExists($this->src, $name);
    }
    
    function setProperty($name, $value) {
        if ($this->convertToPaths) $name = $this->convertToPath($name);
        if ($this->strategy) return $this->strategy->setPropertyOf($this->src, $name, $value);
        return Ac_Autoparams::setObjectProperty($this->src, $name, $value);
    }
    
    // TODO: difference for setters & getters?
    function listProperties() {
        if ($this->strategy) return $this->strategy->listPropertiesOf($this->src);
        return Ac_Autoparams::listObjectProperties($this->src);
    }
    
    function convertToPath($name) {
        return is_array($name)? $name : explode('__', $name);
    }
    
    function __get($name) {
        return $this->getProperty($name);
    }
    
    function __set($name, $value) {
        return $this->setProperty($name, $value);
    }
    
    function __isset($name) {
        return $this->hasProperty($name);
    }
    
}