<?php

class Ac_Decorator_Implode extends Ac_Decorator {
    
    var $glue = ", ";
    
    function apply($value) {
        if (is_array($value)) $value = Ac_Util::implode_r($this->glue, $value);
        return $value;
    }
    
}