<?php

class Ac_Legacy_Adapter_Native extends Ac_Legacy_Adapter {

    var $_user = false;
    
    var $_session = false;
    
    var $pageNavClass = 'Ac_Native_Pagenav';
    
    var $sessionClass = 'Ac_Legacy_Session_Native';
    
    var $configClass = 'Ac_Legacy_Config_Native';
    
    function Ac_Legacy_Adapter_Native($extraSettings = array()) {
        
        parent::Ac_Legacy_Adapter($extraSettings);
        
        if ($this->config->useDatabase) {
            
    		$dbSettings = array(
                'db' => $this->config->db,
                'host' => $this->config->host,
                'user' => $this->config->user,
    		'port' => isset($this->config->port)? $this->config->port : false,
                'password' => $this->config->password,
                'prefix' => $this->config->prefix,
                'charset' => $this->config->charset,
                'config' => & $this->config,
    		);
    		
    		if (is_array($this->config->dbSettings)) Ac_Util::ms($dbSettings, $this->config->dbSettings);
    		$dbClass = 'Ac_Legacy_Database_Native';
    		if (strlen($this->config->dbClass)) $dbClass = $this->config->dbClass; 
            
    		$this->database = new $dbClass($dbSettings);
        } else {
            $this->database = null;
        }
		
        $this->_user = new Ac_Legacy_User_Native();
        
    }
    
    function getUser() {
        $res = $this->_user;
        return $res;
    }
    
}

