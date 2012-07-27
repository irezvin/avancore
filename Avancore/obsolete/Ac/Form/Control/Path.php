<?php

class Ac_Form_Control_Path {
    
    protected $path = false;
    
    function __construct($path) {
        $this->path = $path;
    }
    
    function getPath() {
        return $this->path;
    }
    
    /**
     * @return Ac_Form_Control
     */
    function getControl(Ac_Form_Control $relativeTo, $require = false) {
        $res = $relativeTo->searchControlByPath($this->path);
        if (!$res && $require) {
            throw new Exception("Cannot find control by path '{$this->path}' (relative to control '".$relativeTo->getPath()."')");
        }
        return $res;
    }
    
}