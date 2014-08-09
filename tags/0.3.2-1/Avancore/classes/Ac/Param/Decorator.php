<?php

/**
 * @author Nivzer
 */

/**
 * Applies Ac_I_Decorator to filtered value
 */
class Ac_Param_Decorator extends Ac_Param_Filter {
    
    /**
     * @var Ac_I_Decorator
     */
    protected $decorator = null;
    
    function setDecorator($decorator) {
        $this->decorator = $decorator;
    }

    function getDecorator() {
        return $this->decorator;
    }
    
    function filter($value, Ac_Param $param = null) {
        return Ac_Decorator::decorate($this->decorator, $value, $this->decorator);
    }
    
}