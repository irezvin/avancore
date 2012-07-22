<?php

class Ac_Prototype_Builder {

    protected $target = null;
    
    protected $hasTarget = false;
    
    protected $single = false;
    
    protected $keyProperty = false;
    
    protected $defaults = array();
    
    protected $overrides = array();
    
    protected $prototypes = array();
    
    protected $current = array();
    
    function __construct(& $target = null, $keyProperty = false, $single = false) {
        if (!is_null($target)) {
             if (!is_array($target)) throw Ac_E_InvalidCall::wrongType('target', $target, array('null', 'array'));
             $this->target = & $target;
             $this->hasTarget = true;
        }
        $this->keyProperty = $keyProperty;
        $this->single = $single;
    }
    
    function getSingle() {
        return $this->single;
    }
    
    function getKeyProperty() {
        return $this->keyProperty;
    }
    
    function getHasTarget() {
        return $this->hasTarget;
    }
    
    protected function apply(& $target, $part, $isDefault, $single = null) {
        if (is_null($single)) $single = $this->single;
        $part = is_array($part)? $part : Ac_Prototype_Chain::getResult($part);
        if ($single) $target = $isDefault? Ac_Util::m($part, $target) : Ac_Util::m($target, $part);
            else {
                foreach ($this->target as & $t) {
                    $t = $isDefault? Ac_Util::m($part, $t) : Ac_Util::m($t, $part);
                }
            }
    }

    function addDefault($prototypePart, $_ = null) {
        for ($i = func_num_args() - 1; $i >= 0; $i--) { // Defaults are applied in reverse order to get same results both using target and getResult()
            $part = func_get_arg($i);
            if (!$this->isPrototypePart($part, $e, $i)) throw $e;
            if (is_string($part)) $part = array('class' => $part);
            if ($this->hasTarget) $this->apply($this->target, $part, true);
            $this->defaults[] = $part;
        }
    }

    function addOverride($prototypePart, $_ = null) {
        for ($i = 0; $i < func_num_args(); $i++) {
            $part = func_get_arg($i);
            if (!$this->isPrototypePart($part, $e, $i)) throw $e;
            if (is_string($part)) $part = array('class' => $part);
            if ($this->hasTarget) $this->apply($this->target, $part, false);
            $this->overrides[] = $part;
        }
    }
    
    /**
     * Can be called in two forms:
     * -    addPrototype($prototype)
     * -    addPrototype($key, $prototype)
     * 
     * If key cannot be determined, assigns numeric key and DOES NOT assign key to a prototype.
     * 
     * It is not allowed to add one prototype with the same key more than once 
     * (and to call addPrototype() more than once if $single == true).
     * 
     * Ac_E_InvalidUsage will be thrown in that case (Since such attempt indicates
     * some flaw in calling code' logic)
     * 
     * @param type $keyOrPrototype Key (is $prototype is not provided) or prototype (if it is a single arg)
     * @param type $prototype $prototype (only if $key is provided)
     */
    function addPrototype($keyOrPrototype, $prototype = null) {
        if (func_num_args() == 1) {
            $prototype = $keyOrPrototype;
            $key = null;
        } else {
            $key = $keyOrPrototype;
        }
        if (is_string($prototype)) $prototype = array('class' => $prototype);
        if ($prototype instanceof Ac_Prototype_Chain) $prototype = Ac_Prototype_Chain::getResult($prototype);
        if ($this->keyProperty !== false) {
            if (is_null($key)) $key = $this->getKeyFromPrototype($prototype);
            else $this->putKeyToPrototype($prototype, $key);
        }
        if ($this->single) { 
            if ($this->prototypes) {
                // already added
                throw Ac_E_InvalidUsage("Cannot addPrototype() more than once to Ac_Prototype_Builder instance in \$single mode");
            } else {
                if (is_null($key)) $key = 0;
                $this->prototypes[$key] = $prototype; 
                if ($this->hasTarget) {
                    $this->target = $this->getResult();
                }
            }
        } else {
            if (!is_null($key)) {
                if (isset($this->prototypes[$key])) {
                    // already added
                    throw Ac_E_InvalidUsage("Prototype with key '{$key}' was already added to the builder");
                } else {
                    $this->prototypes[$key] = $prototype;
                    if ($this->hasTarget) $this->target[$key] = $this->getResult (false, $key);
                }
            } else {
                $this->prototypes[] = $prototype;
                if ($this->hasTarget) {
                    $k = array_keys($prototype);
                    $key = array_pop($k); // get last key
                    $this->target[] = $this->getResult (false, $key);
                }
            }
        }
    }
    
    /**
     * Alias of Ac_Prototype_Builder::addPrototype() but with chaining
     * @return Ac_Prototype_Builder
     */
    function add($keyOrPrototype, $prototype = null) {
        $this->addPrototype($keyOrPrototype, $prototype);
        return $this;
    }
    
