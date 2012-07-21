<?php

// TODO: implement $convertToPaths (foo__bar -> array('foo', bar'); check Ac_Prototyped static methods to work with paths)
// TODO: some sane logic on trying to get write-only properties or to write read-only properties
// TODO: errors & exceptions handling (how?)
// TODO: add __unset() support and iterator for listable objects
class Ac_Accessor {
    
    protected $src = null;

    protected $convertToPaths = false;
    
    protected $strategy = false;
    
    function getSrc() {
        return $this->src;
    }
    
    function __construct($src, $convertToPaths = false, $strategy = false) {
        $this->src = $src;
        $this->convertToPaths = $convertToPaths;
        if ($strategy) $this->strategy = Ac_Prototyped::factoryOnce($strategy, 'Ac_I_AccessorStrategy');
    }
    
    function getProperty($name) {
        if ($this->convertToPaths) $name = $this->convertToPath($name);
        if ($this->strategy) return $this->strategy->getPropertyOf($this->src, $name);
            else return Ac_Accessor::getObjectProperty($this->src, $name);
    }
    
    function hasProperty($name) {
        if ($this->convertToPaths) $name = $this->convertToPath($name);
        if ($this->strategy) return $this->strategy->testPropertyOf($this->src, $name);
        return Ac_Accessor::objectPropertyExists($this->src, $name);
    }
    
    function setProperty($name, $value) {
        if ($this->convertToPaths) $name = $this->convertToPath($name);
        if ($this->strategy) return $this->strategy->setPropertyOf($this->src, $name, $value);
        return Ac_Accessor::setObjectProperty($this->src, $name, $value);
    }
    
    // TODO: difference for setters & getters?
    function listProperties() {
        if ($this->strategy) return $this->strategy->listPropertiesOf($this->src);
        return Ac_Accessor::listObjectProperties($this->src);
    }
    
    function convertToPath($name) {
        return is_array($name)? $name : explode('__', $name);
    }
    
    function __get($name) {
        return $this->getProperty($name);
    }
    
    function __set($name, $value) {
        return $this->setProperty($name, $value);
    }
    
    function __isset($name) {
        return $this->hasProperty($name);
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
        } elseif ($propertyName instanceof Ac_I_Getter) {
            $res = $propertyName->get($item, $defaultValue);
        } else {
            if (strlen($propertyName) && method_exists($item, $g = 'get'.ucFirst($propertyName))) {
                $res = $item->$g();
            } elseif ($item instanceof Ac_Model_Data && $item->hasProperty($propertyName)) {
                $pi = $item->getPropertyInfo($propertyName, true);
                if ($pi->assocClass) $res = $item->getAssoc($propertyName);              
                    else $res = $item->getField($propertyName);
            } elseif (($item instanceof Ac_I_Prototyped? $item->hasPublicVars() : true) && (array_key_exists($propertyName, Ac_Util::getClassVars(get_class($item))))) {
                $res = $item->$propertyName;
            } else {
                $res = $defaultValue;
            }
        }
        return $res;
    }
    
    static function listObjectProperties($item) {
        if (is_array($item)) {
            $res = array();
            foreach ($item as $k => $v) $res[$k] = self::listObjectProperties ($item);
            return $res;
        } elseif ($item instanceof Ac_Model_Data) {
            $res = $item->listProperties();
        } else {
            if (!($item instanceof Ac_I_Prototyped) || $item->hasPublicVars()) {
                $vars = array_keys(Ac_Util::getPublicVars($item));
            } else {
                $vars = array();
            }
            if (method_exists($item, '__list_magic')) {
                $magic = $item->__list_magic();
            } else {
                $magic = array();
            }
            $res = array_unique(array_merge(
                    self::listObjectAccessors($item, 'get'),
                    self::listObjectAccessors($item, 'set', 1),
                    $vars,
                    $magic
            ));
        }
        return $res;
    }
    
    private static $apListAccessorsCache = array();
    
    static function listObjectAccessors($item, $prefix = 'get', $nArgs = 0, $stripPrefixAndLcFirst = true) {
        $c = is_object($item)? get_class($item) : '';
        $hash = $c.'/'.$prefix.'/'.$nArgs.'/'.$stripPrefixAndLcFirst;
        if (!(isset(self::$apListAccessorsCache[$hash]))) {
            $refl = new ReflectionClass($c);
            $l = strlen($prefix);
            self::$apListAccessorsCache[$hash] = array();
            foreach ($refl->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
                if (!strncmp($acc = $m->getName(), $prefix, $l) && !$m->isStatic()) {
                    if ($m->getNumberOfRequiredParameters() == $nArgs) { // Should we use == or <= ? Can setter have all-default params?
                        if ($stripPrefixAndLcFirst) {
                            $acc = substr($acc ,$l);
                            $acc{0} = strtolower($acc{0});
                        }
                        self::$apListAccessorsCache[$hash][] = $acc;
                    }
                }
            }
        }
        return self::$apListAccessorsCache[$hash];
    }
    
    static function objectPropertyExists($item, $propertyName) {
        if (is_array($item)) {
            $res = array();
            foreach ($item as $k => $v) {
                $res[$k] = self::objectPropertyExists($v, $propertyName);
            }
            return $res;
        } elseif (is_array($propertyName)) {
            $res = array();
            foreach ($propertyName as $k) {
                $res[$k] = self::objectPropertyExists($item, $k);
            }
            return $res;
        } elseif ($propertyName instanceof Ac_I_Getter) {
            $res = $propertyName->get($item, $defaultValue);
        } else {
            if (strlen($propertyName) && method_exists($item, $g = 'get'.ucFirst($propertyName))) {
                $res = true;
            } elseif ($item instanceof Ac_Model_Data && $item->hasProperty($propertyName)) {
                $res = $item->hasProperty($propertyName);
            } elseif (($item instanceof Ac_I_Prototyped? $item->hasPublicVars() : true) && (array_key_exists($propertyName, Ac_Util::getClassVars(get_class($item))))) {
                $res = true;
            } else {
                $res = false;
            }
        }
        return $res;
    }

    static function setObjectProperty($item, $propertyName, $value = null, $strictParams = false) {
        if (is_array($item)) {
            $res = 0;
            foreach ($item as $c) {
                $res += (int) self::setObjectProperty($c, $propertyName, $value, $strictParams);
            }
            return $res;
        } else {
            if (is_array($propertyName) && is_null($value)) {
                $res = 0;
                foreach ($propertyName as $k => $v) {
                    $res += self::setObjectProperty($item, $k, $v, $strictParams);
                }
            } else {
	            if (strlen($propertyName) && method_exists($item, $s = 'set'.ucFirst($propertyName))) {
	                $res = true;
	                $item->$s($value);
	            } elseif (($item instanceof Ac_Model_Data) && $item->hasProperty($propertyName)) {
	                $res = true;
                    $pi = $item->getPropertyInfo($propertyName, true);
                    if ($pi->assocClass) $item->setAssoc($propertyName, $value);              
                        else $res = $item->setField($propertyName, $value);
	                //$item->setField
	            } elseif ($item instanceof Ac_I_Prototyped? $item->hasPublicVars() : true) {
                    if ($strictParams && $propertyName != 'class') {
                        if (!isset($item->$propertyName) && !property_exists($item, $propertyName)) {
                            throw new Ac_E_InvalidPrototype("Unknown member '{$propertyName}' in class '".get_class($item)."'");
                        }
                    }
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
        while (is_object($curr = Ac_Accessor::getObjectProperty($curr, $propName))) {
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