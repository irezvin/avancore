<?php

/**
 * @TODO There is a limitation on Ac_Accessor::getObjectPropertyByPath because it cannot through the arrays
 * (same for Ac_Util::getArrayByPath which cannot pass through the objects)
 */
class Ac_Accessor_Path {
    
    protected $src = false;
    
    protected $path = false;
    
    protected $default = null;
    
    protected $gotValue = false;
    
    protected $value = false;
    
    protected $cache = false;
    
    function __construct($src, $path, $default = null, $cache = false) {
        if (!(is_array($src) || is_object($src))) throw Ac_E_InvalidCall::wrongType('src', $src, array('array', 'object'));
        $this->src = $src;
        $this->path = is_array($path)? $path : Ac_Util::pathToArray($path);
        $this->default = $default;
        if ($cache) {
            if ($this->path) $this->cache = $cache;
            else throw new Ac_E_InvalidCall("Cannot \$cache with empty \$path");
        }
        
    }
    
    /**
     * This returns src only for non-cached values or for cached values BEFORE their first getValue() call
     */
    function getSrc() {
        return $this->src;
    }
    
    function getPath($asString = false) {
        return $asString? Ac_Util::arrayToPath($this->path) : $this->path;
    }
    
    function getDefault() {
        return $this->default;
    }
    
    function getCache() {
        return $this->cache;
    }
    
    function setCache($value) {
        $value = (bool) $value;
        if ($this->cache && $this->gotValue && !$value) throw new Ac_E_InvalidUsage("Cannot setCache(false) after already called getValue() with enabled \$cache");
        if ($value && !$this->path) {
            throw new Ac_E_InvalidCall("Cannot \$cache with empty \$path");
        }
    }
    
    /**
     * Short-form alias for getValue()
     * @return type 
     */
    function value() {
        return $this->getValue();
    }
    
    protected function doOnGotValue($res) {
        if ($this->cache) {
            $this->src = null; // We don't need it anymore so lets free up some memory
            $this->gotValue = true;
            $this->value = $res;
        }        
    }
    
    protected function doGetFromObject($src, $default) {
        return Ac_Accessor::getObjectPropertyByPath($src, $this->path, $default);
    }
    
    function getValue() {
        if ($this->gotValue === false) {
            $src = $this->src;
            if (is_object($src) && $src instanceof Ac_Accessor_Path) $src = $src->getValue();
            
            if ($this->path) {
                if (is_array($src)) $res = Ac_Util::getArrayByPath($src, $this->path, $this->default);
                elseif (is_object($src)) $res = $this->doGetFromObject($src, $this->path, $this->default);
                else $res = $this->default;
            } else {
                $res = $this->src;
            }
            $this->doOnGotValue($res);
        } else {
            $res = $this->value;
        }
        return $res;
    }
    
    function __toString() {
        return htmlspecialchars(''.$this->getValue(), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * @param mixed $default
     * @return Ac_Accessor_Path 
     */
    function cache() {
        $this->setCache(true);
        return $this;
    }
    
    /**
     * @param mixed $default
     * @return Ac_Accessor_Path 
     */
    function def($default) {
        $this->default = $default;
        return $this;
    }
    
    /**
     * @param string $path
     * @return Ac_Accessor_Path 
     */
    function __get($path) {
        $res = new Ac_Accessor_Path($this, $path, $this->cache);
        return $res;
    }
    
    /**
     * Static function to start param chains
     * Creates Param Value with src, but without any paths

     * @return Ac_Accessor_Path
     */
    static function chain($src) {
        return new Ac_Accessor_Path($src, array());
    }
    
}