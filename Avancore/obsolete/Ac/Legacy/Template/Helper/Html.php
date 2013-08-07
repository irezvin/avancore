<?php

class Ac_Legacy_Template_Helper_Html extends Ac_Legacy_Template_Helper {
    function jsQuote($string, $singleQuote = true) {
        $quote = $singleQuote? "'" : '"';
        $res = $quote.addcslashes($string, $quote."\n\r\t\0").$quote;
        return $res;
    }
    
    function toJson($value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = true) {
        return $this->_toJson($value, $indent, $indentStep, $newLines, $withNumericKeys);
    }
    
    function _arrayIsSimple ($arr) {
        foreach (array_keys($arr) as $k) if (!is_scalar($arr[$k])) return false;
        return true;   
    }
    
    function _onlyNumericKeys ($arr) {
        foreach (array_keys($arr) as $k) if (!is_numeric($k)) return false;
        return true;
    }
    
    function _toJson($value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = true) {
        if (!is_array($value)) {
            if (is_int($value) || is_float($value)) $res = (string) $value;
            elseif (is_object($value)) {
                if (method_exists($value, 'toJson')) {
                    $res = $value->toJson();
                } 
                elseif (method_exists($value, 'toString')) {
                    $res = $this->jsQuote($value->toString()); 
                }
                else {
                     trigger_error ('Class '.get_class($value).' doesn\'t have either toJson() or toString() methods', E_USER_ERROR);
                }
                if (!is_string($res)) $res = $this->_toJson($res, $indent + $indentStep, $indentStep);
            } else $res = $this->jsQuote($value);
        } else {
            if (!count($value)) $res = '{}'; else {
                $nk = !$withNumericKeys && $this->_onlyNumericKeys($value);
                $res = $nk? '[' : '{';
                $n = 0;
                $c = count($value);
                if ($newLines && !$this->_arrayIsSimple($value)) $nl = "\n".str_repeat(' ', $indent); 
                    else $nl = ' ';
                foreach (array_keys($value) as $k) {
                    $l = !$nk? ($nl . $this->jsQuote($k) . ': ') : $nl;
                    $res .= $l . $this->_toJson($value[$k], $indent + $indentStep, $indentStep, $newLines, $withNumericKeys);
                    if ($n++ < ($c - 1)) $res .=  ','; 
                }
                $res .= $nk? ']' : ' }';
            } 
        }
        return $res;
    }
    
}

?>