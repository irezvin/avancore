<?php

class Ac_Debug {
    
    static $instanceStats = array();
    
    static $misc = array();
    
    protected static $estack = array();
    
    static function getIni(array $options) {
        $res = array();
        foreach ($options as $v) $res[$v] = ini_get($v);
        return $res;
    }
    
    static function setIni(array $optionsValues) {
        foreach ($optionsValues as $k => $v) {
            ini_set($k, $v);
        }
    }
    
    static function pushErrors($enable = false) {
        $ini = self::getIni(array('display_errors', 'error_reporting', 'html_errors'));
        array_push(self::$estack, $ini);
        if ($enable) self::enableAllErrors();
    }
    
    static function popErrors() {
        if (count(self::$estack)) {
            $ini = array_pop(self::$estack);
            self::setIni($ini);
            $res = true;
        } else {
            $res = false;
        }
        return $res;
    }
    
    static function savageMode($flush = false) {
        while(ob_get_level()) $flush? ob_end_flush() : ob_end_clean();
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT);
    }
    
    /**
     * Stands for "dump data and die"
     */
    static function ddd($_ = null) {
        self::savageMode();
        $a = func_get_args();
        ob_start();
        call_user_func_array('var_dump', $a); $l = __LINE__;
        $d = ob_get_clean();
        if (strpos($d, $str = __FILE__.':'.$l) !== false) {
            $s = debug_backtrace();
            $d = str_replace($str, $s[0]['file'].':'.$s[0]['line'], $d);
        }
        echo($d);
        die();
    }
    
    /**
     * Stands for "dump data"
     */
    static function dd($_ = null) {
        self::savageMode();
        $a = func_get_args();
        ob_start();
        call_user_func_array('var_dump', $a); $l = __LINE__;
        $d = ob_get_clean();
        if (strpos($d, $str = __FILE__.':'.$l) !== false) {
            $s = debug_backtrace();
            $d = str_replace($str, $s[0]['file'].':'.$s[0]['line'], $d);
        }
        echo ($d);
    }
    
    static function log($_ = null) {
        $a = func_get_args();
        return call_user_func_array(array('Ac_Debug_Log', 'l'), $a);
    }
    
    /**
     * Stands for "dump trace"
     */
    static function ddt($_ = null) {
        self::savageMode();
        $a = func_get_args();
        array_push($a, self::getTrace(false));
        if (count($a)) call_user_func_array('var_dump', $a);
    }
    
    static function enableAllErrors() {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('html_errors', PHP_SAPI != "cli");
    }
    
    /**
     * @return FirePHP
     */
    static function fb($_ = null) {
        $args = func_get_args();
        if (count($args)) {
            if (!headers_sent()) {
                $call = array(Ac_Debug_FirePHP::getInstance(), 'fb');
                return call_user_func_array($call, $args);
            }
        } else {
            return Ac_Debug_FirePHP::getInstance();
        }
    }
    
    static function clear() {
        self::$instanceStats = array();
        self::$misc = array();
    }
    
    /**
     * @param mixed $foo
     * Replaces Ac_Model_Objects with their values & class
     */
    static function dr($foo, $dump = false) {
        if (is_array($foo)) {
            $res = array(); 
            foreach ($foo as $k => $v) {
                $res[$k] = self::dr($v);
            }
        } elseif (is_object($foo) && $foo instanceof Ac_Model_Object) {
            $res = array_merge(array('__class' => get_class($foo)), $foo->getDataFields());
        } else {
            $res = $foo;
        }
        if ($dump) var_dump($res);
        return $res;
    }
    
    static function drr($foo) {
        foreach (func_get_args() as $arg) {
            self::dr($arg, true);
        }
    }
    
    static function reportConstruct($obj, $mask = true) {
        if (!is_array($mask) || in_array(get_class($this), $mask)) {
            $c = get_class($obj);
            if (!isset(self::$instanceStats[$c])) 
                self::$instanceStats[$c] = array('c' => 0, 'd' => 0);
            self::$instanceStats[$c]['c']++;
        }
    }
    
    static function reportDestruct($obj, $mask = true) {
        if (!is_array($mask) || in_array(get_class($this), $mask)) {
            $c = get_class($obj);
            if (!isset(self::$instanceStats[$c])) 
                self::$instanceStats[$c] = array('c' => 0, 'd' => 0);
            self::$instanceStats[$c]['d']++;
        }
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
    
        
    static function getTrace($asArray = false, $skip = true) {
        if ($skip === true) $skip = array('Ac_Debug');
        $bt = debug_backtrace();
        if ($skip) {
            $classes = Ac_Util::toArray($skip);
            $i = 0;
            while (isset($bt[$i]['class']) && in_array($bt[$i]['class'], $skip)) $i++;
            $bt = array_slice($bt, $i);
        }
        $c = count($bt);
        $s = array();
        foreach ($bt as $i => $arr) {
            $string = array();
            $string[] = sprintf("%4d.", $c - $i);
            if (isset($arr['function'])) {
                $fn = $arr['function'];
                if (isset($arr['class'])) {
                    $type = isset($arr['type'])? $arr['type'] : '::';
                    $fn = $arr['class'].$type.$fn;
                }
                $string[] = $fn;
            }
            if (isset($arr['file'])) $string[] = 'in '.$arr['file'];
            if (isset($arr['line'])) $string[] = '# '.$arr['line'];
            $s[] = $asArray? $string : implode(" ", $string);
        }
        $res = $asArray? $s : implode("\n", $s);
        return $res;
    }

    
}