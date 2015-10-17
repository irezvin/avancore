<?php

class Ac_Model_SortCriterion_Fields extends Ac_Prototyped implements Ac_I_Search_Criterion_Sort {
    
    protected $fields = array();

    function setFields($fields) {
        $fields = Ac_Util::toArray($fields);
        $this->fields = array();
        foreach ($fields as $k => $v) {
            if (is_numeric($k)) {
                $k = $v;
                $v = true;
            }
            $this->fields[$k] = $v? 1 : -1;
        }
    }

    function getFields() {
        return $this->fields;
    }
    
    function hasPublicVars() {
        return true;
    }
    
    function __invoke($record1, $record2) {
        $res = 0;
        foreach ($this->fields as $field => $sign) {
            $a = $record1->getField($field);
            $b = $record2->getField($field);
            if ($a != $b) {
                if ($a < $b) $res = -$sign;
                else $res = $sign;
                break;
            }
        }
        return $res;
    }
    
}