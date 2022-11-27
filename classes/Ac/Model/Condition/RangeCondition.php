<?php

class Ac_Model_Condition_RangeCondition extends Ac_Model_Condition_AbstractCondition {

    var $min = false;
    
    var $max = false;
    
    function test($value) {
        if ($this->min === false && $this->max === false) return true;
        if ($this->min !== false && $value < $this->min) return false;
        if ($this->max !== false && $value > $this->max) return false;
        return true;
    }
    
}