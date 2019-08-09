<?php

class Ac_Decorator_Cast extends Ac_Decorator {
    
    var $type = false;
    
    function apply($value) {
        if ($this->type !== false) settype($value, $this->type);
        return $value;
    }
    
}