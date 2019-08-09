<?php

class Ac_Js_Object_Assign {

    protected $object = false;
    
    protected $property = false;
    
    protected $value = false;
    
    function __construct(Ac_Js_Object $object, $property, $value) {
        $this->object = $object;
        $this->property = $property;
        $this->value = $value;
    }
    
    /**
     * @return Ac_Js_Object
     */
    function getObject() {
        return $this->object;
    }
    
    function getProperty() {
        return $this->property;
    }
    
    function getValue() {
        return $this->value;
    }
    
    function toJs() {
        return $this->object->id.'.'.$this->property.' = '.new Ac_Js_Val($this->value);
    }
    
    function __toString() {
    	return $this->toJs();
    }

}