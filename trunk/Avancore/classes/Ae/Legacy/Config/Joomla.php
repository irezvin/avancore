<?php

Ae_Dispatcher::loadClass('Ae_Legacy_Config');

class Ae_Legacy_Config_Joomla extends Ae_Legacy_Config {

    function Ae_Legacy_Config_Joomla($configFilePath, $configOptions = array()) {
        parent::Ae_Legacy_Config($configFilePath, $configOptions);
        
        $this->liveSite = $GLOBALS['mosConfig_live_site'];
        $this->absolutePath = $GLOBALS['mosConfig_absolute_path'];
        $this->debug = $GLOBALS['mosConfig_debug'];
        $this->mailFrom = $GLOBALS['mosConfig_mailfrom'];
        $this->fromName = $GLOBALS['mosConfig_fromname'];
        $this->siteName = $GLOBALS['mosConfig_sitename'];
        $this->mailer = $GLOBALS['mosConfig_mailer'];
        $this->smtpHost = $GLOBALS['mosConfig_smtphost'];
        $this->smtpPass = $GLOBALS['mosConfig_smtppass'];
        $this->smtpUser = $GLOBALS['mosConfig_smtpuser'];
        $this->smtpAuth = $GLOBALS['mosConfig_smtpauth'];
        $this->listLimit = $GLOBALS['mosConfig_list_limit'];
    }
    
    function getFrontendDir() {
        if ($this->frontendDirOverride !== false) {
        	$res = $this->frontendDirOverride;
        } else {
	    	$disp = & Ae_Dispatcher::getInstance();
	        $res = $this->absolutePath.'/components/'.$disp->getAppName();
        }
        return $res; 
    }
    
    function getBackendDir() {
        if ($this->backendDirOverride !== false) {
        	$res = $this->backendDirOverride;
        } else {
	    	$disp = & Ae_Dispatcher::getInstance();
	        $res = $this->absolutePath.'/administrator/components/'.$disp->getAppName(); 
        }
        return $res;
    }

    function getFrontendUrl() {
        return $this->liveSite.'/index.php';
    }
    
    function getBackendUrl() {
        return $this->liveSite.'/administrator/index2.php';
    }

    function getNative($paramName, $defaultValue = null) {
        $varName = 'mosConfig_'.$paramName;
        if (isset($GLOBALS[$varName])) $res = $GLOBALS[$varName];
            else $res = $defaultValue;
        return $res;
    }
    
}

?>