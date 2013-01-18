<?php

class Ac_Debug {
    
    static $created = array();
    
    static $deleted = array();
    
    static $misc = array();
    
    static function savageMode($flush = false) {
        while(ob_get_level()) $flush? ob_end_flush() : ob_end_clean();
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT);
    }
    
    static function enableAllErrors() {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('html_errors', 1);
    }
    
    static function fb($_) {
        if (!headers_sent()) {
            $args = func_get_args();
            $call = array(Ac_Debug_FirePHP::getInstance(), 'fb');
            return call_user_func_array($call, $args);
        }
    }
    
    static function clear() {
        self::$created = array();
        self::$deleted = array();
        self::$misc = array();
    }
    
    static function reportConstruct($obj) {
        $c = get_class($obj);
        if (!isset(self::$created[$c])) self::$created[$c] = 1;
            else self::$created[$c]++;
    }
    
    static function reportDestruct($obj) {
        $c = get_class($obj);
        if (!isset(self::$deleted[$c])) self::$deleted[$c] = 1;
            else self::$deleted[$c]++;
    }
    
    static function getInstanceCounters($class = false) {
        if ($class === false) 
            $class = array_unique(
                array_merge(
                    array_keys(self::$created), 
                    array_keys(self::$deleted)
                )
            );
        elseif (!is_array($class)) $class = array($class);
        $res = array();
        foreach ($class as $c) {
            $res[$c] = array(
                'created' => isset(self::$created[$c])? self::$created[$c] : 0,
                'deleted' => isset(self::$deleted[$c])? self::$deleted[$c] : 0,
            );
            $res[$c]['existing'] = $res[$c]['created'] - $res[$c]['deleted'];
        }
        return $res;
    }
    
}