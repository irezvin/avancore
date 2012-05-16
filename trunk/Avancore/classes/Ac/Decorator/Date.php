<?php

class Ac_Decorator_Date extends Ac_Decorator {
    
    var $format = null;
    
    var $useGmt = false;
    
    function apply($value) {
        $value = Ac_Util::date($value, $this->format, $this->useGmt);
        return $value;
    }
    
}