<?php

class Ac_Mixin extends Ac_Prototyped implements Ac_I_Mixin {
    
    protected $mixables = array();
    
    /**
     * @param array $methodName => mixableId
     */
    protected $mixMethodMap = false;
    
    /**
     * @param array $propertyName => mixableId
     */
    protected $mixPropertyMap = false;
    
    /**
     * @param array $id => $id IDs of Ac_I_Mixable_Shared
     */
    protected $sharedMixableIds = false;
    
    const ACCESS_GET = 0;
    const ACCESS_SET = 1;
    const ACCESS_ISSET = 2;
    const ACCESS_UNSET = 3;
    
    protected function clearMixMaps() {
        $this->mixMethodMap = false;
        $this->mixPropertyMap = false;
    }
    
    public function addMixable(Ac_I_Mixable $mixable, $id = false, $canReplace = false) {
        $id = $mixable->getMixableId();
        if (is_numeric($id) || !strlen($id)) {
            $this->mixables[] = $mixable;
            end($this->mixables);
            $res = key($this->mixables);
        } else {
            if (isset($this->mixables[$id])) {
                if ($canReplace) $this->deleteMixable($id);
                else throw Ac_E_InvalidCall::alreadySuchItem('mixable', $id, 'deleteMixable');
            }
            $this->mixables[$id] = $mixable;
            $res = $id;
        }
        $mixable->registerMixin($this);
        $this->clearMixMaps();
        return $res;
    }

    public function deleteMixable($id, $dontThrow = false) {
        if (isset($this->mixables[$id])) {
            $tmp = $this->mixables[$id];
            unset($this->mixables[$id]);
            $tmp->unregisterMixin($this);
            $res = true;
            $this->clearMixMaps();
        } else {
            if ($dontThrow) $res = false;
                else throw Ac_E_InvalidCall::noSuchItem ('mixable', $id, 'listMixables');
        }
        return $res;
    }

    public function getMixable($id, $dontThrow = false) {
        if (isset($this->mixables[$id])) $res = $this->mixables;
        elseif ($dontThrow) $res = null;
        else throw Ac_E_InvalidCall::noSuchItem ('mixable', $id, 'listMixables');
    }

    public function getMixables($className = false) {
        if ($className === false) $res = $this->mixables;
        else {
            $res = array();
            foreach ($this->mixables as $id => $mix) 
                if ($mix instanceof $className) $res[$id] = $mix;
        }
        return $res;
    }

    public function listMixables($className = false) {
        if ($className === false) $res = array_keys($this->mixables);
        else {
            $res = array();
            foreach ($this->mixables as $id => $mix) 
                if ($mix instanceof $className) $res[] = $id;
        }
        return $res;
    }

    public function setMixables(array $mixables, $addToExisting = false) {
        $this->clearMixMaps();
        if (!$addToExisting && count($this->mixables)) {
            $tmp = $this->mixables;
            $this->mixables = array();
            foreach ($tmp as $mix) {
                $mix->unregisterMixin($this);
            }
        }
        foreach ($mixables as $k => $v) {
            if (!is_object($v)) $mix = Ac_Prototyped::factory($v, 'Ac_I_Mixable', $v);
                else $mix = $v;
            if (!$mix instanceof Ac_I_Mixable) throw Ac_E_InvalidCall("\$mixables['{$k}'] must implement "
            . "Ac_I_Mixable,  ".Ac_Util::typeClass ($mix)." provided instead");
            if (is_numeric($k)) $k = $mix->getMixableId();
            if (is_numeric($k) || !strlen($k)) array_push($this->mixables, $mix);
            else {
                if (isset($this->mixables[$k])) {
                    $tmp = $this->mixables[$k];
                    unset($this->mixables[$k]);
                    $tmp->unregisterMixin($this);
                }
                $this->mixables[$k] = $mix;
            }
            $mix->registerMixin($this);
        }
    }

    protected function listOwnMethods() {
        $res = Ac_Util::getPublicMethods($this);
        return $res;
    }
    
    protected function fillMixMaps() {
        $this->mixMethodMap = array();
        $this->mixPropertyMap = array();
        $this->sharedMixableIds = array();
        $mm = $this->listOwnMethods();
        $mp = array();
        foreach ($mm as $m) $this->mixMethodMap[$m] = false;
        foreach ($this->mixables as $id => $mix) {
            
            if ($mix instanceof Ac_I_Mixable_Shared)
                $this->sharedMixableIds[$id] = $id;
            
            $nm = array_diff($mix->listMixinMethods(), $mm);
            foreach ($nm as $m) $this->mixMethodMap[$m] = $id;
            $mm = array_merge($mm, $nm);
            
            $np = array_diff($mix->listMixinProperties(), $mp);
            foreach ($np as $p) $this->mixPropertyMap[$p] = $id;
        }
    }
    
    public function hasMethod($methodName) {
        if ($this->mixMethodMap === false) $this->fillMixMaps();
        $res = isset($this->mixMethodMap[$methodName]);
        return $res;
    }
    
    public function __get($property) {
        if ($this->mixPropertyMap === false) $this->fillMixMaps();
        if (isset($this->mixPropertyMap[$property])) {
            $id = $this->mixPropertyMap[$property];
            if (isset($this->sharedMixableIds[$id])) {
                $res = $this->mixables[$id]->getMixinProperty($this, $property);
            } else {
                $res = $this->mixables[$id]->$property;
            }
        } else {
            $res = $this->doAccessMissingProperty($property, self::ACCESS_GET);
        }
        return $res;
    }
    
