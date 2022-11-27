<?php

class Ac_Model_Condition_Helper_GetObjectProperty implements Ac_Model_Condition_Helper_ModelAccessorInterface {
    
    function getPropertyValue($model, $property, & $found = null) {
        static $tmp;
        if (!$tmp) $tmp = new stdClass();
        $res = Ac_Util::getObjectProperty($model, $property, $tmp);
        $found = true;
        if ($res === $tmp) {
            $found = false;
            $res = null;
        }
        return $res;
    }
    
}