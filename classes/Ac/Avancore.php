<?php

if (!class_exists('Ac_Util', false)) {
    require_once(dirname(__FILE__).'/Util.php');
    Ac_Util::addIncludePath();
    Ac_Util::registerAutoload();
}

class Ac_Avancore extends Ac_Application {
    
    const version = '0.4.1-dev';
    
    protected $defaultAssetsPlaceholder = '{AC}';
    
    function getAppClassFile() {
        return __FILE__;
    }
    
    function getVersion() {
        return self::$version;
    }
 
    protected function doOnInitialize() {
        parent::doOnInitialize();
        if ($this->addIncludePaths) {
            Ac_Util::addIncludePath(dirname(dirname(dirname(__FILE__))).'/obsolete');
        }
    }
    
    /**
     * @return Ac_Avancore
     */
    static function getInstance($id = null) {
        return Ac_Application::getApplicationInstance('Ac_Avancore', $id);
    }
    
    function getDefaultAssetPlaceholders() {
        $res = parent::getDefaultAssetPlaceholders();
        Ac_Util::ms($res, array(
            '{JQUERY}' => '{AC}/vendor/jquery.min.js'
        ));
        return $res;
    }
    
    
}
