<?php

/**
 * 
 */

/**
 * This class is a collection of static methods due to the global nature of String Objects 
 * (some of them should be called by string objects' or containers' methods, some other are useful utilites).
 * 
 * @TODO Should we register SO on all lifecycle stages, as it is now, or only during __toString()? (and reset the mark on __clone?)
 */
abstract class Ac_StringObject {

    static $issueNotices = true;
    
    protected static $pregExpr = '##\[[0-9a-z.]+\]##';
    
    protected static $strings = array();

    static function getPregExpr() {
        return self::$pregExpr;
    }
    
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
    
    /**
     * Returns $objects array where each Ac_I_StringOBjectWrapper() is replaced with its getHeloObject()
     * 
     * @param array $objects
     */
    static function unwrap(array $objects) {
        $res = array();
        foreach ($objects as $k => $v) {
            if (is_object($v) && $v instanceof Ac_I_StringObjectWrapper) $v = $v->getHeldObject();
            $res[$k] = $v;
        }
        return $res;
    }
    
    /**
     * @param string $mark
     * @return Ac_I_StringObject
     */
    static function getObjectByMark($mark) {
        $res = null;
        if (isset(self::$strings[$mark])) $res = self::$strings[$mark];
        return $res;
    }

    static function getObjects($string, $dontUnwrap = false) {
        $rx = preg_match_all('/'.self::$pregExpr.'/', $string, $matches);
        $res = array_intersect_key(self::$strings, $fl = array_flip($matches[0]));
        if (!$dontUnwrap) $res = self::unwrap($res);
        if (self::$issueNotices) {
            $notFound = array_diff_key($fl, self::$strings);
            if ($notFound) trigger_error("Unknown marks in buffer '{$string}': ".implode(", ", array_keys($notFound)));
        }
        return $res;
    }
    
    /**
     * Recursively extracts string objects from every item in $arr
     * Returns array of same structure
     *  
     * @param array $arr
     * @param type $dontUnwrap
     * @return array
     */
    static function getObjectsArr(array $arr, $dontUnwrap = false) {
        foreach ($arr as $k => $v) {
            $res[$k] = is_array($v)? self::getObjectsArr($v, $dontUnwrap) : self::getObjects($v, $dontUnwrap);
        }
        return $res;
    }
    
    static function sliceStringWithObjects($string, $dontUnwrap = false) {
        if ($objects = self::getObjects($string, $dontUnwrap)) {
            $items = preg_split('/('.self::$pregExpr.')/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($items as $key => & $item) {
                if (!strncmp($item, '##', 2) && isset($objects[$item])) 
                    $items[$key] = $objects[$item];
            }
            $res = $items;
        } else {
            $res = array($string);
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
    
    static function onContainerSleep(Ac_I_StringObjectContainer $container) {
        self::registerContainerStrings($container);
    }
    
    static function registerContainerStrings(Ac_I_StringObjectContainer $container) {
        foreach ($container->getStringBuffers() as $string)
            $container->registerStringObjects (self::getObjects ($string, true));
    }
        
    /**
     * Locates previous, current, and next string object marks and their offset relative to position $position.
     * 'current' string object mark means mark that starts before or at $position and ends after or at $position.
     * If $position is not inside any mark, 'current' key in result will contain FALSE value.
     * If $position is between marks, 'current' will be FALSE, while 'prev' and 'next' will be filled-in
     * 
     * @param type $string
     * @param int $position
     * @param bool $useCache Whether to cache results of preg_split for future calls
     * @retrun array('prev' => FALSE|array($mark, $offset), 'current'  => FALSE|array($mark, $offset), 'next' => FALSE|array($mark, $offset))
     */
    static function getStringObjectContext($string, $position, $useCache = false) {
        $items = null;
        if ($useCache) {
            static $cache = array();
            $hash = md5($string);
            if (isset($cache[$hash])) $items = $cache[$hash];
        }
        if (is_null($items)) {
            $items = preg_split('/('.self::$pregExpr.')/', $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
            if ($useCache) $cache[$hash] = $items;
        }
        $prev = false;
        $current = false;
        $next = false;
        foreach ($items as $i => $item) {
            if ($i % 2) { // it is a mark
                list($mark, $offset) = $item;
                $last = $offset + strlen($mark) - 1;
                if ($position >= $offset && $position <= $last) $current = $item;
                elseif ($offset < $position) $prev = $item;
                elseif ($last > $position) {
                    $next = $item; // we have found next item - stop
                    break;
                }
            }
        }
        return compact('prev', 'current', 'next');
    }

}