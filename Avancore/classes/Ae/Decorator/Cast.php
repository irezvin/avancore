<?php

class Ae_Decorator_Cast extends Ae_Decorator {
    
    var $type = false;
    
    function apply($value) {
        if ($this->type !== false) settype($value, $this->type);
        return $value;
    }
    
}