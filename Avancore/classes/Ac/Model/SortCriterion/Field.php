<?php

class Ac_Model_SortCriterion_Field extends Ac_Prototyped implements Ac_I_Search_Criterion_Sort {
    
    var $field = false;
    
    var $reverse = false;
    
    function hasPublicVars() {
        return true;
    }
    
    function __invoke($record1, $record2) {
        $field = $this->field;
        $a = $record1->getField($field);
        $b = $record2->getField($field);
        if ($a < $b) $res = -1;
        elseif ($a > $b) $res = 1;
        else $res = 0;
        if ($this->reverse) $res = -$res;
        return $res;
    }
    
}