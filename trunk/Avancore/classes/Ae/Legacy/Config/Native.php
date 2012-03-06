<?php

class Ae_Legacy_Config_Native extends Ae_Legacy_Config {

    var $db = false;
    var $user = false;
    var $password = false;
    var $host = false;
    var $encoding = false;
    var $timezone = false;
    var $prefix = false;
    var $charset = false;
    
    var $dbClass = 'Ae_Legacy_Database_Native';
    var $dbSettings = array();
    
    function Ae_Legacy_Config_Native($configFilePath, $configOptions = array()) {
        parent::Ae_Legacy_Config($configFilePath, $configOptions);
    }
    
    function getFrontendDir() {
        if ($this->frontendDirOverride !== false) return $this->frontendDirOverride; 
    	return $this->absolutePath; 
    }
    
    function getBackendDir() {
    	if ($this->backendDirOverride !== false) return $this->backendDirOverride;
        return $this->absolutePath; 
    }

    function getFrontendUrl() {
        return $this->liveSite.'/index.php';
    }
    
    function getBackendUrl() {
        return $this->liveSite.'/index.php';
    }

    function getNative($paramName, $defaultValue = null) {
        $varName = 'mosConfig_'.$paramName;
        if (isset($GLOBALS[$varName])) $res = $GLOBALS[$varName];
            else $res = $defaultValue;
        return $res;
    }
    
}

?>