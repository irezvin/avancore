<?php

class Ac_Legacy_Config_Joomla15 extends Ac_Legacy_Config_Joomla {

	var $_jConfig = false;
	
    function __construct($configFilePath, $configOptions = array()) {
    	
    	$this->_jConfig = new JConfig();
    	$jUri = JURI::getInstance();
    	
        parent::Ac_Legacy_Config($configFilePath, $configOptions);
        
        $this->liveSite = rtrim($jUri->root(), '/');
        $this->absolutePath = JPATH_SITE;
        $this->debug = $this->_jConfig->debug;
        $this->mailFrom = $this->_jConfig->mailfrom;
        $this->fromName = $this->_jConfig->fromname;
        $this->siteName = $this->_jConfig->sitename;
        $this->mailer = $this->_jConfig->mailer;
        $this->smtpHost = $this->_jConfig->smtphost;
        $this->smtpPass = $this->_jConfig->smtppass;
        $this->smtpUser = $this->_jConfig->smtpuser;
        $this->smtpAuth = $this->_jConfig->smtpauth;
        $this->listLimit = $this->_jConfig->list_limit;
    }

    function getNative($paramName, $defaultValue = null) {
        if(isset($this->_jConfig->$paramName)) $res = $this->_jConfig->$paramName;
        	else $res = $defaultValue;
        	
        return $res;
    }
	
}