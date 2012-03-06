<?php

/**
 * System Adapter class for Avancore Lite Framework
 */
class Ae_Legacy_Adapter {

    /**
     * @var Ae_Legacy_Session
     */
    var $session = false;
    
    /**
     * @var Ae_Legacy_Database
     */
    var $database = false;
    
    /**
     * @var Ae_Userstate
     */
    var $userstate = false;
    
    /**
     * @var Ae_Legacy_Config
     */
    var $config = false;
    
    var $pageNavClass = false;
    
    var $sessionClass = false;
    
    var $configClass = 'Ae_Legacy_Config';
    
    /**
     * @var array
     * @access protected
     */
    var $_extraSettings = array();
    
    /**
     * Path where config file is taken from
     * %a% is replaced with application directory (Ae_Dispatcher::getAppDir())
     * If set to FALSE, config file won't be loaded 
     */
    var $configPath = '%a%/app.config.php';
    
    /**
     * Extra (or all) configuration options (usually they are taken from the config file)
     */
    var $configOptions = array();     
    
    var $dbSettings = false;
    
    var $dbClass = false;
    
    function Ae_Legacy_Adapter($extraSettings = array()) {
        if (strtolower(get_class($this)) === 'ae_adapter') trigger_error ('Attempt to instantiate abstract class', E_USER_ERROR);
        $this->_processExtraSettings($extraSettings);
        $this->config = & $this->_instantiateConfig($this->configClass);  
    }
    
    
    /**
     * @access protected
     */
    function _processExtraSettings($extraSettings) {
        $this->_extraSettings = $extraSettings;
        foreach ($this->_listPassthroughExtraSettings() as $xs) {
            if (isset($extraSettings[$xs])) $this->$xs = $extraSettings[$xs];
        }
    }
    
    /**
     * @access protected
     */
    function _listPassthroughExtraSettings() {
        return array('configPath', 'configOptions');
    }
    
    /**
     * @access protected
     */
    function & _instantiateConfig ($configClass) {
        if (strlen($this->configPath)) 
            $configPath = str_replace('%a%', Ae_Dispatcher::getAppDir(), $this->configPath);
        else $configPath = false;
        $res = new $configClass ($configPath, $this->configOptions);
        return $res;
    }
    
    function & createPageNav($total, $limitstart, $limit) {
        $pnc = $this->pageNavClass;
        $res = new $pnc ($total, $limitstart, $limit);
        return $res;
    }
    
    /**
     * @return Ae_Legacy_User
     */
    function getUser() {
        trigger_error ('Call to abstract method', E_USER_ERROR);
    }
    
    function getSession() {
        if ($this->_session === false) {
            if ($this->sessionClass === false) trigger_error ('$sessionClass is not specified in conrete Ae_Legacy_Adapter instance', E_USER_ERROR);          
            $sc = $this->sessionClass;
            $this->_session = new $sc();
        }
        return $this->_session;
    }
    
    /**
     * Replaces placeholders from $this->config->assetPlaceholders in jsOrCssLib 
     * 
     * @param string $jsOrCssLib
     * @return string
     */
    function unfoldAssetString($jsOrCssLib) {
        $i = 0;
        for ($i = 0; $i < 10; $i++) {
            $new = strtr($jsOrCssLib, $this->config->assetPlaceholders);
            if ($new == $jsOrCssLib) break;
            $jsOrCssLib = $new;
        }
        return $jsOrCssLib;
    }
    
    function getJsUrlStr ($jsLib, $isLocal) {
        $jsLib = $this->unfoldAssetString($jsLib);
        if (!strncasecmp('http://', $jsLib, 7) && !strncasecmp('https://', $jsLib, 8)) {
            $prefix = $this->config->jsDir !== false? $this->config->jsDir : $this->config->liveSite.'/js/';
        } else $prefix = '';
        return $prefix.$jsLib;
    }
    
    function getCssUrlStr ($cssLib, $isLocal) {
        $cssLib = $this->unfoldAssetString($cssLib);
        if (!strncasecmp('http://', $cssLib, 7) && !strncasecmp('https://', $cssLib, 8)) {
            $prefix = $this->config->liveSite.'/';
        } else $prefix = '';
        return $prefix.$cssLib;
    }
    
}


?>