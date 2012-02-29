<?php

abstract class Ae_Autoparams {

    function hasPublicVars() {
        return false;
    }
    
    function __construct (array $options = array()) {
        $this->initFromOptions($options);
    }
    
    protected function initFromOptions(array $options = array()) {
        $v = ($this->hasPublicVars());
        $gotKeys = array();
        if ($v) $v = array_flip(array_keys(Ae_Util::getClassVars(get_class($this))));
        foreach ($options as $n => $opt) {
            if (method_exists($this, $mn = 'set'.ucfirst($n))) {
                $this->$mn($opt);
                $gotKeys[] = $n;
            } elseif (isset($v[$n])) {
                $this->$n = $opt;
                $gotKeys[] = $n;
            }
        }
        return $gotKeys;
    }
    
    static function factory($prototype, $baseClass = null) {
        $className = $baseClass;
        
        if (is_object($prototype)) {
            $res = $prototype;
        } else {
            if (!is_array($prototype)) $prototype = array('class' => (string) $prototype);
            
            if (isset($prototype['class'])) $className = $prototype['class'];
            
			/**
			 * A bug very hard to spot; workaround for https://bugs.php.net/bug.php?id=53727, https://bugs.php.net/bug.php?id=51570
             * Had to call class_exists to autoload class
			 */
            if (class_exists($className) && is_subclass_of($className, 'Ae_Autoparams')) { 
                $res = new $className ($prototype);
            } else {
                
                // Other objects can be instantiated using prototype arrays too
                
                $p = $prototype;
                if (isset($p['__construct']) && is_array($c = $p['__construct'])) {
                    $r = new ReflectionClass($className);
                    ksort($c);
                    $res = $r->newInstanceArgs(array_values($c));
                    unset($p['__construct']);
                } else {
                    $res = new $className();
                }
                Ae_Autoparams::setObjectProperty($res, $p);
            }
        }
        if (strlen($baseClass && !$res instanceof $baseClass)) throw new Exception(get_class($res).' is not an instance of '.$baseClass);
        return $res;
    }

