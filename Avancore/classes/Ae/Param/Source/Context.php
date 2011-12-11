<?php

class Ae_Param_Source_Context extends Ae_Autoparams implements Ae_I_Param_Destination {
    
    /**
	 * @var Ae_Controller_Context 
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
    
    function setData(Ae_Controller_Context $data) {
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
        Ae_Util::simpleSetArrayByPath($tmp, $path, $value);
        $this->data->setData($tmp);
    }
    
    function deleteValue(array $path) {
        $tmp = $this->data->getData();
        Ae_Util::unsetArrayByPath($tmp, $path);
        $this->data->setData($tmp);
    }
    
}