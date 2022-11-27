<?php

class Ac_Model_Condition_EmptyCondition extends Ac_Model_Condition_AbstractCondition {

    function test($value) {
        if (is_array($value)) return !count($value);
        return is_null($value) || !strlen($value);
    }
    
}