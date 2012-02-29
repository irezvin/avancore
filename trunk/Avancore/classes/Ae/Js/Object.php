<?php

class Ae_Js_Object {
    
    var $id = false;
    
    var $constructor = false;
    
    var $args = array();
    
    protected $ref = false;
    
    protected $init = false;
    
    function __construct($id, $constructor = null, $args = array()) {
        if (func_num_args() == 1 && is_array($id) && isset($id['_id'])) {
            $this->id = $id['_id'];
            if (isset($id['_constructor'])) $this->constructor = $id['_constructor'];
            unset($id['_id']);
            unset($id['_constructor']);
            $this->args = $id;
        } else {
            $this->id = $id;
            $this->constructor = $constructor;
            $this->args = $args;
        }
    }
    
    /**
     * @return Ae_Js_Object_Ref
     */
    function ref() {
        if (!$this->ref) $this->ref = new Ae_Js_Object_Ref($this);
        return $this->ref;
    }
    
    /**
     * @return Ae_Js_Object_Call
     */
    function call($func, $_ = null) {
        $args = func_get_args();
        if (!is_array($func)) $func = array($func);
        if (!is_array($this->id)) $id = array($this->id);
            else $id = $this->id;
        $func = array_merge($id, $func);
        $res = new Ae_Js_Call($func,  array_slice($args, 1));
        return $res;
    }
    
    /**
     * @return Ae_Js_Object_Init
     */
    function init() {
        if (!$this->init) $this->init = new Ae_Js_Object_Init($this);
        return $this->init;
    }
    
    function assign($property, $value) {
        return new Ae_Js_Object_Assign($this, $property, $value);
    }
    
}