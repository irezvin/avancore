<?php

class Ae_Js_Var {
    
    var $var = 'undefined';
    
    function __construct($var = 'undefined') { $this->var = $var; }
    
    function toJs() {
        return $this->var;
    }
    
    function __toString() {
    	return $this->toJs();
    }
    
}

?>