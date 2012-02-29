<?php

Ae_Dispatcher::loadClass ('Ae_Legacy_Adapter');

class Ae_Joomla_Adapter extends Ae_Legacy_Adapter {

    var $_user = false;
    
    var $_session = false;
    
    var $useNativeDatabase = false;
    
    var $pageNavClass = 'Ae_Joomla_Pagenav';
    
    var $sessionClass = 'Ae_Legacy_Session_Joomla';
    
    var $configClass = 'Ae_Legacy_Config_Joomla';
    
    function Ae_Joomla_Adapter($extraSettings = array()) {
        
        parent::Ae_Legacy_Adapter($extraSettings);
        
        $dbSettings = $this->dbSettings === false? array(
                'db' => $GLOBALS['mosConfig_db'],
                'host' => $GLOBALS['mosConfig_host'],
                'user' => $GLOBALS['mosConfig_user'],
                'password' => $GLOBALS['mosConfig_password'],
                'prefix' => $GLOBALS['mosConfig_dbprefix'],
        ) : $this->dbSettings;
        $dbSettings['config'] = & $this->config;
            
        if ($this->useNativeDatabase) {
            Ae_Dispatcher::loadClass('Ae_Legacy_Database_Native');
            $this->database = new Ae_Legacy_Database_Native(
            	$dbSettings
            );
        } else {
        	if ($this->dbClass) {
        		Ae_Dispatcher::loadClass($this->dbClass);
        		$dbc = $this->dbClass;
        		$this->database = new $dbc($dbSettings); 
        	} else {
            	Ae_Dispatcher::loadClass('Ae_Legacy_Database_Joomla');
            	$this->database = new Ae_Legacy_Database_Joomla(array('config' => & $this->config));
        	}
        }
        
        Ae_Dispatcher::loadClass('Ae_Legacy_User_Joomla');
        $josUser = null;
        if (isset($GLOBALS['my']) && is_object($GLOBALS['my'])) $josUser = & $GLOBALS['my'];
        $this->_user = new Ae_Legacy_User_Joomla($josUser);
        
    }
    
    function _listPassthroughExtraSettings() {
        return array_merge(parent::_listPassthroughExtraSettings(), array('dbClass', 'dbSettings', 'useNativeDatabase'));
    }
    
    function getUser() {
        $res = & $this->_user;
        return $res;
    }
    
    
    function getJsUrlStr ($jsLib, $isLocal) {
        $jsLib = $this->unfoldAssetString($jsLib);
        if (strncasecmp('http://', $jsLib, 7) && strncasecmp('https://', $jsLib, 8)) {
            if ($this->config->jsDir !== false) {
                $prefix = $this->config->jsDir;
            } else {
                $disp = & Ae_Dispatcher::getInstance();
                $prefix = $disp->isBackend()? '/administrator/components/'.$disp->getAppName() : '/components/'.$disp->getAppName();
                $prefix .= '/js/';
                $prefix = $this->config->liveSite.$prefix;
            }
        } else $prefix = '';
        return $prefix.$jsLib;
    }
    
    function getCssUrlStr ($cssLib, $isLocal) {
        $cssLib = $this->unfoldAssetString($cssLib);
        if (strncasecmp('http://', $cssLib, 7) && strncasecmp('https://', $cssLib, 8)) {
            $disp = & Ae_Dispatcher::getInstance();
            $prefix = $disp->isBackend()? '/administrator/components/'.$disp->getAppName() : '/components/'.$disp->getAppName();
            $prefix .= '/';
            $prefix = $this->config->liveSite.$prefix;
        } else $prefix = '';
        return $prefix.$cssLib;
    }
    
}

?>