    public function __set($property, $value) {
        if ($this->mixPropertyMap === false) $this->fillMixMaps();
        if (isset($this->mixPropertyMap[$property])) {
            $id = $this->mixPropertyMap[$property];
            if (isset($this->sharedMixableIds[$id])) {
                $this->mixables[$id]->setMixinProperty($this, $property, $value);
            } else {
                $this->mixables[$id]->$property = $value;
            }
        } else {
            $this->doAccessMissingProperty($property, self::ACCESS_SET, $value);
        }
    }
    
    public function __isset($property) {
        if ($this->mixPropertyMap === false) $this->fillMixMaps();
        if (isset($this->mixPropertyMap[$property])) {
            $id = $this->mixPropertyMap[$property];
            if (isset($this->sharedMixableIds[$id])) {
                $res = $this->mixables[$id]->issetMixinProperty($this, $property);
            } else {
                $res = isset($this->mixables[$id]->$property);
            }
        } else {
            $res = $this->doAccessMissingProperty($property, self::ACCESS_ISSET);
        }
        return $res;
    }
    
    public function __unset($property) {
        if ($this->mixPropertyMap === false) $this->fillMixMaps();
        if (isset($this->mixPropertyMap[$property])) {
            $id = $this->mixPropertyMap[$property];
            if (isset($this->sharedMixableIds[$id])) {
                $this->mixables[$id]->unsetMixinProperty($this, $property);
            } else {
                unset($this->mixables[$id]->$property);
            }
        } else {
            $this->doAccessMissingProperty($property, self::ACCESS_UNSET);
        }
    }
    
    public function __call($method, $arguments) {
        if ($this->mixMethodMap === false) $this->fillMixMaps();
        if (isset($this->mixMethodMap[$method])) {
            $id = $this->mixMethodMap[$method];
            if ($id === false) {
                $res = call_user_func_array(array($this, $method), $arguments);
            }
            if (isset($this->sharedMixableIds[$id])) {
                $res = $this->mixables[$id]->callMixinMethod($this, $method, $arguments);
            } else {
                $res = call_user_func_array(array($this->mixables[$id], $method), $arguments);
            }
        } else {
            $res = $this->doCallUnknownMethod($method, $arguments);
        }
        return $res;
    }
    
    private function intGetCallingContext($private) {
        $t = debug_backtrace();
        $c = get_class($this);
        while ($a = array_shift($t)) {
            $sameClass = 
                isset($a['class'])
                && (
                    $a['class'] == $c 
                    ||  
                    !$private && (
                        is_subclass_of($c, $a['class']) || is_subclass_of($a['class'], $c)
                    ) 
                    || $private && $a['class'] == 'Ac_Mixin'
                );
            if (!$sameClass) return isset($a['class'])? $a['class'] : '';
        }
        return '';
    }
    
    protected function doCallUnknownMethod($method, $arguments) {
        $res = null;
        $rc = new ReflectionClass($c = get_class($this));
        if ($rc->hasMethod($method)) {
            $m = $rc->getMethod($method);
            if ($m->isPublic()) {
                trigger_error("doCallUnknownMethod() is called for public method {$rc}::{$method}(), "
                . "which means doCallUnknownMethod() was used not as intended", E_USER_NOTICE);
                $res = call_user_func_array(array($this, $method), $arguments);
            } else {
                $scope = $m->isPrivate()? "private" : "protected";
                $context = $this->intGetCallingContext($m->isPrivate());
                trigger_error("Call to {$scope} method {$c}::{$method}() from context '{$context}'",
                    E_USER_ERROR);
            }
        } else {
            trigger_error("Call to undefined method {$c}::{$method}()", E_USER_ERROR);
        }
        
        return $res;
    }
     
    protected function doAccessMissingProperty($property, $mode = self::ACCESS_GET, $value = null) {
        
        $res = null;
        $perform = false;
        
        if (in_array($property, array_keys(get_class_vars($c = get_class($this))))) {
            $rc = new ReflectionClass($c);
            if (!$rc->hasProperty($property)) 
                throw new Ac_E_Assertion("Impossible: '{$property}' property is in "
                . "get_class_vars({$c}), but ReflectionClass::hasProperty() returns false!");
            $p = $rc->getProperty($property);
            if ($p->isPublic()) {
                trigger_error("doAccessMissingProperty() is called for public property {$c}::\${$property} "
                . "which means the method was used not as intended", E_USER_NOTICE);
                $perform = true;
            } else {
                if ($mode === self::ACCESS_ISSET) $res = false; // mimic PHP behavior
                else {
                    $scope = $p->isPrivate()? "private" : "protected";
                    trigger_error("Cannot access {$scope} property {$c}::\${$property}", E_USER_ERROR);
                }
            }
        } else {
            $perform = true;
        }
        
        if ($perform) {
            if ($mode === self::ACCESS_GET) $res = $this->$property;
            elseif ($mode === self::ACCESS_SET) $this->$property = $value;
            elseif ($mode === self::ACCESS_UNSET) unset($this->$property);
            elseif ($mode === self::ACCESS_ISSET) $res = isset($this->$property);
        }
        return $res;
    }
    /*
    function __construct (array $prototype = array()) {
        $this->initFromPrototype($prototype);
    }
    
    protected function initFromPrototype(array $prototype = array(), $strictParams = null) {
        
    }
    */
   
}