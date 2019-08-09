<?php

class Ac_Param_Source_Object extends Ac_Prototyped implements Ac_I_Param_Destination {
    
    protected $tmp = null;
    
    /**
     * @var object
     */
    protected $data = false;
    
    function __construct(array $options = array()) {
        $this->tmp = new stdClass();
        $o = $options;
        unset($o['data']);
        parent::__construct($o);
        if (isset($options['data'])) $this->setData($options['data']);
    }
    
    function setData($data) {
        if (!is_object($data)) throw new Exception("\$data must be an object");
        $this->data = $data;
    }
    
    function hasParamValue(array $path) {
        $this->getParamValue($path, null, $res);
        return $res;
    }
    
    function getParamValue(array $path, $default = null, & $found = null) {
        if (count($path)) {
            $curr = $this->data;
            $p = array_values($path);
            while (($seg = array_splice($p, 0, 1, array())) && is_object($curr) && ($curr !== $this->tmp)) {
                $seg = $seg[0];
                $curr = Ac_Accessor::getObjectProperty($curr, $seg, $this->tmp);
            }
            if (!count($p)) {
                $found = $curr !== $this->tmp;
                $res = $found? $curr : $default;
            } else {
                $res = $default;
                $found = false;
            }
        } else {
            $found = true;
            $res = $this->data;
        }
        return $res;
    }
    
    function setParamValue(array $path, $value) {
        $res = true;
        if (count($path)) {
            $p = $path;
            $prop = array_splice($p, count($p) - 1, 1);
            $target = $this->getParamValue($p, null, $found);
            if ($target) $res = Ac_Accessor::setObjectProperty($target, $prop, $value); 
                else $res = false;
        } else {
            $this->setData($value);
        }
        return $res;
    }
    
    function deleteParamValue(array $path) {
        Ac_Util::unsetArrayByPath($this->data, $path);
    }
    
}