<?php

/**
 * @author Nivzer
 */

/**
 * Applies Ae_I_Decorator to filtered value
 */
class Ae_Param_Decorator extends Ae_Param_Filter {
    
    /**
     * @var Ae_I_Decorator
     */
    protected $decorator = null;
    
    function setDecorator($decorator) {
        $this->decorator = $decorator;
    }

    function getDecorator() {
        return $this->decorator;
    }
    
    function filter($value, Ae_Param $param = null) {
        return Ae_Decorator::decorate($this->decorator, $value, $this->decorator);
    }
    
}