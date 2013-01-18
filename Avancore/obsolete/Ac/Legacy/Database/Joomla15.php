<?php

class Ac_Legacy_Database_Joomla15 extends Ac_Legacy_Database_Joomla {
	
    /**
     * @var JDatabase
     */
    var $_db = false;
    
    var $useMysqli = false;
    
    function _doGetAccess() {
        
        // Create J15 config (not providing config file) if the class is created without Dispatcher
        if (!$this->_config && class_exists('JConfig', false)) {
            $this->_config = new Ac_Legacy_Config_Joomla15(false);
        }
        $res = array();
        if (class_exists('JConfig')) {
            $jc = new JConfig;
            $res['user'] = $jc->user;
            $res['password'] = $jc->password;
            $res['db'] = $jc->db;
            $res['host'] = $jc->host;
            $res['prefix'] = $jc->dbprefix;
        }
        return $res;
    }
    
    function _doInitialize(array $options = array()) {
        if (!class_exists('JFactory')) 
            trigger_error ('No JFactory found', E_USER_ERROR);
            
        $this->_db = JFactory::getDBO();
        $this->useMysqli = $this->_db instanceof JDatabaseDriverMysqli;
    }
    
    function fetchAssoc($resultResource) {
        if ($this->useMysqli) return mysqli_fetch_assoc($resultResource);
        return mysql_fetch_assoc($resultResource);
    }
    
    function fetchObject($resultResource, $className = null) {
        if ($this->useMysqli) return $className? mysqli_fetch_object($resultResource, $className) : mysqli_fetch_object($resultResource);
        return $className? mysql_fetch_object($resultResource, $className) : mysql_fetch_object($resultResource);
    }
    
    function freeResultResource($resultResource) {
        if ($this->useMysqli) return mysqli_free_result($resultResource); 
        else return mysql_free_result($resultResource);
    }
    
	
}