<?php

class Ac_Cache_Accessor {

    protected $uns = false;
    
    protected $value = false;
    
    protected $id = null;
    
    protected $group = null;
    
    protected $cache = null;
    
    protected $evenOld = false;
    
    function __construct($id, Ac_Cache_Abstract $cache = null, $group = false, $evenOld = false) {
        $this->cache = $cache;
        if (!is_scalar($id)) $id = md5(serialize($id));
        $this->id = $id;
        $this->group = $group;
        $this->evenOld = (bool) $evenOld;
    }
    
    function has() {
        if ($this->value === false) $this->get();
        return $this->value !== null;
    }
    
    function get($unserialize = false) {
        if ($this->value !== false) return $this->value;
        if (func_num_args()) $this->uns = $unserialize;
        if (!$this->cache) return null;
        $this->value = $this->cache->get($this->id, $this->group, null, $this->evenOld);
        if ($this->uns && strlen($this->value)) $this->value = unserialize($this->value);
        return $this->value;
    }
    
    /**
     * loads value from cache and unserializes it
     */
    function getData() {
        $this->uns = true;
        if ($this->value !== false) $this->value = strlen($this->value)? unserialize($this->value) : null;
        if ($this->value === false) $this->getValue();
        return $this->value;
    }
    
    function setData($data) {
        $this->put(serialize($data));
    }
    
    function data($value = null) {
        if (func_num_args() === 0) return $this->getData();
        elseif ($value === null) $this->delete ();
        else $this->putData($value);
    }
    
    function put($value, $forceSerialize = false) {
        if (!is_scalar($value) || $forceSerialize) {
            $this->uns = true;
        }
        $this->value = $value;
        if (!$this->cache) return;
        $this->cache->put($this->id, $this->uns? serialize($value) : $value, $this->group);
    }
    
    function delete() {
        $this->value = null;
        if (!$this->cache) return;
        $this->cache->delete($this->id, $this->group);
    }
    
    function getValue($asIs = false) {
        if ($this->value !== false || $asIs) return $this->value;
        return $this->get();
    }
    
    function getId() {
        return $this->id;
    }
    
    function getUnserialized() {
        return $this->uns;
    }

    /**
     * @return bool
     */
    function getEvenOld() {
        return $this->evenOld;
    }
    
    /**
     * @return Ac_Cache_Abstract
     */
    function getCache() {
        return $this->cache;
    }
    
}