<?php

/**
 * @TODO Should we register SO on all lifecycle stages, as it is now, or only during __toString()? (and reset the mark on __clone?)
 */
class Ac_StringObject {

    static $issueNotices = true;
    
    protected static $strings = array();

    static function updateMark(Ac_I_StringObject $stringObject, $register = false) {
        $stringObject->setStringObjectMark($m = '##['.uniqid('', true).']##');
        if ($register) self::$strings[$m] = $stringObject;
        return $m;
    }
    
    static function register(Ac_I_StringObject $stringObject) {
        if (!strlen($m = $stringObject->getStringObjectMark()))
            $m = self::updateMark($stringObject);
        self::$strings[$m] = $stringObject;
    }
    
    static function registerMany(array $stringObjects) {
        $res = array();
        foreach ($stringObjects as $stringObject) {
            if (!strlen($m = $stringObject->getStringObjectMark()))
                $m = self::updateMark($stringObject);        
            if (!isset(self::$strings[$m])) self::$strings[$m] = $stringObject;
            $res[$m] = $stringObject;
        }
        return $res;
    }
    
    /**
     * Should be called from stringObject::__construct
     * @param Ac_I_StringObject $stringObject 
     */
    static function onConstruct(Ac_I_StringObject $stringObject) {
        self::register($stringObject);
    }
    
    /**
     * Should be called from stringObject::__wakeup
     * @param Ac_I_StringObject $stringObject 
     */
    static function onWakeup(Ac_I_StringObject $stringObject) {
        self::register($stringObject);
    }
    
    /**
     * Should be called from stringObject::__clone
     * @param Ac_I_StringObject $stringObject 
     */
    static function onClone(Ac_I_StringObject $stringObject) {
        self::updateMark($stringObject, true);
    }
    
    static function getObjects($string) {
        $rx = preg_match_all('/##\[[0-9a-z.]+\]##/', $string, $matches);
        $res = array_intersect_key(self::$strings, $fl = array_flip($matches[0]));
        if (self::$issueNotices) {
            $notFound = array_diff_key($fl, self::$strings);
            if ($notFound) trigger_error("Unknown marks in buffer '{$string}': ".implode(", ", array_keys($notFound)));
        }
        return $res;
    }
    
    static function replaceObjects($string, $method, $checkMethod = false, $_ = null) {
        $objects = self::getObjects($string);
        $vals = array();
        $args = func_get_args();
        array_splice($args, 0, 3);
        foreach ($objects as $mark => $object) {
            if (!$checkMethod || method_exists($object, $method))
                $vals[$mark] = call_user_func_array(array($object, $method), $args);
        }
        $res = strtr($string, $vals);
        return $res;
    }
    
    static function registerContainerStrings(Ac_I_StringObjectContainer $container) {
        foreach ($container->getStringBuffers() as $string)
            $container->registerStringObjects ($this->getObjects ($string));
    }
    
}