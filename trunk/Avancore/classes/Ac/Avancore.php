<?php

if (!class_exists('Ac_Dispatcher', false)) {
    require_once(dirname(__FILE__).'/Dispatcher.php');
    Ac_Dispatcher::addIncludePath();
    Ac_Dispatcher::registerAutoload();
}

class Ac_Avancore extends Ac_Application {
    
    const version = '0.3.0';
    
    protected $defaultAssetsPlaceholder = '{AC}';
    
    function getAppClassFile() {
        return __FILE__;
    }
    
    function getVersion() {
        return self::$version;
    }
    
    /**
     * @return Ac_Avancore
     */
    static function getInstance($id = null) {
        return Ac_Application::getApplicationInstance('Ac_Avancore', $id);
    }
    
    protected function doOnInitialize() {
        parent::doOnInitialize();
    }
    
    function getDefaultAssetPlaceholders() {
        $res = parent::getDefaultAssetPlaceholders();
        Ac_Util::ms($res, array(
            '{JQUERY}' => '{AC}/assets/vendor/jquery.min.js'
        ));
        return $res;
    }
    
    
}