    /**
     * Clones the builder if defaults are provided and adds defaults to the clone
     * Otherwise returns $this
     */
    function define() {
        $args = func_get_args();
        if ($args) {
            $res = $this->cloneObject();
            call_user_func_array(array($res, 'addDefault'), $args);
        } else {
            $res = $builder;
        }
        return $res;
    }
    
    /**
     * Clones the builder if defaults are provided and adds defaults to the clone
     * Otherwise returns $this
     */
    function override() {
        $args = func_get_args();
        if ($args) {
            $res = $this->cloneObject();
            call_user_func_array(array($res, 'addOverride'), $args);
        } else {
            $res = $builder;
        }
        return $res;
    }
    
    /**
     * Creates bound chain 
     * @return Ac_Prototype_Chain
     */
    function also() {
        return new Ac_Prototype_Chain($this);
    }
    
    /**
     * Allows simple addition of prototypes by the form $builder->key = $prototype
     * @param string $key Key
     * @param array|object|string|Null $prototype 
     */
    function __set($key, $value) {
        $this->addPrototype($var, $value);
    }
    
    function __get($key) {
        return $this->getResult(false, $key);
    }
    
    function __isset($key) {
        return array_key_exists($key, $this->prototypes);
    }
    
    function __list_all_properties() {
        return $this->listPrototypes();
    }
    
    /**
     * Clones current builder with target, defaults and overrides (does not copy prototypes)
     * @return Ac_Prototype_Builder
     */
    function cloneObject() {
        $res = new Ac_Prototype_Builder($this->target, $this->keyProperty, $this->single);
        $res->defaults = array_slice($this->defaults, 0);
        $res->overrides = array_slice($this->overrides, 0);
        return $res;
    }
    
    protected function getKeyFromPrototype($prototype) {
        $res = null;
        if (is_array($prototype)) {
            if (isset($prototype[$this->keyProperty])) $res = $prototype[$this->keyProperty];
        } elseif (is_object($prototype)) {
            $res = Ac_Accessor::getObjectProperty($prototype, $this->keyProperty);
        }
        return $res;
    }
    
    protected function putKeyToPrototype(& $prototype, $key) {
        if (is_array($prototype)) $prototype[$this->keyProperty] = $key;
        elseif (is_object($prototype)) {
            Ac_Accessor::setObjectProperty($prototype, $this->keyProperty, $key);
        }
    }
    
    static function isPrototypePart($prototypePart, & $e = null, $argName = '$prototypePart') {
        $res = true;
        if (!(is_string($prototypePart) || is_array($prototypePart) || is_object($prototypePart) && $prototypePart instanceof Ac_Prototype_Chain)) {
            $res = false;
            $e = Ac_E_InvalidCall::wrongType($argName, $prototypePart, array('string', 'array', 'Ac_Prototype_Chain'));
        }
        return $res;
    }
    
    function listPrototypes() {
        return array_keys($this->prototypes);
    }

    /**
     * Builds and returns prototype or array of prototypes
     * 
     * If $single is true or $forKey is provided, and $alwaysReturnMany is FALSE, method will return 
     * - prototype of a single object, 
     * - instance of that object (if it was provided to addPrototype())
     * - NULL if no prototype or instance was provided
     * 
     * If $single is false or $alwaysReturnMany is true, method will return array with 0..N prototypes 
     * (N = 1 for $single mode)
     * 
     * @param $alwaysReturnMany Return array with key => prototype even if $single property is set to TRUE
     * @param string|null $forKey Return only result containing prototype(s) for given key
     * @return array|object|null
     * 
     * @throws Ac_E_InvalidCall if wrong $forKey is provided
     */
    function getResult($alwaysReturnMany = false, $forKey = null) {
        $res = array();
        if (!is_null($forKey)) {
             if (!array_key_exists($forKey, $this->prototypes)) 
                 throw Ac_E_InvalidCall::noSuchItem ('prototype', $forKey, 'listPrototypes');
             $single = true;
             $keys = array($forKey);
        } else {
            $single = $this->single;
            $keys = array_keys($this->prototypes);
        }
        foreach ($keys as $k) {
            $v = $this->prototypes[$k];
            if (is_null($v)) continue;
            if (is_array($v)) {
                $proto = array();
                foreach ($this->defaults as $def) {
                    $this->apply ($proto, $def, true, true);
                }
                $this->apply ($proto, $v, false, true);
                foreach ($this->overrides as $over) $this->apply ($proto, $over, false, true);
            } else {
                $proto = $v;
            }
            $res[$k] = $proto;
        }
        if ($single && !$alwaysReturnMany) {
            $res = array_pop($res);
        }
        return $res;
    }
    
    function getResultAsChain() {
        
    }
    
}