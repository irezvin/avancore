<?php

class Ae_Config {
    /**
     * @access protected
     */
    var $_configFilePath = false;
    
    /**
     * @access protected
     */
    var $_configOptions = array();
    
    var $_configFileLoaded = false;
    
    var $liveSite;
    var $absolutePath;
    var $debug;
    var $mailFrom;
    var $fromName;
    var $siteName;
    var $mailer;
    var $smtpHost;
    var $smtpPass;
    var $smtpUser;
    var $smtpAuth;
    
    /**
     * @var '' | 'ssl' | 'tls'
     */
    var $smtpSecure = '';
    var $listLimit;
    var $cachePath;
    var $cachePrefix = 'ae';
    var $sendEmails = true;
    var $pageMapPath = false;
    var $backendDirOverride = false;
    var $frontendDirOverride = false;
    var $languagesDir = false;
    var $dbClass;
    var $assetPlaceholders = array();
    var $jsDir = false;
    var $managerImagesUrl = false;
    var $emailsSavePath = false;
    
    var $useDatabase = true;
    
    var $useConfigFile = true;

    var $configFileRequired = false;
    
    function Ae_Config($configFilePath, $configOptions = array()) {
        if (isset($configOptions['configFilePath']) && strlen($configOptions['configFilePath']))
            $configFilePath = $configOptions['configFilePath'];
        if (strtolower(get_class($this)) == 'ae_config') trigger_error ('Attempt to instantiate abstract class', E_USER_ERROR);
        $this->_configFilePath = $configFilePath;
        $this->_configOptions = $configOptions;
        Ae_Util::simpleBindAll($configOptions, $this);
        if ($this->_configFilePath) $configOptions = Ae_Util::m($cf = $this->_loadConfigFile($this->_configFilePath), $co = $configOptions);
        $this->_configOptions = $configOptions;
        Ae_Util::simpleBindAll($configOptions, $this);
    }
    
    /**
     * @access protected
     */
    function _loadConfigFile($path) {
        $config = array();
        if ($this->useConfigFile) {
	        if (file_exists($path)) {
	            require ($path);
	            if (!is_array($config)) $config = array();
	        } else {
	            if ($this->configFileRequired) trigger_error ("Config file not found: ".$path, E_USER_ERROR);
	        }
        }
        return $config;
    }
    
    function getConfigArray() {
        $res = $this->_configOptions;
        foreach (get_object_vars($this) as $p => $v) if ($p{0} != '_') $res[$p] = $v;
        return $res;
    }
    
    function getValue($paramName, $defaultValue = null) {
    	if (array_key_exists($paramName, $this->_configOptions)) $res = $this->_configOptions[$paramName];
    		else $res = $defaultValue;
    	return $res;
    }

    function getFrontendDir() {
        trigger_error ('Attempt to instantiate abstract class', E_USER_ERROR);
    }
    
    function getBackendDir() {
        trigger_error ('Attempt to instantiate abstract class', E_USER_ERROR);
    }
    
    function getFrontendUrl() {
        trigger_error ('Attempt to instantiate abstract class', E_USER_ERROR);
    }
    
    function getBackendUrl() {
        trigger_error ('Attempt to instantiate abstract class', E_USER_ERROR);
    }
    
    function getNative($paramName, $defaultValue = null) {
        return $defaultValue;
    }
    
}

?>