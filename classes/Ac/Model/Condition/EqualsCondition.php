<?php

class Ac_Model_Condition_EqualsCondition extends Ac_Model_Condition_AbstractCondition {

    var $value = null;
    
    var $strict = false;
    
    function test($value) {
        if (is_array($value)) {
            foreach ($value as $item) if ($this->test($item)) return true;
            return false;
        }
        if (is_array($this->value)) {
            return (in_array($value, $this->value, $this->strict));
        }
        return $this->strict? $this->value === $value : $this->value == $value;
    }
    
}