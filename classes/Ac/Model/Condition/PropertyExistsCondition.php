<?php

class Ac_Model_Condition_PropertyExistsCondition extends Ac_Model_Condition_PropertyCondition {

    var $mustNotExist = false;
    
    function test($value) {
        $this->getPropertyValue($value, $exists);
        if ($this->mustNotExist && $exists) return false;
        if ($this->conditions) return parent::test($exists);
        return $exists;
    }
    
}