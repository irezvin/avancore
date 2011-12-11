<?php

class Ae_Param_Source_Array extends Ae_Autoparams implements Ae_I_Param_Destination {
    
    protected $data = array();
    
    function __construct(array $options = array()) {
        $o = $options;
        unset($o['data']);
        parent::__construct($o);
        if (isset($options['data'])) $this->setData($options['data']);
    }
    
    function setData(array & $data) {
        $this->data = & $data;
    }
    
    function hasValue(array $path) {
        $this->getValue($path, null, $res);
        return $res;
    }
    
    function getValue(array $path, $default = null, & $found = null) {
        $src = & $this->data;
        $path = array_values($path);
        $found = true;
        for ($i = 0, $c = count($path); $i < $c; $i++) {
            $key = $path[$i];
            if (is_array($src) && isset($src[$key])) $src = & $src[$key];
                else {
                    $hasValue = false;
                    return $defaultValue;
                }
        }
        return $src;
    }
    
    function setValue(array $path, $value) {
        Ae_Util::simpleSetArrayByPath($this->data, $path, $value);
    }
    
    function deleteValue(array $path) {
        Ae_Util::unsetArrayByPath($this->data, $path);
    }
    
}