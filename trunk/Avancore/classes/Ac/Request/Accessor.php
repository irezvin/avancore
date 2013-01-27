<?php

class Ac_Request_Accessor implements Ac_I_Accessor, Ac_I_Param_Source {
    
    protected $src = false;
    
    /**
     * @var Ac_Request
     */
    protected $request = false;
    
    protected $uncamelize = false;
    
    /**
     * @param Ac_Request $request Request to take values from
     * @param Ac_Request_Src|string|array $src Source in Request (Src object, string or cascade)
     * @param bool $uncamelize  Convert httpHost to HTTP_HOST
     */
    function __construct(Ac_Request $request, $src, $uncamelize = false) {
        $this->request = $request;
        $this->src = $src;
        $this->uncamelize = $uncamelize;
    }
    
    protected static function uncamelize($path) {
        return strtoupper(preg_replace('/([a-z])([A-Z])/', '\1_\2', $path));
    }
    
    function get($path, $default = false, & $found = null) {
        if ($this->uncamelize) $path = self::uncamelize($path);
        if ($this->src !== false) {
            $res = $this->request->getValueFrom($this->src, $path, $default, $found);
        }   else $res = $this->request->getValue($path, $default, $found);
        return $res;
    }
    
    function set($path, $value) {
        if ($this->uncamelize) $path = self::uncamelize($path);
        if (Ac_Request::isCascade($this->src)) throw new Ac_E_InvalidUsage("Cannot set() request variable in cascade path");
        $this->request->setValueByPath($this->src, $path, $value);
    }
    
    function delete($path) {
        if ($this->uncamelize) $path = self::uncamelize($path);
        if (Ac_Request::isCascade($this->src)) throw new Ac_E_InvalidUsage("Cannot delete() request variable in cascade path");
        $this->request->unsetValueByPath($this->src, $path);
    }
    
    function __get($varName) {
        return $this->get($varName);
    }
    
    function __set($varName, $value) {
        $this->set($varName, $value);
    }
    
    function __unset($varName) {
        $this->delete($varName);
    }
    
    function __isset($varName) {
        $r = $this->get($varName, null, $found);
        return $found && !is_null($r);
    }
     
    function getProperty($name) {
        return $this->get($name);
    }
    
    function hasProperty($name) {
        return $this->__isset($name);
    }
    
    function setProperty($name, $value) {
        return $this->set($name, $value);
    }
    
    function listProperties() {
        return false;
    }

    // --- Ac_I_Param_Source implementation
    function hasParamValue(array $path) {
        $this->get($path, null, $found);
        return $found;
    }
    
    function getParamValue(array $path, $default = null, & $found = null) {
        return $this->get($path, $default, $found);
    }
    
   
}