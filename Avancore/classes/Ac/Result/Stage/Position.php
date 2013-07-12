<?php

class Ac_Result_Stage_Position {

    /**
     * @var Ac_Result
     */
    protected $result = false;
    
    protected $propertyName = false;
    
    protected $offset = false;
    
    protected $isString = null;

    protected $currentObject = null;
    
    protected $classes = false;
    
    function __construct(Ac_Result $result, $classes = false) {
        $this->result = $result;
        $this->classes = $classes;
    }
    
    function gotoPosition($propertyName, $offset, $isString) {
        
    }
    
    function hasObject() {
        
    }
    
    function advance() {
        
    }
    
    function getObjectAtPosition() {
        
    }
    
    function locateCurrentObject() {
        
    }
    
    function getPropertyName() {
        
    }
    
    // Modifier methods
    
    function insertBefore($insertedObject) {
        
    }
    
    function insertAfter($insertedObject) {
        
    }
    
    function removeCurrentObject() {
        
    }
    
    function replaceCurrentObject($withobject) {
        
    }
    
}