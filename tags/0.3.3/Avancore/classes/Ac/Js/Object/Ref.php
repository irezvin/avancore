<?php

class Ac_Js_Object_Ref {
    
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
    
    function toJs() {
        return $this->object->id;
    }
    
    function __toString() {
    	return $this->toJs();
    }
    
    
}