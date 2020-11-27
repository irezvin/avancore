<?php

class Ac_Lang_Resource {

    protected static $instance = false;
    
    protected $strings = array();
    
    protected $langId = 'en';
    
    protected $providers = array();
    
    protected $stringsLoaded = false;

    protected static $callbacks = [];
    
    function setLangId($langId) {
        if ($langId !== ($oldLangId = $this->langId)) {
            $this->langId = $langId;
            $this->reloadStringsFromProviders();
        }
    }
    
    function getLangId() {
        return $this->langId;
    }

    function clearProviders($alsoClearStrings = false) {
        $this->providers = array();
        if ($alsoClearStrings) $this->reloadStringsFromProviders();
    }
    
    /**
     * @return array
     */
    function getProviders() {
        return $this->providers; 
    }
    
    /**
     * @param Ac_I_Lang_ResourceProvider | array $resourceProviders One or many resource provider (also any number of arguments can be specified)
     * @return array List of current resource providers
     */
    function setResourceProviders($resourceProviders) {
        $args = func_get_args();
        $providers = Ac_Util::flattenArray($args);
        $this->providers = $providers;
        $this->reloadStringsFromProviders();
    }
    
    /**
     * @param $prefix Return only strings with labels starting with given prefix
     */
    function getStrings($prefix = false) {
        $this->actualizeStrings();
        if ($l = strlen($prefix)) {
            foreach ($this->strings as $k => $s) if (!strncmp($k, $prefix, $l)) {
                $res[$k] = $s;
            }
        } else {
            $res = $this->strings;
        }
        return $res;
    }
    
    function setStrings(array $strings) {
        $this->strings = $strings;
    }
    
    function addStrings(array $strings, $noReplace = false) {
        $this->strings = $noReplace? array_merge($strings, $this->strings) : array_merge($this->strings, $strings); 
    }
    
    static function setInstance(Ac_Lang_Resource $instance) {
        self::$instance = $instance;
    }
    
    /**
     * @return Ac_Lang_Resource
     */
    static function getInstance() {
        if (!self::$instance) self::setInstance(new Ac_Lang_Resource);
        return self::$instance;
    }
    
    static function registerCallback($callback) {
        if (!is_callable($callback)) {
            throw Ac_E_InvalidCall::wrongType('callback', $callback, "callable");
        }
        $k = self::findCallback($callback); 
        if ($k !== false) return $k;
        $k =  count(self::$callbacks);
        self::$callbacks[$k] = $callback;
        return $k;
    }
    
    protected static function findCallback($callback) {
        foreach (self::$callbacks as $k => $item) {
            if ($callback === $item) return $k;
            if (is_array($callback) && is_array($item) && count($callback) == count($item)) {
                foreach ($callback as $kk => $vv) {
                    if (!isset($item[$kk]) || $item[$kk] !== $vv) continue 2;
                    return $k;
                }
            }
        }
        return false;
    }
    
    static function getCallbacks() {
        return self::$callbacks;
    }
    
    static function isCallbackRegistered($callback) {
        return (self::findCallback($callback) !== false);
    }
    
    static function unregisterCallback($callback) {
        $k = self::findCallback($callback);
        if ($k === false) return false;
        array_splice(self::$callbacks, $k, 1);
        return true;
    }
    
    
    function getString($id, $default = false) {
        if (!$this->stringsLoaded) $this->actualizeStrings();
        if (isset($this->strings[$id])) return $this->strings[$id];
        foreach (self::$callbacks as $cb) {
            $res = $cb($id);
            if (is_string($res)) {
                $this->strings[$id] = $res;
                return $res;
            }
        }
        return $default === false? ('~'.$id) : $default;
    }
    
    protected function reloadStringsFromProviders() {
        $this->strings = array();
        $this->stringsLoaded = false;
    }
    
    protected function actualizeStrings() {
        if (!$this->stringsLoaded) {
            $this->stringsLoaded = true;
            foreach ($this->providers as $provider) $this->addStrings($provider->getLangData($this->langId));
        }
    }
    
    function __wakeup() {
        //if (!self::$instance) 
        self::$instance = $this;
    }
    
}