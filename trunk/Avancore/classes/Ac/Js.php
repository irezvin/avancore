<?php

class Ac_Js {

    function jsQuote($string, $singleQuote = true) {
        if (is_bool($string)) return $string? 'true': 'false';
        if (is_numeric($string)) return ''.$string;
        $quote = $singleQuote? "'" : '"';
        $res = $quote.addcslashes($string, $quote."\n\r\t\0\\").$quote;
        return $res;
    }

    static protected function iJsQuote($string) {
        if (is_bool($string)) return $string? 'true': 'false';
        if (is_int($string) || is_float($string)) return (string) $string;
        return "'".addcslashes($string, "'\n\r\t\0\\")."'";
    }
    
    static protected function isArraySimple (& $arr) {
        foreach (array_keys($arr) as $k) if (!is_scalar($arr[$k])) return false;
        return true;   
    }
    
    static protected function areOnlyNumericKeys (& $arr) {
        foreach (array_keys($arr) as $k) if (!is_numeric($k)) return false;
        return true;
    }
    
    function toJs($value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = false) {
        return Ac_Js::lrToJs($this, $value, $indent, $indentStep, $newLines, $withNumericKeys);
    }
    
    static protected function lrToJs(Ac_Js $js, $value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = false) {
        if (!is_array($value)) {
            if (is_int($value) || is_float($value)) return (string) $value;
            elseif (is_null($value)) return 'null';
            elseif (is_bool($value)) return $value? 'true' : 'false';
            elseif (is_object($value)) {
                if ($value instanceof DateTime) {
                	return "'".@$value->format('Y-m-d H:i:s')."'";
                } elseif ($value instanceof Ac_I_Jsable) {
                    $v = $value->toJs($js, $indent, $indentStep, $newLines);
                    return is_string($v)? $v : Ac_Js::lrToJs($js, $value, $indent + $indentStep, $indentStep, $newLines, $withNumericKeys); 
                } elseif (method_exists($value, 'toJson')) {
                    $json = $value->toJson();
                    if (!is_string($json)) return Ac_Js::lrToJs($js, $json, $indent + $indentStep, $indentStep,$newLines, $withNumericKeys);
                        return $json;
                } elseif (method_exists($value, 'toJs')) {
                    $json = $value->toJs();
                    if (!is_string($json)) return Ac_Js::lrToJs($js, $json, $indent + $indentStep, $indentStep,$newLines, $withNumericKeys);
                        else return $json;
                } 
                elseif (method_exists($value, 'toString')) {
                    return Ac_Js::iJsQuote($value->toString()); 
                } 
                elseif (method_exists($value, '__toString')) {
                    return Ac_Js::iJsQuote($value->__toString());
                }
                else {
                     trigger_error ('Class '.get_class($value).' doesn\'t have either toJs(), __toString() or toString() methods', E_USER_ERROR);
                }
                if (!is_string($res)) $res = Ac_Js::lrToJs($js, $res, $indent + $indentStep, $indentStep, $newLines, $withNumericKeys);
            } else return Ac_Js::iJsQuote($value);
        } else {
            if (!count($value)) return '[]'; else {
                $nk = !$withNumericKeys && Ac_Js::areOnlyNumericKeys($value);
                if ($withNumericKeys == 1) $withNumericKeys = false;
                $res = $nk? '[' : '{';
                $n = 0;
                $c = count($value);
                if ($newLines && !Ac_Js::isArraySimple($value)) $nl = "\n".str_repeat(' ', $indent); 
                    else $nl = ' ';
                foreach (array_keys($value) as $k) {
                    $l = !$nk? ($nl . Ac_Js::iJsQuote($k) . ': ') : $nl;
                    $res .= $l . Ac_Js::lrToJs($js, $value[$k], $indent + $indentStep, $indentStep, $newLines, $withNumericKeys);
                    if ($n++ < ($c - 1)) $res .=  ','; 
                }
                $res .= $nk? ']' : ' }';
                return $res;
            } 
        }
        throw new Exception ("Assertion: function should end before this place");
    }
    
    function fixIndent($text, $indent = 0) {
        $indText = str_repeat(' ', $indent);
        $text = $indText.str_replace("\n", "\n".$indText, $text);
        return $text;
    }
    
}

?>