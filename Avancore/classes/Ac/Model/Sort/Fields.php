<?php

class Ac_Model_Sort_Fields extends Ac_Prototyped implements Ac_I_Search_Criterion_Order {
    
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
            $a = $record1->$field;
            $b = $record2->$field;
            if ($a != $b) {
                if ($a < $b) $res = -$sign;
                else $res = $sign;
                break;
            }
        }
        return $res;
    }
    
}