<?php

/**
 * @TODO: move code to call __initialize to some common method? 
 */
if (!class_exists('Ac_Prototyped', false)) require_once(dirname(__FILE__).'/I/Prototyped.php');
abstract class Ac_Prototyped implements Ac_I_Prototyped {
    
    static $strictParams = true;
    
    public static $factoryOnceInstances = array();

    function hasPublicVars() {
        return false;
    }
    
    function __construct (array $prototype = array()) {
        $this->initFromPrototype($prototype);
    }
    
    protected function initFromPrototype(array $prototype = array(), $strictParams = null) {
        
        if (isset($prototype['__initialize']) && is_array($prototype['__initialize'])) {
            $class = new ReflectionClass($myClass = get_class($this));
            foreach ($prototype['__initialize'] as $methodName => $args) {
                if (!is_null($args)) {
                    if (!is_array($args)) $args = array($args);
                    $method = $class->getMethod($methodName);
                    // We have to check methods visibility; otherwise we will be able to call both private and protected methods using __initialize
                    if (!$method->isPublic()) throw new Ac_E_InvalidPrototype("Attempt to call non-public method {$myClass}::{$methodName}() from \$prototype::__initialize block");
                    $posParams = Ac_Accessor::mapFunctionArgs($method, $args, $method->isUserDefined());
                    call_user_func_array(array($this, $methodName), $posParams);
                }
            }
        }
        unset($prototype['__initialize']);
        
        $v = ($this->hasPublicVars());
        $gotKeys = array();
        if ($v) $v = array_flip(array_keys(Ac_Util::getClassVars(get_class($this))));
        if (is_null($strictParams)) $strictParams = self::$strictParams;
        foreach ($prototype as $n => $opt) if ($n !== 'class') {
            if (method_exists($this, $mn = 'set'.$n)) {
                $this->$mn($opt);
                $gotKeys[] = $n;
            } elseif (isset($v[$n]) || method_exists($this, '__isset') && $this->__isset($n)) {
                $this->$n = $opt;
                $gotKeys[] = $n;
            } elseif ($strictParams) {
                throw new Ac_E_InvalidPrototype("Unknown member '{$n}' in class '".get_class($this)."'");
            }
        }
        return $gotKeys;
    }
    
    /**
     * Creates object with given prototype or return previously created instance with the same prototype (only if prototype is an array or a string).
     * If prototype is an object, works as regular Ac_Prototyped::factory
     * 
     * @return object
     */
    static function factoryOnce($prototype, $baseClass = null, $strictParams = null) {
        if (is_array($prototype)) {
            $hash = md5(serialize($prototype));
            $bc = (string) $baseClass;
            if (!isset(self::$factoryOnceInstances[$bc]) || !isset(self::$factoryOnceInstances[$bc][$hash])) {
                self::$factoryOnceInstances[$bc][$hash] = self::factory($prototype, $baseClass, $strictParams);
            }
            $res = self::$factoryOnceInstances[$bc][$hash];
        } else {
            $res = self::factory($prototype, $baseClass, $strictParams);
        }
        return $res;
    }
    
    static function factory($prototype, $baseClass = null, $strictParams = null) {
        if (is_null($strictParams)) $strictParams = self::$strictParams;
        
        $className = $baseClass;
        
        if (is_object($prototype)) {
            $res = $prototype;
        } else {
            if (!is_array($prototype)) $prototype = array('class' => (string) $prototype);

            $class = null;
            
            if (isset($prototype['class'])) $className = $prototype['class'];
            
			/**
			 * A bug very hard to spot; workaround for https://bugs.php.net/bug.php?id=53727, https://bugs.php.net/bug.php?id=51570
             * Had to call class_exists to autoload class
			 */
            if (class_exists($className) && Ac_Util::implementsInterface($className, 'Ac_I_Prototyped')) { 
                $res = new $className ($prototype);
            } else {
                
                // Other objects can be instantiated using prototype arrays too
                
                $p = $prototype;
                if (isset($p['__construct']) && is_array($params = $p['__construct'])) {
                    $class = new ReflectionClass($className);
                    $cr = $class->getConstructor();
                    $posParams = Ac_Accessor::mapFunctionArgs($cr, $params, $cr->isUserDefined());
                    $res = $class->newInstanceArgs(array_values($posParams));
                } else {
                    $res = new $className();
                }
                unset($p['__construct']);
                if (isset($p['__initialize']) && is_array($p['__initialize'])) {
                    if (!$class) $class = new ReflectionClass($className);
                    $ud = $class->isUserDefined();
                    foreach ($p['__initialize'] as $methodName => $args) {
                        if (!is_null($args)) {
                            if (!is_array($args)) $args = array($args);
                            $method = $class->getMethod($methodName);
                            if (!$method->isPublic()) throw new Ac_E_InvalidPrototype("Attempt to call non-public method {$className}::{$methodName}() from \$prototype::__initialize block");
                            $posParams = Ac_Accessor::mapFunctionArgs($method, $args, $method->isUserDefined());
                            call_user_func_array(array($res, $methodName), $posParams);
                        }
                    }
                }
                unset($p['__initialize']);
                Ac_Accessor::setObjectProperty($res, $p, null, $strictParams);
            }
        }
        if (strlen($baseClass && !$res instanceof $baseClass)) throw new Exception(get_class($res).' is not an instance of '.$baseClass);
        return $res;
    }

    static function factoryCollection(
        array $prototypes, 
        $baseClass = 'Ac_Prototyped', 
        array $defaults = array(), 
        $keyToProperty = false, 
        $keyToCollection = true, 
        $setObjectPropertiesToDefaults = false, 
        & $resArray = null,
        $treatNumericKeysAsEmpty = false,
        $strictParams = null
    ) {
        if (is_null($strictParams)) $strictParams = self::$strictParams;
        $res = array();
        $p = array_merge(array(), $prototypes);
        if ($keyToCollection === true) $keyToCollection = $keyToProperty;
        foreach ($prototypes as $k => $v) {
            if (is_string($v)) {
                $v = array('class' => $v);
            }
            if (is_array($v)) {
	            $proto = Ac_Util::m($defaults, $v);
	            if ($keyToProperty !== false && !($treatNumericKeysAsEmpty && is_numeric($k)) && !isset($proto[$keyToProperty])) $proto[$keyToProperty] = $k;
	            $item = self::factory($proto, $baseClass);
	            if ($keyToCollection !== false) $collectionKey = Ac_Accessor::getObjectProperty($item, $keyToCollection, $k);
	                else $collectionKey = $k;
	            if (!is_null($resArray)) $resArray[$collectionKey] = $item;
	            $res[$collectionKey] = $item;
            } else {
                if (is_object($v)) {
                    if (strlen($baseClass) && !$v instanceof $baseClass)
                        throw new Exception("Collection item '{$k} isn\'t an instance of {$baseClass}");
                    else {
                        $item = $v;
		                if (!is_null($resArray)) $resArray[$collectionKey] = $item;
			            if ($keyToCollection !== false) $collectionKey = Ac_Accessor::getObjectProperty($item, $keyToCollection, $k, $strictParams);
			                else $collectionKey = $k;
                        if ($setObjectPropertiesToDefaults) 
                            Ac_Accessor::setObjectProperty($item, $defaults, null, $strictParams);
			            $res[$collectionKey] = $item;
                    }
                }
            }
        }
        return $res;
    }
    
    


}
