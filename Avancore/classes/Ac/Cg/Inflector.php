<?php

class Ac_Cg_Inflector {
    
    /**
     * Explodes an identifier to its parts using spaces, camelCase and underscores.
     * For example, if 'foo_bar bazQuux' is given, result will be array('foo', 'bar', 'baz', 'quux')
     *
     * @param string $string
     */
    static function explode($string) {
        if (!is_string($string)) {
            if (is_array($string)) $res = Ac_Util::array_values($string);
            else $res = array();
        } else {
            $string = str_replace('_', ' ', $string);
            $string = preg_replace ('/([A-Z])/', ' \1', $string);
            $string = trim($string);
            $string = preg_replace ('/\s+/', ' ', $string);
            $string = strtolower($string);
            $res = explode(' ', $string);
        }
        return $res;
    }
    
    /**
     * Tries to convert to plural form to singular one.
     *
     * @param string|array $stringOrArray Identifier or explode'd identifier (if array is given, only <em>last</em> element is converted)
     * @param bool $convertAll convert all parts of string/array
     * @return string|array
     */
    static function pluralToSingular($stringOrArray, $convertAll = false) {
        return Ac_Cg_Inflector::_convert($stringOrArray, $convertAll, true);
    }
    
    /**
     * Tries to convert to singular form from plural one.
     *
     * @param string|array $stringOrArray Identifier or explode'd identifier (if array is given, only <em>last</em> element is converted)
     * @param bool $convertAll convert all parts of string/array
     * @return string|array
     */
    static function singularToPlural($stringOrArray, $convertAll = false) {
        return Ac_Cg_Inflector::_convert($stringOrArray, $convertAll, false);
    }
    
    static function _convert($stringOrArray, $convertAll, $toSingular) {
        if ($convertAll) {
            $res = array();
            foreach (Ac_Cg_Inflector::explode(" ", $string) as $part) $res[] = Ac_Cg_Inflector::_convert($part, false, $toSingular);
        } else {
            if (is_array($stringOrArray)) {
                $res = $stringOrArray;
                if (count($stringOrArray)) {
                    $keys = array_keys($stringOrArray);
                    $lastKey = $keys[count($keys) - 1];
                    $res[$lastKey] = Ac_Cg_Inflector::_convert($res[$lastKey], false, $toSingular);
                }
            } else {
                if ($toSingular) {
                    $res = Ac_Cg_Inflector::_toSingular($stringOrArray);
                } else {
                    $res = Ac_Cg_Inflector::_toPlural($stringOrArray);
                }
            }
        }
        return $res;
    }
    
    static function _toSingular($string) {
        if (substr($string, -3) == 'ies') {
            $res = substr($string, 0, -3).'y';
        } elseif (substr($string, -3) == 'ses') {
            $res = substr($string, 0, -2);
        } elseif ((substr($string, -1) == 's') && (substr($string, -2) != 'ss')) {
            $res = substr($string, 0, -1);
        } else {
            $res = $string;
        }
        return $res;
    }
    
    static function _toPlural($string) {
        if (substr($string, -1) == 'y') {
            $res = substr($string, 0, -1).'ies';
        } elseif (substr($string, 0, -2) == 'ss') {
            $res = $string."es";
        } elseif (substr($string, 0, -1) != 's') {
            $res = $string."s";
        } else {
            $string = $res;
        }
        return $res;
    }
    
    /**
     * Converts string into 'Foo_Bar_Baz_Quux' form
     *
     * @param string|array $identifier
     * @return string
     */
    static function pearize($identifier) {
        $parts = array();  
        foreach (Ac_Cg_Inflector::explode($identifier) as $part) $parts[] = ucfirst($part);
        return implode("_", $parts);
    }
    
    /**
     * Converts string into 'fooBarBazQuux' form (or 'FooBarBazQuux' form if $ucFirst is true)
     *
     * @param string|array $identifier
     * @param bool $ucFirst Make first letter of the result identifier uppercase
     * @return string
     */
    static function camelize($identifier, $ucFirst = false) {
        $parts = array();  
        foreach (Ac_Cg_Inflector::explode($identifier) as $part) $parts[] = ucfirst($part);
        $res = implode("", $parts);
        if (!$ucFirst && strlen($res)) $res{0} = strtolower($res{0});
        return $res;
    }
    
    /**
     * Converts string into 'Foo bar baz quuxx'
     *
     * @param string|array $identifier 
     * @param bool $ucFirst Whether convert first letter to upper case
     * @param unknown_type $ucOther Whether convert other parts to upper case
     */
    static function humanize($identifier, $ucFirst = true, $ucOther = false) {
        $parts = Ac_Cg_Inflector::explode($identifier);
        if ($ucFirst) $start = 0; else $start = 1;
        $c = $ucOther? count($parts) : min(count($parts), 1);
        for ($i = $start; $i < $c; $i++) $parts[$i] = ucfirst($parts[$i]);
        $res = implode(" ", $parts);
        return $res;
    }
    
    /**
     * Converts 'fooBarBazQuux' into 'FOO_BAR_BAZ_QUUX' (useful for define() statements) 
     *
     * @param string|array $identifier
     */
    static function definize($identifier) {
        return strtoupper(implode("_", Ac_Cg_Inflector::explode($identifier)));
    }
    
}

