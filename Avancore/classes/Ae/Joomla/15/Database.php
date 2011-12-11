<?php

Ae_Dispatcher::loadClass('Ae_Joomla_Database');

class Ae_Joomla_15_Database extends Ae_Joomla_Database {
	
    /**
     * @var JDatabase
     */
    var $_db = false;
    
    function _doGetAccess() {
        
        // Create J15 config (not providing config file) if the class is created without Dispatcher
        if (!$this->_config && class_exists('JConfig', false)) {
            $this->_config = new Ae_Joomla_15_Config(false);
        }
        if ($this->_config) {
            $res = array(
                'user' => $this->_config->getNative('user'), 
                'password' => $this->_config->getNative('password'), 
                'host' => $this->_config->getNative('host'), 
                'db' => $this->_config->getNative('db'), 
                'prefix' => $this->_config->getNative('dbprefix'),
            );
        } else {
            $res = array();
        }
        return $res;
    }
    
    function _doInitialize($options) {
        if (!defined('_JEXEC') || !class_exists('JFactory')) 
            trigger_error ('No Joomla and/or JFactory found', E_USER_ERROR);
            
        $this->_db = &JFactory::getDBO();     
    }
	
}