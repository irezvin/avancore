<?php

class Ac_E_InvalidCall extends Exception {

    static function toString($value) {
        if (is_object($value)) {
            $res = '['.get_class($value).']';
            if (method_exists($value, '__toString')) $res = $value.$res;
        } else {
            $res = ''.$value.'['.gettype($value).']';
        }
        return $res;
    }
    
    static function canRunMethodOnce($issuer, $methodName) {
        return new Ac_E_InvalidCall(get_class($issuer).": can {$methodName}() only once");
    }
    
    static function outOfSet($paramName, $value, array $allowedValues) {
        $value = self::toString($value);
        return new Ac_E_InvalidCall("Invalid \${$paramName} value '{$value}'; allowed values are '".implode(', ', $allowedValues)."'");
    }
    
    static function wrongType($paramName, $value, $allowedTypes) {
        return new Ac_E_InvalidCall("Invalid \${$paramName} type: '".gettype($value)."'; allowed types are '".implode(', ', Ac_Util::toArray($allowedTypes)."'"));
    }
    
    static function noSuchItem($title, $key, $checkFn = false) {
        $msg = "No such $title: '{$key}'";
        if ($checkFn) $msg .= "; check with {$checkFn}() first";
        return new Ac_E_InvalidCall($msg);
    }
    
    static function noSuchProperty($issuer, $propName, array $allowedValues = array()) {
        $issuer = self::toString($issuer);
        if ($allowedValues) $suff = "; allowed properties are '".implode(', ', $allowedValues)."'"; 
            else $suff = "";
        return new Ac_E_InvalidCall("No such property: '$issuer'->\${$propName}'{$suff}");
    }
    
}