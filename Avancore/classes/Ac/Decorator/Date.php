<?php

class Ac_Decorator_Date extends Ac_Decorator {
    
    var $format = null;
    
    var $useGmt = false;
    
    var $skipEmptyValue = true;
    
    function apply($value) {
        if (strlen($value) || !$this->skipEmptyValue) {
            $value = Ac_Util::date($value, $this->format, $this->useGmt);
        }
        return $value;
    }
    
}