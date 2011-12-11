<?php

class Ae_Param_Condition_Error {
    
    var $id = false;
    
    var $paramPath = false;
    
    var $prefix = false;
    
    var $translations = false;
    
    var $default = false;
    
    function getTranslations() {
        $res = $this->translations;
        $res['{param}'] = strlen($this->paramPath)? " '".$this->paramPath."'" : "";
        return $res;  
    }
    
    function __construct($id, $prefix = '', $default = null, $paramPath = '', array $translations = array()) {
        $this->id = $id;
        $this->suffix = $prefix;
        $this->default = $default;
        $this->paramPath = $paramPath;
        $this->translations = $translations;
    } 
    
    function __toString() {
        return ''.$this->getAeLangString();
    }
    
    /**
     * @return Ae_Lang_String
     */
    function getAeLangString() {
        $id = (strlen($this->prefix)? $this->prefix.'_' : '').$this->id;
        $res = new Ae_Lang_String($id, array(
            'default' => $this->default,
            'strtr' => $this->getTranslations(),
        ));
        return $res;
    }
    
}