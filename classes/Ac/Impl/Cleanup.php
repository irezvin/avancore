<?php

class Ac_Impl_Cleanup {
    
    protected static $lock = array();
    protected static $level = 0;
    
    static $debug = 0;
    
    static function clean(Ac_I_WithCleanup $obj, array $allowedClasses = array()) {
        
        self::$level++;
        if ($allowedClasses) {
            $found = false;
            foreach ($allowedClasses as $c) if ($obj instanceof $c) {
                $found = true;
                break;
            }
        } else $found = true;
        if ($found) {
            $h = spl_object_hash($obj);
            if (!isset(self::$lock[$h])) {
                if (self::$debug) Ac_Debug::$misc['Ac_Impl_Cleanup'][] = get_class($obj);
                self::$lock[$h] = true;
                $obj->invokeCleanup();
                if ($refs = $obj->getCleanupArrayRefs()) 
                    foreach ($refs as $k => & $arr) {
                        if (!is_numeric($k)) $ac = array_merge(explode(',', $k), $allowedClasses);
                        else $ac = $allowedClasses; 
                        self::cleanArray($arr, $allowedClasses);
                    }
            }
        }
        self::$level--;
        if (!self::$level) {
            if (self::$debug) Ac_Debug::$misc['Ac_Impl_Cleanup'][] = self::$lock;
            self::$lock = array();
        }
        
    }
    
    static function cleanArray(array & $arr, array $allowedClasses = array()) {
        
        self::$level++;
        
        foreach ($arr as $k => & $v) {
            if (is_object($v) && $v instanceof Ac_I_WithCleanup) self::clean($v, $allowedClasses);
            elseif (is_array($v)) self::cleanArray($v, $allowedClasses);
        }
        $arr = array();
        
        self::$level--;
        if (!self::$level) {
            if (self::$debug) Ac_Debug::$misc['Ac_Impl_Cleanup'][] = self::$lock;
            self::$lock = array();
        }
    }
    
}