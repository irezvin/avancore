<?php

if (!class_exists('Ae_Dispatcher', false)) {
    require_once(dirname(__FILE__).'/Dispatcher.php');
    Ae_Dispatcher::addIncludePath();
    Ae_Dispatcher::registerAutoload();
}

class Ae_Avancore extends Ae_Application {
    
    const version = '0.3.0';
    
    protected $defaultAssetsPlaceholder = '{AE}';
    
    function getAppClassFile() {
        return __FILE__;
    }
    
    function getVersion() {
        return self::$version;
    }
    
    static function getInstance($id = null) {
        return Ae_Application::getInstance('Ae_Avancore', $id);
    }
    
    protected function doOnInitialize() {
        parent::doOnInitialize();
    }
    
}