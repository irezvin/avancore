<?php

interface Ac_I_Param_Condition {
    
    /**
     * Condition can be used to check any property (or maybe several properties) of Param object
     * (if it's match() method is supplied with Ac_I_Param instance)
     * 
     * @param string $propertyName
     */
    function setPropName($propertyName);
    
    /**
     * Returns propName
     */
    function getPropName();
    
    /**
     * Checks condition against value (and param instance)
     * 
     * @param $value
     * @param $errors
     * @param $param
     */
    function match($value, & $errors = array(), Ac_I_Param $param = null);
    
}