    static function factoryCollection(
        array $prototypes, 
        $baseClass = 'Ae_Autoparams', 
        array $defaults = array(), 
        $keyToProperty = false, 
        $keyToCollection = true, 
        $setObjectPropertiesToDefaults = false, 
        & $resArray = null,
        $treatNumericKeysAsEmpty = false
    ) {
        $res = array();
        $p = array_merge(array(), $prototypes);
        if ($keyToCollection === true) $keyToCollection = $keyToProperty;
        foreach ($prototypes as $k => $v) {
            if (is_array($v)) {
	            $proto = Ae_Util::m($defaults, $v);
	            if ($keyToProperty !== false && !($treatNumericKeysAsEmpty && is_numeric($k)) && !isset($proto[$keyToProperty])) $proto[$keyToProperty] = $k;
	            $item = self::factory($proto, $baseClass);
	            if ($keyToCollection !== false) $collectionKey = self::getObjectProperty($item, $keyToCollection, $k);
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
			            if ($keyToCollection !== false) $collectionKey = self::getObjectProperty($item, $keyToCollection, $k);
			                else $collectionKey = $k;
                        if ($setObjectPropertiesToDefaults) 
                            self::setObjectProperty($item, $defaults);
			            $res[$collectionKey] = $item;
                    }
                }
            }
        }
        return $res;
    }

    static function getObjectPropertyByPath($item, array $path, $defaultValue = null) {
        $head = $item;
        while (is_object($head) && !is_null($segment = array_shift($path))) {
            $head = self::getObjectProperty($head, $segment, $defaultValue);
        }
        $res = count($path)? $defaultValue : $head;
        return $res;;
    }
    
    static function getObjectProperty($item, $propertyName, $defaultValue = null) {
        if (is_array($item)) {
            $res = array();
            foreach ($item as $k => $v) {
                $res[$k] = self::getObjectProperty($v, $propertyName, $defaultValue);
            }
            return $res;
        } elseif (is_array($propertyName)) {
            $res = array();
            foreach ($propertyName as $k) {
                $res[$k] = self::getObjectProperty($item, $k, $defaultValue);
            }
            return $res;
        } elseif ($propertyName instanceof Ae_I_Getter) {
            $res = $propertyName->get($item, $defaultValue);
        } else {
            if (strlen($propertyName) && method_exists($item, $g = 'get'.ucFirst($propertyName))) {
                $res = $item->$g();
            } elseif ($item instanceof Ae_Model_Data && $item->hasProperty($propertyName)) {
                $pi = $item->getPropertyInfo($propertyName, true);
                if ($pi->assocClass) $res = $item->getAssoc($propertyName);              
                    else $res = $item->getField($propertyName);
            } elseif (($item instanceof Ae_Autoparams? $item->hasPublicVars() : true) && array_key_exists($propertyName, Ae_Util::getClassVars(get_class($item)))) {
                $res = $item->$propertyName;
            } else {
                $res = $defaultValue;
            }
        }
        return $res;
    }

    static function setObjectProperty($item, $propertyName, $value = null) {
        if (is_array($item)) {
            $res = 0;
            foreach ($item as $c) {
                $res += (int) self::setObjectProperty($c, $propertyName, $value);
            }
            return $res;
        } else {
            
            if (is_array($propertyName) && func_num_args() < 3) {
                $res = 0;
                foreach ($propertyName as $k => $v) {
                    $res += self::setObjectProperty($item, $k, $v);
                }
            } else {
	            if (strlen($propertyName) && method_exists($item, $s = 'set'.ucFirst($propertyName))) {
	                $res = true;
	                $item->$s($value);
	            } elseif (($item instanceof Ae_Model_Data) && $item->hasProperty($propertyName)) {
	                $res = true;
                    $pi = $item->getPropertyInfo($propertyName, true);
                    if ($pi->assocClass) $item->setAssoc($propertyName, $value);              
                        else $res = $item->setField($propertyName, $value);
	                //$item->setField
	            } elseif ($item instanceof Ae_Autoparams? $item->hasPublicVars() : true) {
	                $res = true;
	                $item->$propertyName = $value;	                
	            } else {
	                $res = false;
	            }
	            
            }
            return $res;
        }
    }

    static function itemMatchesPattern($item, array $pattern, $strict = false, $className = false) {
        if ($className !== false && !($item instanceof $className)) return false;
        if ($strict) {
            foreach ($pattern as $propName => $propValue) if (self::getObjectProperty($item, $propName) !== $propValue) return false;
        } else {
            foreach ($pattern as $propName => $propValue) {
                if (self::getObjectProperty($item, $propName) != $propValue) {
                    return false;
                }
            }
        }
        return true;
    }

    static function findItems(array $items, array $pattern, $strict = false, $preserveKeys = false, $className = false) {
        $res = array();
        foreach ($items as $k => $item) {
            if (self::itemMatchesPattern($item, $pattern, $strict, $className)) {
                if (!$preserveKeys) $k = count($res);
                $res[$k] = $item;
            }
        }
        return $res;
    }

    /**
     * Collects 'array with parents' (list of objects linked with one another by $propName)
     * For example, if $propName is 'parent', $res will be array($object->getParent(), $object->getParent->getParent()) and so on
     *
     * @param object $base Object to start traversal from
     * @param string $propName Property to find next object in the chain
     * @param bool $rootFirst Whether to put last object in the chain into head of the result (by default it will be last)
     * @param bool $addSelf Whether to add $object to the result array
     * @param bool $noWarningOnRecursion Dont issue warning if cyclic reference detected (routine will break on cyclic reference anyway)
     * @return array
     */
    static function getAllParents($object, $propName = 'parent', $rootFirst = false, $addSelf = false, $noWarningOnRecursion = false) {
        $res = array();
        if ($addSelf) $res[] = $object;
        $curr = $object;
        while (is_object($curr = Ae_Autoparams::getObjectProperty($curr, $propName))) {
            if (in_array($curr, $res, true)) {
                if (!$noWarningOnRecursion)
                    trigger_error("Recursion detected when traversing object list by property '{$propName}'", E_USER_WARNING);
                break;
            }
            $res[] = $curr;
        }
        if ($rootFirst) $res = array_reverse($res);
        return $res;
    }

}
