<?php

/**
 * To define new names for members and methods, use
 * protected static $_compat_{oldProperty} = '{newPropertyName}' 
 * (or TRUE if it was promoted to getter)
 */
trait Ac_Compat_Overloader {
    
    /**
     * @var int one of Ac_I_Compat::ACTION_ constants
     */
    protected $compatOverloaderAction = null;
    
    static $defaultAction = Ac_I_Compat::ACTION_NONE;
    
    function & __get($property) {
        $res = $this->compatGet($property);
        return $res;
    }
    
    // by default __set doesn't allow creation of arbitrary properties
    function __set($property, $value) { 
        return $this->compatSet($property, $value);
    }
    
    function __call($method, $args) {
        return $this->compatCall($method, $args);
    }
    
    function __isset($property) {
        return $this->compatIsset($property);
    }
    
    function hasMethod($methodName) {
        if (method_exists($this, $methodName) || $this->compatCall($methodName, [], true)) {
            return true;
        }
        if (isset(self::$magicParent) && method_exists($parent = self::$magicParent, 'hasMethod')) {
            return $parent::hasMethod($methodName);
        }
        return false;
    }
    
    protected static function implCompatOverloaderAction($class, $action, $old, $new, $mode) {
        if ($action === null) $action = Ac_Compat_Overloader::$defaultAction;
        if (!$action) return;
        $newMsg = $new;
        $oldMsg = $old;
        if ($mode === Ac_I_Compat::MODE_RENAME_METHOD) {
            $oldMsg = "{$old}()";
            $newMsg = "{$new}()";
        } elseif ($mode === Ac_I_Compat::MODE_USE_ACCESSOR) {
            $newMsg = "{$new}()";
            $oldMsg = "${$old}";
        } elseif ($mode === Ac_I_Compat::MODE_RENAME_PROPERTY) {
            $newMsg = "$".$new;
            $oldMsg = "$".$old;
        }
        $message = "Access to {$class}::{$oldMsg} is deprecated. Use {$class}::{$newMsg} instead.";
        if ($action === Ac_I_Compat::ACTION_DEPRECATED) {
            trigger_error($message, E_USER_DEPRECATED);
            return;
        }
        
        // Ac_I_Compat::ACTION_THROW or any other value
        throw Ac_Compat_Exception($message);
    }
    
    protected $compatCallLock = 0;
    
