<?php

class Ac_Param_Filter_Numeric extends Ac_Param_Filter {
    
    var $allowFloat = false;
    
    var $allowZero = true;
    
    var $allowNegative = true;
    
    function filter($value, Ac_Param $param = null) {
        if (is_numeric($value)) {
            $value = $this->allowFloat? floatval($value) : intval($value);
            if (!$this->allowNegative && $value < 0) $value = null;
            elseif (!$this->allowZero && !$value) $value = null;
        } else $value = null;
        return $value;
    }
    
}