<?php

class Ac_Decorator_ReplaceNull extends Ac_Decorator {

    var $string = '';
    
    function apply($value) {
        if (is_null($value)) $value = $this->string;
        return $value;
    }
    
}