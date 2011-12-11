<?php

class Ae_Param_Filter_Numeric extends Ae_Param_Filter {
    
    var $allowFloat = false;
    
    var $allowZero = true;
    
    var $allowNegative = true;
    
    function filter($value, Ae_Param $param = null) {
        if (is_numeric($value)) {
            $value = $allowFloat? floatval($value) : intval($value);
            if (!$allowNegative && $value < 0) $value = null;
            elseif (!$allowZero && !$value) $value = null;
        } else $value = null;
        return $value;
    }
    
}