<?php

abstract class Ac_Cache_Abstract extends Ac_Prototyped {
    
    var $enabled = true;
    
    var $lifetime = 3600;
    
    var $defaultGroup = 'default';
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Ac_Cache_Accessor
     */
    function accessor($id, $group = false) {
        return new Ac_Cache_Accessor($id, $this, $group);
    }

    function has($id, $group = false, & $howOld = null, $lifetime = false, & $filename = null) {
        $howOld = null;
        $filename = null;
        if (!$this->enabled) return false;
        if ($lifetime === false) $lifetime = $this->lifetime;
        if ($group === false) $group = $this->defaultGroup;
        return $this->implHas($id, $group, $howOld, $lifetime, $filename);
    }
    
    function get($id, $group = false, $default = null, $evenOld = false) {
        if (!$this->enabled) return false;
        if ($group === false) $group = $this->defaultGroup;
        return $this->implGet($id, $group, $default, $evenOld);
    }
    
    function put($id, $content, $group = false) {
        if (!$this->enabled) return false;
        if ($group === false) $group = $this->defaultGroup;
        return $this->implPut($id, $content, $group);
    }
    
    function delete($id, $group = false) {
        if (!$this->enabled) return false;
        if ($group === false) $group = $this->defaultGroup;
        return $this->implDelete($id, $group);
    }

    abstract protected function implHas($id, $group, & $howOld, $lifetime, & $filename);
    
    abstract protected function implGet($id, $group, $default, $evenOld);
    
    abstract protected function implPut($id, $content, $group);

    abstract protected function implDelete($id, $group);
    
    abstract function deleteAll();
    
}
