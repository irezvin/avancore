<?php

class Ac_Cg_Php_Expression extends Ac_Cg_Base {
    
    var $expression = false;
    
    function __construct ($expression = '') {
        $this->expression = $expression;
    }
    
    function getExpression() {
        return $this->expression;
    }
    
}