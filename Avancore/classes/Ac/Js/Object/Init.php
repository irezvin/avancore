<?php

class Ac_Js_Object_Init {

    /**
     * @var Ac_Js_Object
     */
    protected $object = false;
    
    function __construct(Ac_Js_Object $object) {
        $this->object = $object;
    }
    
    /**
     * @return Ac_Js_Object
     */
    function getObject() {
        return $this->object;
    }
    
    function getArgs() {
        if ($this->object->args) {
            if (!isset($this->object->args[0])) $args = array($this->object->args);
            else $args = $this->object->args;
        } else {
            $args = array();
        }
        return $args;
    }
    
    function toJs() {
        $args = $this->getArgs();
        if ($this->object->constructor) {
            $res = $this->object->id.' = '.$this->getRvalue().';';
        } else {
            $res = $this->object->id.' = '.$this->getRvalue();
        }
        return $res;
    }
    
    function getRvalue() {
        $args = $this->getArgs();
        if (strlen($this->object->constructor)) {
            $res = new Ac_Js_Call($this->object->constructor, $args, true);
        } else {
            $res = $args? new Ac_Js_Val($this->args) : new Ac_Js_Var('{}');
        }
        return $res;
    }
    
    function __toString() {
    	return $this->toJs();
    }
    
}