<?php

/**
 * Version of Ac_Accessor_Path that always contains reference to the $src
 * and allow to call different methods (not only get<Foo>ByPath)
 * 
 * @TODO decide what to do with $default 
 *       since it becomes obsolete if original doGetValue is replaced by the method from methodMap
 */
class Ac_Accessor_PathRef extends Ac_Accessor_Path {
    
    protected $cacheableMethods = array();
    
    protected $methodsCache = array();
    
    /**
     * @var array myMethod => srcMethod
     */
    protected $methodMap = array();
    
    protected $passPathAsString = false;
    
    protected $children = false;
    
    function __construct($src, $path, $default = null, $cache = false, array $options = array()) {
        parent::__construct($src, $path, $default, $cache);
        if ($options) $this->setOptions($options);
    }

    /**
     * It is allowed to use $src method instead of Ac_Accessor::getObjectPropertyByPath()
     * by providing metodMap['value']
     * 
     * @param array $methodMap myMethod => srcMethod
     */
    function setMethodMap(array $methodMap) {
        $this->setOptions(array('methodMap' => $methodMap));
    }

    function getMethodMap() {
        return $this->methodMap;
    }

    /**
     * Whether path array should be converted using Ac_Util::arrayToPath before passing it to $src
     * @param bool $passPathAsString 
     */
    function setPassPathAsString($passPathAsString) {
        $this->setOptions(array('passPathAsString' => $passPathAsString));
    }

    function getPassPathAsString() {
        return $this->passPathAsString;
    }

    /**
     * @param bool|array $cacheableMethods  TRUE = all, false = none, array = list of MY methods that are cacheable
     */
    function setCacheableMethods($cacheableMethods) {
        $this->setOptions(array('cacheableMethods' => $cacheableMethods));
    }

    /**
     * @param bool Whether spawned parameter instances should be cached (instead of creating new one every time)
     */
    function setCacheChildren($cacheChildren) {
        $this->setOptions(array('cacheChildren' => $cacheChildren));
    }

    function getCacheChildren() {
        return is_array($this->children);
    }
    
    function getCacheableMethods() {
        return $this->cacheableMethods;
    }    
    
    protected function doGetFromObject($src, $default) {
        if (isset($this->methodMap['value'])) {
            return $this->callSrcMethod('value');
        } else {
            return Ac_Accessor::getObjectPropertyByPath($src, $this->path, $default);
        }
    }
    
    protected function setOptions(array $options) {
        if (isset($options[$k = 'methodMap'])) {
            if (is_array($options[$k])) $this->$k = $options[$k];
            else throw Ac_E_InvalidCall::wrongType("options[$k]", $options[$k], array('array'));
            unset($options[$k]);
        }
        if (isset($options[$k = 'cacheableMethods'])) {
            if (is_bool($options[$k]) || is_array($options[$k])) $this->$k = $options[$k];
            else throw Ac_E_InvalidCall::wrongType("options[$k]", $options[$k], array('array', 'boolean'));
            unset($options[$k]);
        }
        if (isset($options[$k = 'passPathAsString'])) {
            $this->$k = (bool) $options[$k];
            unset($options[$k]);
        }
        if (isset($options[$k = 'cacheChildren'])) {
            if ($options[$k] && !is_array($this->children)) $this->children = array();
                else $this->children = false;
            unset($options[$k]);
        }
        if ($options) throw new Ac_E_InvalidCall("Unknown options(s): ".implode(", ", array_keys($options)));
        $this->gotValue = false;
        $this->methodsCache = array();
        if (is_array($this->children)) $this->children = array();
    }

    protected function doOnGotValue($res) {
        if ($this->cache) {
            $this->gotValue = true;
            $this->value = $res;
        }
    }
    
    function setCache($value) {
        $value = (bool) $value;
        if ($this->cache && $this->gotValue && !$value) {
            $this->gotValue = false;
            $this->methodsCache = array();
        }
    }
    
    function callSrcMethod($methodName, array $extraArgs = array(), $dontAddPath = false) {
        if ($extraArgs) {
            $id = md5(serialize($extraArgs));
        } else {
            $id = '';
        }
        if (
            array_key_exists($methodName, $this->methodsCache) &&
            array_key_exists($id, $this->methodsCache[$methodName])
        ) {
            $res = $this->methodsCache[$methodName][$id];
        } else {
            if (isset($this->methodMap[$methodName]))
                $m = $this->methodMap[$methodName];
            else 
                $m = $methodName;
            if ($dontAddPath) {
                $args = $extraArgs;
            } else {
                $p = $this->passPathAsString? Ac_Util::arrayToPath($this->path) : $this->path;
                $args = array_merge(array($p), $extraArgs);
            }
            $res = call_user_func_array(array($this->src, $m), $args);
            if ($this->cacheableMethods === true || $this->cacheableMethods && in_array($methodName, $this->cacheableMethods)) {
                $this->methodsCache[$methodName][$id] = $res;
            }
        }
        return $res;
    }
    
    function __call($method, $args) {
        return $this->callSrcMethod($method, $args);
    }
    

    /**
     * @param string $path
     * @return Ac_Accessor_PathRef
     */
    function __get($path) {
        if (is_array($this->children) 
            && 
            isset($this->children[$strPath = Ac_Util::arrayToPath($path)])
        ) {
            $res = $this->children[$strPath];
        } else {
            $res = new Ac_Accessor_PathRef($this->src, array_merge($this->path, is_array($path)? $path : Ac_Util::pathToArray($path)), $this->default, $this->cache);
            $res->methodMap = $this->methodMap;
            $res->passPathAsString = $this->passPathAsString;
            $res->cacheableMethods = $this->cacheableMethods;
            if (is_array($this->children)) $this->children[$strPath] = $res;
        }
        return $res;
    }
    
    /**
     * Static function to start param chains
     * Creates Param Value with src, but without any paths

     * @return Ac_Accessor_PathRef
     */
    static function chain($src) {
        return new Ac_Accessor_PathRef($src, array());
    }
    
    
}