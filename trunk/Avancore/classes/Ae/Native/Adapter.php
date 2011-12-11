<?php

Ae_Dispatcher::loadClass ('Ae_Adapter');

class Ae_Native_Adapter extends Ae_Adapter {

    var $_user = false;
    
    var $_session = false;
    
    var $pageNavClass = 'Ae_Native_Pagenav';
    
    var $sessionClass = 'Ae_Native_Session';
    
    var $configClass = 'Ae_Native_Config';
    
    function Ae_Native_Adapter($extraSettings = array()) {
        
        parent::Ae_Adapter($extraSettings);
        
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
    		$dbClass = 'Ae_Native_Database';
    		if (strlen($this->config->dbClass)) $dbClass = $this->config->dbClass; 
            
            Ae_Dispatcher::loadClass($dbClass);
    		$this->database = new $dbClass($dbSettings);
        } else {
            $this->database = null;
        }
		
        Ae_Dispatcher::loadClass('Ae_Native_User');
        $this->_user = new Ae_Native_User();
        
    }
    
    function getUser() {
        $res = & $this->_user;
        return $res;
    }
    
}

?>