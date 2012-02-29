<?php

class Ae_Js_Object_Assign {

    protected $object = false;
    
    protected $property = false;
    
    protected $value = false;
    
    function __construct(Ae_Js_Object $object, $property, $value) {
        $this->object = $object;
        $this->property = $property;
        $this->value = $value;
    }
    
    /**
     * @return Ae_Js_Object
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
        return $this->object->id.'.'.$this->property.' = '.new Ae_Js_Val($this->value);
    }
    
    function __toString() {
    	return $this->toJs();
    }

}