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
     * @param array $id => $id IDs of 'core' mixables
     */
    protected $coreMixableIds = array();
    
    /**
     * @param array $id => $id IDs of Ac_I_Mixable_Shared
     */
    protected $sharedMixableIds = false;
    
    const ACCESS_GET = 0;
    const ACCESS_SET = 1;
    const ACCESS_ISSET = 2;
    const ACCESS_UNSET = 3;

    function __construct(array $prototype = array()) {
        $this->registerCoreMixables();
        // TODO: initialize while providing values to mixables
        parent::__construct($prototype);
    }
    
    protected function initFromPrototype(array $prototype = array(), $strictParams = null) {
        if (isset($prototype['mixables'])) { // always init mixables first
            $this->setMixables($prototype['mixables']);
            unset($prototype['mixables']);
        }
        if (count($this->mixables)) {
            if (is_null($strictParams)) $strictParams = Ac_Prototyped::$strictParams;
            $gotKeys = parent::initFromPrototype($prototype, false);
            
            if ($this->mixPropertyMap === false) $this->fillMixMaps();
            
            $extraProperties = array_diff_key($prototype, array_flip($gotKeys));
            
            foreach (array_intersect_key($extraProperties, $this->mixPropertyMap) as $prop => $val) {
                $this->$prop = $val;
                $gotKeys[] = $prop;
                unset($extraProperties[$prop]);
            }
            
            $acquired = array();
            foreach ($this->mixables as $mixable) {
                if ($mixable instanceof Ac_I_Mixable_WithInit) {
                    $acquiredByMixin = $mixable->handleMixinInit($extraProperties, $this);
                    if (is_array($acquiredByMixin)) $acquired = array_merge($acquired, $acquiredByMixin);
                }
            }
            if ($strictParams) {
                $missingKeys = array_diff(array_keys($extraProperties), $acquired);
                $gotKeys = array_unique(array_merge($gotKeys, $acquired));
                if ($missingKeys) { // mimic Ac_Prototyped behaviour if there are missing keys
                    $first = array_shift($missingKeys);
                    throw new Ac_E_InvalidPrototype("Unknown member '{$first}' in class '".get_class($this)."'");
                }
            }
        } else {
            $gotKeys = parent::initFromPrototype($prototype, $strictParams);
        }
        return $gotKeys;
    }
    
    protected function doGetCoreMixables() {
        return array();
    }
    
    protected function registerCoreMixables() {
        if ($m = $this->doGetCoreMixables()) {
            $this->setMixables($m);
            $ck = array_keys($this->mixables);
            $this->coreMixableIds = array_combine($ck, $ck);
        }
    }
    
    protected function clearMixMaps() {
        $this->mixMethodMap = false;
        $this->mixPropertyMap = false;
    }
    
    public function addMixable(Ac_I_Mixable $mixable, $id = false, $canReplace = false) {
        if ($id === false) $id = $mixable->getMixableId();
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
            if (isset($this->coreMixableIds[$id])) 
                throw new Ac_E_InvalidUsage("Cannot delete core Mixable '$id'; check with getCoreMixables() first");
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
        if (isset($this->mixables[$id])) $res = $this->mixables[$id];
        elseif ($dontThrow) $res = null;
        else throw Ac_E_InvalidCall::noSuchItem ('mixable', $id, 'listMixables');
        return $res;
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
    
    public function getCoreMixables() {
        $res = array();
        foreach ($this->coreMixableIds as $id) $res[$id] = $this->mixables[$id];
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
            foreach (array_diff(array_keys($this->mixables), $this->coreMixableIds) as $id) {
                $tmp = $this->mixables[$id];
                unset($this->mixables[$id]);
                $tmp->unregisterMixin($this);
            }
        }
        foreach ($mixables as $id => $v) {
            if (!is_object($v)) $mix = Ac_Prototyped::factory($v, 'Ac_I_Mixable');
                else $mix = $v;
            if (!$mix instanceof Ac_I_Mixable) throw Ac_E_InvalidCall("\$mixables['{$id}'] must implement "
            . "Ac_I_Mixable, but ".Ac_Util::typeClass ($mix)." was provided instead");
            if (is_numeric($id)) $id = $mix->getMixableId();
            if (is_numeric($id) || !strlen($id)) array_push($this->mixables, $mix);
            else {
                if (isset($this->mixables[$id])) {
                    if (isset($this->coreMixableIds[$id])) 
                        throw new Ac_E_InvalidUsage("Cannot replace core Mixable '{$id}'; "
                        . "check with getCoreMixables() first");
                    $tmp = $this->mixables[$id];
                    unset($this->mixables[$id]);
                    $tmp->unregisterMixin($this);
                }
                $this->mixables[$id] = $mix;
                if (Ac_Accessor::methodExists($mix, 'setMixableId')) {
                    $mix->setMixableId($id);
                }
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
        foreach ($mm as $m) $this->mixMethodMap[strtolower($m)] = false;
        foreach ($this->mixables as $id => $mix) {
            
            if ($mix instanceof Ac_I_Mixable_Shared)
                $this->sharedMixableIds[$id] = $id;
            
            $nm = array_diff($mix->listMixinMethods($this), $mm);
            foreach ($nm as $m) $this->mixMethodMap[strtolower($m)] = $id;
            $mm = array_merge($mm, $nm);
            
            $np = array_diff($mix->listMixinProperties($this), $mp);
            foreach ($np as $p) $this->mixPropertyMap[$p] = $id;
        }
    }
    
    public function hasMethod($methodName) {
        if ($this->mixMethodMap === false) $this->fillMixMaps();
        $res = isset($this->mixMethodMap[strtolower($methodName)]);
        return $res;
    }
    
    public function & __get($property) {
        if ($this->mixPropertyMap === false) $this->fillMixMaps();
        if (isset($this->mixPropertyMap[$property])) {
            $id = $this->mixPropertyMap[$property];
            if (isset($this->sharedMixableIds[$id])) {
                $res = & $this->mixables[$id]->getMixinProperty($this, $property);
            } else {
                $res = & $this->mixables[$id]->$property;
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
    
    public function __list_magic() {
        if ($this->mixPropertyMap === false) $this->fillMixMaps();
        return array_keys($this->mixPropertyMap);
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
        $cMethod = strtolower($method);
        if (isset($this->mixMethodMap[$cMethod])) {
            $id = $this->mixMethodMap[$cMethod];
            if ($id === false) {
                $res = call_user_func_array(array($this, $cMethod), $arguments);
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
    
    function hasPublicVars() {
        if ($this->mixPropertyMap === false) $this->fillMixMaps();
        return (bool) count($this->mixPropertyMap);
    }
    
    /*
    function __construct (array $prototype = array()) {
        $this->initFromPrototype($prototype);
    }
    
    protected function initFromPrototype(array $prototype = array(), $strictParams = null) {
        
    }
    */
   
}