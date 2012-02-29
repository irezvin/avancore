<?php

class Ae_Js_Object_Ref {
    
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
        return $this->object->id;
    }
    
    function __toString() {
    	return $this->toJs();
    }
    
    
}