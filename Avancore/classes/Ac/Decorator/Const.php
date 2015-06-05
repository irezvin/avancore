<?php

class Ac_Decorator_Const extends Ac_Decorator {
    
    var $const = '';
    
    function apply($value) {
        return $this->const;
    }
    
}