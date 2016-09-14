<?php

class Ac_Param_Source_Array extends Ac_Prototyped implements Ac_I_Param_Destination {
    
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
    
    function hasParamValue(array $path) {
        $this->getParamValue($path, null, $res);
        return $res;
    }
    
    function getParamValue(array $path, $default = null, & $found = null) {
        $src = & $this->data;
        $path = array_values($path);
        $found = true;
        for ($i = 0, $c = count($path); $i < $c; $i++) {
            $key = $path[$i];
            if (is_array($src) && isset($src[$key])) $src = & $src[$key];
                else {
                    $hasValue = false;
                    return $default;
                }
        }
        return $src;
    }
    
    function setParamValue(array $path, $value) {
        Ac_Util::setArrayByPathRef($this->data, $path, $value);
    }
    
    function deleteParamValue(array $path) {
        Ac_Util::unsetArrayByPath($this->data, $path);
    }
    
}