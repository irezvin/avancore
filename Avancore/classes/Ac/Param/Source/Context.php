<?php

class Ac_Param_Source_Context extends Ac_Autoparams implements Ac_I_Param_Destination {
    
    /**
	 * @var Ac_Legacy_Controller_Context 
     */
    protected $data = false;
    
    protected $tmp = false;
    
    function __construct(array $options = array()) {
        $this->tmp = new stdClass();
        $o = $options;
        unset($o['data']);
        parent::__construct($o);
        if (isset($options['data'])) $this->setData($options['data']);
    }
    
    function setData(Ac_Legacy_Controller_Context $data) {
        $this->data = $data;
    }
    
    function hasValue(array $path) {
        $r = $this->data->getData($path, $this->tmp);
        $res = $path !== $this->tmp;
        return $res;
    }
    
    function getValue(array $path, $default = null, & $found = null) {
        $res = $this->data->getData($path, $this->tmp);
        if (!($found = !($res === $this->tmp))) { 
            $res = $default;
        }
        return $res;
    }
    
    function setValue(array $path, $value) {
        $tmp = $this->data->getData();
        Ac_Util::simpleSetArrayByPath($tmp, $path, $value);
        $this->data->setData($tmp);
    }
    
    function deleteValue(array $path) {
        $tmp = $this->data->getData();
        Ac_Util::unsetArrayByPath($tmp, $path);
        $this->data->setData($tmp);
    }
    
}