<?php

class Ae_Getter implements Ae_I_Getter {
    
    protected $path = array();
    protected $isMultiple = false;
    
    function __construct($path, $isMultiple = false) {
        $this->isMultiple = $isMultiple;
        if (!$this->isMultiple) {
            if (!is_array($path)) $path = Ae_Util::pathToArray($path);
        }
        $this->path = $path;
    }
    
    function get($object, $defaultValue = null) {
        if ($this->isMultiple) $res = Ae_Autoparams::getObjectProperty ($object, $this->path, $defaultValue);
        else $res = Ae_Autoparams::getObjectPropertyByPath($object, $this->path, $defaultValue);
        return $res;
    }
    
    function __toString() {
        $res = $this->isMultiple? implode(", ", Ae_Util::toArray($this->path)) : Ae_Util::arrayToPath($this->path);
        return $res;
    }
    
}