    protected function compatPropertyAccess($property, $type = Ac_I_Compat::ACCESS_GET, $setValue = null, $allowArbitrarySet = true) {
        $newProperty = false;
        $accessor = null;
        if ($type === Ac_I_Compat::ACCESS_GET) {
            $accessorPrefix = 'get';
        } else {
            $accessorPrefix = 'set';
        }
        $newProperty = null;
        $newPropertyVar = '_compat_'.$property;
        if (isset(self::$$newPropertyVar)) $newProperty = self::$$newPropertyVar;
        if (!$newProperty) { // there's no alias
            
            // !! TODO: compatibility with parent's __get, __set, __isset
            
            // standard PHP behaviour for isset($object->var) is to return FALSE when variable doesn't exist or is out of scope
            
            $parent = get_parent_class($this);
            
            if (!$this->compatCallLock) {
                if ($type === Ac_I_Compat::ACCESS_ISSET) {
                    if (isset(self::$magicParent) && method_exists($parent = self::$magicParent, '__isset')) {
                        $this->compatCallLock++;
                        $res = $parent::__isset($property);
                        $this->compatCallLock--;
                        return $res;
                    }
                    return false;
                } elseif ($type === Ac_I_Compat::ACCESS_GET) {
                    if (isset(self::$magicParent) && method_exists($parent = self::$magicParent, '__get')) {
                        $this->compatCallLock++;
                        $res = $parent::__get($property);
                        $this->compatCallLock--;
                        return $res;
                    }
                } else {
                    if (isset(self::$magicParent) && method_exists($parent = self::$magicParent, '__set')) {
                        $this->compatCallLock++;
                        $res = $parent::__set($property, $setValue);
                        $this->compatCallLock--;
                        return $res;
                    }
                }
            }
            
            
            if ($type === Ac_I_Compat::ACCESS_SET && $allowArbitrarySet) {
                // since this was done through compat, property is either private or protected
                if (!in_array($property, array_keys(get_object_vars($this)))) { 
                    $this->$property = $setValue;
                    return;
                }
                $rp = new ReflectionProperty(get_class($this), $property);
                $modifier = $rp->isPrivate()? "private" : "protected";
                trigger_error("Cannot access {$modifier} property ".get_class($this)."::{$property}", E_USER_ERROR);
            }
            
            trigger_error("Undefined property: ".get_class($this)."::\${$property}", E_USER_NOTICE);
            return;
            
        }
        $mode = null;
        $mode = Ac_I_Compat::MODE_USE_ACCESSOR;
        if ($newProperty === true) {
            $newProperty = $property;
            $accessor = $accessorPrefix.ucfirst($newProperty);
        } elseif (!Ac_Accessor::methodExists($this, $accessor)) {
            $accessor = $accessorPrefix.ucfirst($newProperty);
            $accessor = null;
            $mode = Ac_I_Compat::MODE_RENAME_PROPERTY;
        }
        $this->implCompatOverloaderAction (
            get_class($this), 
            $this->compatOverloaderAction, 
            $property, $accessor? $accessor : $newProperty, 
            $mode
        );
        if ($type === Ac_I_Compat::ACCESS_GET) {
            if ($accessor) return $this->$accessor();
            return $this->$newProperty;
        }
        if ($type === Ac_I_Compat::ACCESS_SET) {
            if ($accessor) return $this->$accessor($setValue);
            $this->$newProperty = $setValue;
            return;
        }
        if ($type === Ac_I_Compat::ACCESS_ISSET) {
            // __isset() has non-standard behavioir for compatibility with Ac_I_Prototyped:
            // returns TRUE if class member is defined
            return true;
        }
        
        throw Ac_E_InvalidCall::outOfConst('type', $type, 'ACCESS_', 'Ac_I_Compat');
    }
    
    protected function compatGet($var) {
        return $this->compatPropertyAccess($var, Ac_I_Compat::ACCESS_GET);
    }
    
    protected function compatSet($property, $value, $allowArbitrarySet = true) {
        return $this->compatPropertyAccess($property, Ac_I_Compat::ACCESS_SET, $value, $allowArbitrarySet);
    }
    
    protected function compatIsset($property) {
        return $this->compatPropertyAccess($property, Ac_I_Compat::ACCESS_ISSET);
    }
    
    protected function compatCall($method, $args, $checkOnly = false) {
        $newMethod = null;
        $newMethodVar = '_compat_'.$method;
        if (isset(self::$$newMethodVar)) $newMethod = self::$$newMethodVar;
        if (!$newMethod) {
            if ($checkOnly) {
                return false;
            }
            if (isset(self::$magicParent) && method_exists($parent = self::$magicParent, '__call')) {
                $res = parent::__call($method, $args);
                return $res;
            }
            trigger_error("Call to undefined method ".get_class($this)."::{$method}()", E_USER_ERROR);
            return;
        }
        if ($checkOnly) return true;
        $this->implCompatOverloaderAction (
            get_class($this), 
            $this->compatOverloaderAction, 
            $method, $newMethod, Ac_I_Compat::MODE_RENAME_METHOD
        );
        if (!$args) return $this->$newMethod();
        $c = count($args);
        if ($c == 1) return $this->$newMethod($args[0]);
        if ($c == 2) return $this->$newMethod($args[0], $args[1]);
        if ($c == 3) return $this->$newMethod($args[0], $args[1], $args[2]);
        return call_user_func_array([$this, $newMethod], $args);
    }
    
}