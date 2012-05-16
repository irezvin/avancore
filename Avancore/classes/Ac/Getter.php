<?php

class Ac_Getter implements Ac_I_Getter {
    
    protected $path = array();
    protected $isMultiple = false;
    
    function __construct($path, $isMultiple = false) {
        $this->isMultiple = $isMultiple;
        if (!$this->isMultiple) {
            if (!is_array($path)) $path = Ac_Util::pathToArray($path);
        }
        $this->path = $path;
    }
    
    function get($object, $defaultValue = null) {
        if ($this->isMultiple) $res = Ac_Autoparams::getObjectProperty ($object, $this->path, $defaultValue);
        else $res = Ac_Autoparams::getObjectPropertyByPath($object, $this->path, $defaultValue);
        return $res;
    }
    
    function __toString() {
        $res = $this->isMultiple? implode(", ", Ac_Util::toArray($this->path)) : Ac_Util::arrayToPath($this->path);
        return $res;
    }
    
}