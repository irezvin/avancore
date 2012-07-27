<?php

class Cg_Php_Expression {
    
    var $expression = false;
    
    function Cg_Php_Expression($expression) {
        $this->expression = $expression;
    }
    
    function getExpression() {
        return $this->expression;
    }
    
}