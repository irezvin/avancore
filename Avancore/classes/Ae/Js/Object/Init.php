<?php

class Ae_Js_Object_Init {

    /**
     * @var Ae_Js_Object
     */
    protected $object = false;
    
    function __construct(Ae_Js_Object $object) {
        $this->object = $object;
    }
    
    /**
     * @return Ae_Js_Object
     */
    function getObject() {
        return $this->object;
    }
    
    function toJs() {
        if ($this->object->args) {
            if (!isset($this->object->args[0])) $args = array($this->object->args);
            else $args = $this->object->args;
        } else {
            $args = array();
        }
        if ($this->object->constructor) {
            $res = $this->object->id.' = '.(new Ae_Js_Call($this->object->constructor, $args, true)).';';
        } else {
            $a = $args? new Ae_Js_Val($this->args) : new Ae_Js_Var('{}');
            $res = $this->object->id.' = '.$a.';';
        }
        return $res;
    }
    
    function __toString() {
    	return $this->toJs();
    }
    
}