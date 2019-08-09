<?php

class Ac_Lang_Resource {

	protected static $instance = false;
	
	protected $strings = array();
	
    protected $langId = 'en';
    
    protected $providers = array();
    
    protected $stringsLoaded = false;

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
	
	function getString($id, $default = false) {
		if (!$this->stringsLoaded) $this->actualizeStrings();
		if (isset($this->strings[$id])) return $this->strings[$id];  
		return $default === false? ('~'.$id) : $default;
	}
	
	protected function reloadStringsFromProviders() {
		if (!$this->stringsLoaded) {
			$this->strings = array();
			$this->stringsLoaded = false;
		}
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