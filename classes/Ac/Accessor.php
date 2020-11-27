<?php

// TODO: implement $convertToPaths (foo__bar -> array('foo', bar'); check Ac_Prototyped static methods to work with paths)
// TODO: some sane logic on trying to get write-only properties or to write read-only properties
// TODO: add __unset() support and iterator for listable objects
// TODO: wrap Exceptions into some sane objects that know, at least, destination class
class Ac_Accessor implements Ac_I_Accessor {
    
    protected $src = null;

    protected $convertToPaths = false;
    
    protected $strategy = false;
    
    /**
     * @var false|true|array
     */
    protected $collectErrors = false;
    
    /**
     * @var propName => array($exception, $exception...)
     */
    protected $errors = array();
    
    protected static $methodSignatures = array();
    
    function getSrc() {
        return $this->src;
    }
    
    /**
     * Enables error collector
     * Note: errors are collected on set() only
     * 
     * @param bool|scalar|string|array $types 
     * 
     * true = catch all; 
     * false = catch none; 
     * string = class name or 
     * array = class names of exceptions to catch
     */
    function setCollectErrors($types = null) {
       if (is_string($types)) $types = array($types);
       $this->collectErrors = $types; 
    }
    
    function getCollectErrors() {
        return $this->collectErrors;
    }
    
    /**
     * Returns collected errors and clears collected errors' memory
     * @return array Collected errors
     */
    function clearErrors() {
        $res = $this->errors;
        $this->errors = array();
        return $res;
    }
    
