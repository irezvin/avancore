<?php

class Ac_Model_Condition_RegexpCondition extends Ac_Model_Condition_AbstractCondition {

    var $regexp = '/.*/u';
    
    function test($value) {
        return (bool) preg_match($this->regexp, $value);
    }
    
}