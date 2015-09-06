<?php

class Ac_Model_Sort_Field extends Ac_Prototyped implements Ac_I_Search_Criterion_Order {
    
    var $field = false;
    
    function hasPublicVars() {
        return true;
    }
    
    function __invoke($record1, $record2) {
        $a = $record1->$field;
        $b = $record2->$field;
        if ($a < $b) $res = -1;
        elseif ($a > $b) $res = 1;
        else $res = 0;
        return $res;
    }
    
}