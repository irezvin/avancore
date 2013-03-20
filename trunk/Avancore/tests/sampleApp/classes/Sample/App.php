<?php

class Sample_App extends Ac_Application {
    
    const version = '0.3.0';
    
    function getVersion() {
        return self::$version;
    }
    
    function getAppClassFile() {
        return dirname(__FILE__);
    }
    
    /**
     * @return Sample_App
     */
    static function getInstance($id = null) {
        return Ac_Application::getApplicationInstance('Sample_App', $id);
    }
    
}