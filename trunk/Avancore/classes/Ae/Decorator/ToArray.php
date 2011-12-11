<?php

class Ae_Decorator_ToArray extends Ae_Decorator {
    
    /**
     * If $strict is true, then NULL, FALSE and empty string *will* be converted to array with one element too... 
     * @var bool
     */
    var $strict = false;

    function apply($value) {
        if (!is_array($value)) {
            if ($this->strict) $value = array($value);
            else {
                if ($value !== null && $value !== false && $value !== '') $value = array($value);
                    else $value = array();
            }
        }
        return $value;
    }
    
}