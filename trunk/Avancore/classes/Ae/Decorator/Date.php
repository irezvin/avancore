<?php

class Ae_Decorator_Date extends Ae_Decorator {
    
    var $format = null;
    
    var $useGmt = false;
    
    function apply($value) {
        $value = Ae_Util::date($value, $this->format, $this->useGmt);
        return $value;
    }
    
}