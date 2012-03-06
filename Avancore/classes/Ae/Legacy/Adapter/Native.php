<?php

class Ae_Legacy_Adapter_Native extends Ae_Legacy_Adapter {

    var $_user = false;
    
    var $_session = false;
    
    var $pageNavClass = 'Ae_Native_Pagenav';
    
    var $sessionClass = 'Ae_Legacy_Session_Native';
    
    var $configClass = 'Ae_Legacy_Config_Native';
    
    function Ae_Legacy_Adapter_Native($extraSettings = array()) {
        
        parent::Ae_Legacy_Adapter($extraSettings);
        
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
    		
    		if (is_array($this->config->dbSettings)) Ae_Util::ms($dbSettings, $this->config->dbSettings);
    		$dbClass = 'Ae_Legacy_Database_Native';
    		if (strlen($this->config->dbClass)) $dbClass = $this->config->dbClass; 
            
    		$this->database = new $dbClass($dbSettings);
        } else {
            $this->database = null;
        }
		
        $this->_user = new Ae_Legacy_User_Native();
        
    }
    
    function getUser() {
        $res = & $this->_user;
        return $res;
    }
    
}

?>