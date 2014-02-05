<?php

class Ac_Cg_Php_Expression {
    
    var $expression = false;
    
    function Ac_Cg_Php_Expression($expression) {
        $this->expression = $expression;
    }
    
    function getExpression() {
        return $this->expression;
    }
    
}