    function getErrors($propName = false, $clear = false) {
        if ($propName !== false) {
            if (is_array($propName)) $propName = Ac_Util::arrayToPath($propName);
            $res = isset($this->errors[$propName])? $this->errors[$propName] : array();
            if ($clear) unset ($this->errors[$propName]);
        } else {
            $res = $this->errors;
            if ($clear) $this->errors = array();
        }
        
        return $res;
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
        $res = true;
        
        if ($this->strategy) $f = array($this->strategy, 'setPropertyOf');
        else $f = array('Ac_Accessor', 'setObjectProperty'); 
        
        if ($this->collectErrors !== false) {
            try {
                call_user_func($f, $this->src, $name, $value);
            } catch (Exception $e) {
                $match = false;
                if ($this->collectErrors === true) $match = true;
                else {
                    foreach ($this->collectErrors as $class) {
                        if (is_a($e, $class)) {
                            $match = true;
                            break;
                        }
                    }
                }
                if ($match) { 
                   if (is_array($name)) $name = Ac_Util::arrayToPath ($name);
                    $this->errors[$name][] = $e;
                    $res = false;
                } else {
                    throw $e;
                }
            }
        } else {
            call_user_func($f, $this->src, $name, $value);
        }
        return $res;
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
    
    static function getObjectCollection($object, $listMethod, $getMethod) {
        $res = array();
        foreach($object->$listMethod() as $key) {
            $res[$key] = $object->$getMethod($key);
        }
        return $res;
    }

    static function getObjectPropertyByPath($item, array $path, $defaultValue = null) {
        $head = $item;
        while (is_object($head) && !is_null($segment = array_shift($path))) {
            $head = self::getObjectProperty($head, $segment, $defaultValue);
        }
        $res = count($path)? $defaultValue : $head;
        return $res;
    }
    
    static function getObjectProperty($item, $propertyName, $defaultValue = null, $treatArraysAsObjects = false) {
        if (is_array($item) && !$treatArraysAsObjects || $treatArraysAsObjects === -1) {
            $res = array();
            if ($treatArraysAsObjects === -1) $treatArraysAsObjects = true;
            foreach ($item as $k => $v) {
                $res[$k] = self::getObjectProperty($v, $propertyName, $defaultValue, $treatArraysAsObjects);
            }
            return $res;
        } elseif (is_array($propertyName)) {
            $res = array();
            foreach ($propertyName as $k) {
                $res[$k] = self::getObjectProperty($item, $k, $defaultValue, $treatArraysAsObjects);
            }
            return $res;
        } elseif ($propertyName instanceof Ac_I_Getter) {
            $res = $propertyName->get($item, $defaultValue);
        } elseif (is_array($item)) {
            if (array_key_exists($propertyName, $item)) $res = $item[$propertyName];
                else $res = $defaultValue;
        } elseif (is_object($item)) {
            $args = explode(':', $propertyName);
            $propertyName = $args[0];
            $args = array_slice($args, 1);
            if (strlen($propertyName) && method_exists($item, $g = 'get'.$propertyName)) {
                $res = call_user_func_array(array($item, $g), $args); 
            } elseif ($item instanceof Ac_Model_Data && $item->hasProperty($propertyName)) {
                $pi = $item->getPropertyInfo($propertyName, true);
                if ($pi->assocClass) {
                    $res = $item->getAssoc($propertyName);              
                }
                else $res = $item->getField($propertyName);
            } elseif ($item instanceof Ac_I_Accessor) {
                if ($item->hasProperty($propertyName)) $res = $item->getProperty($propertyName);
                else $res = $defaultValue;
            } elseif (
                ($item instanceof Ac_I_Prototyped? $item->hasPublicVars() : true) 
                && (
                    array_key_exists($propertyName, Ac_Util::getPublicVars($item))
                    || method_exists($item, '__list_magic') && in_array($propertyName, $item->__list_magic())
                    || method_exists($item, '__list_all_properties') && in_array($propertyName, $item->__list_all_properties())
                )
            ) {
                $res = $item->$propertyName;
            } else {
                $res = $defaultValue;
            }
        } else {
            throw new Ac_E_InvalidCall("Unsupported \$item type: ".gettype($item)."; you may have forgotten "
                ."to pass \$treatArraysAsObjects = true when trying to retrieve values from array");
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
            if (method_exists($item, '__list_all_properties')) {
                $res = $item->__list_all_properties();
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
                            $acc[0] = strtolower($acc[0]);
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
            if (strlen($propertyName) && method_exists($item, $g = 'get'.$propertyName)) {
                $res = true;
            } elseif ($item instanceof Ac_Model_Data && $item->hasProperty($propertyName)) {
                $res = true;
            } elseif (($item instanceof Ac_I_Prototyped? $item->hasPublicVars() : true)) {
                $res = (array_key_exists($propertyName, Ac_Util::getClassVars(get_class($item))));
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
	            if (strlen($propertyName) && method_exists($item, $s = 'set'.$propertyName)) {
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

    
    static function itemMatchesPattern($item, array $pattern, $strict = false, $className = false, $treatArraysAsObjects = false) {
        if ($className !== false && !($item instanceof $className)) return false;
        foreach ($pattern as $propName => $propValue) {
            $val = self::getObjectProperty($item, $propName, null, $treatArraysAsObjects);
            if (is_array($propValue)) {
                if (!in_array($val, $propValue, $strict)) return false;
            } elseif ($strict? ($val !== $propValue) : ($val != $propValue)) return false;
        }
        return true;
    }

    static function findItems(array $items, array $pattern, $strict = false, $preserveKeys = false, $className = false, $treatArraysAsObjects = false) {
        $res = array();
        foreach ($items as $k => $item) {
            if (self::itemMatchesPattern($item, $pattern, $strict, $className, $treatArraysAsObjects)) {
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
    
    /**
     * @return array List of positional parameters
     * 
     * Parameters with matching names are passed disregarding to their position
     * Other args are passed by position either in the key (they should begin with digit or have numeric key) and _will_ overwrite parameters with matching names
     * 
     * @param type $reflectionMethod
     * @param array $argsArray 
     * @param bool $useDefaults Try to use default values
     */
    static function mapFunctionArgs(ReflectionFunctionAbstract $reflectionMethod, array $argsArray, $useDefaults = true) {
        ksort($argsArray);
        $posParams = array();
        $maxIdx = -1;
        foreach ($reflectionMethod->getParameters() as $param) {
            $pName = $param->getName();
            if (array_key_exists($pName, $argsArray)) {
                $posParams[$pName] = $argsArray[$pName];
                unset($argsArray[$pName]);
                $maxIdx = $param->getPosition();
            } else {
                $posParams[$pName] = $useDefaults && $param->isDefaultValueAvailable()? $param->getDefaultValue() : null;
            }
        }
        $posParams = array_values($posParams);
        if ($argsArray) { // all parameters with non-matching names will be by-position
            $i = 0;
            foreach ($argsArray as $idx => $value) {
                if (is_int($idx) || is_string($idx) && is_numeric($idx[0])) $idx = (int) $idx;
                else $idx = $i;
                // pad array with empty values
                while (count($posParams) < ($idx + 1)) $posParams[] = null;
                $posParams[$idx] = $value;
                $maxIdx = max($maxIdx, $idx);
                $i++;
            }
        }
        $numParams = max($maxIdx + 1, $reflectionMethod->getNumberOfRequiredParameters());
        $posParams = array_slice($posParams, 0, $numParams); // pass only required and provided parameters 
        return $posParams;
    }
    
    /**
     * Checks whether object method' exists (uses Ac_I_WithMethods::hasMethod when necessary)
     * 
     * @param object|Ac_I_WithMethods $object
     * @param string $methodName
     * @return bool
     */
    static function methodExists($object, $methodName) {
        if (method_exists($object, $methodName)) return true;
        if (method_exists($object, 'hasMethod')) return $object->hasMethod($methodName);
        return false;
    }
    
    /**
     * Checks whether variable $name is callable (uses Ac_I_WithMethods::hasMethod when necessary)
     * 
     * @param Ac_I_WithMethods $name
     * @return bool
     */
    static function isCallable($name) {
        if (is_array($name) && isset($name[0]) && is_object($name[0]) 
            && $name[0] instanceof Ac_I_WithMethods && isset($name[1]) && count($name) == 2) {
            return self::methodExists($name[0], $name[1]);
        } else return is_callable ($name);
    }
    
    static function getMethodSignature($class, $method) {
        if (!isset(self::$methodSignatures[$key = $class.'::'.$method])) {
            self::$methodSignatures[$key] = array();
            $m = new ReflectionMethod($class, $method);
            foreach ($m->getParameters() as $param) {
                $s = $param.'';
                $class = false;
                if (!$param->isArray()) {
                    $ss = explode(">", $s, 2);
                    $s1 = explode(" ", ltrim($ss[1], ' '), 2);
                    if ($s1[0][0] !== '$') $class = $s1[0];
                }
                /* @var $param ReflectionParameter */
                self::$methodSignatures[$key][$paramName = $param->getName()] = array(
                    'name' => $param->getName(),
                    'class' => $class,
                    'isArray' => $param->isArray(),
                    'optional' => $param->isOptional(),
                    'defaultValue' => $param->isOptional()? $param->getDefaultValue() : null,
                    'string' => $s,
                    'readable' => preg_replace('/^[^>]+> /', '', rtrim($s, ' ]')),
                );
            }
        }
        return self::$methodSignatures[$key];
    }    
    
}