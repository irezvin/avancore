<?php

class Ac_Debug_Log {

    private static $instance = null;
    
    const CALLBACK_LOG_WRITE = 'CALLBACK_LOG_WRITE';
    
    /**
     * @return Ac_Debug_Log
     */
    static final function getInstance() {
        if (!self::$instance) self::$instance = new Ac_Debug_Log();
        return self::$instance;
    }
    
    /**
     * Sets new instance; returns old one
     * 
     * @return Ac_Debug_Log
     */
    static final function setInstance(Ac_Debug_Log $instance) {
        $res = self::$instance;
        self::$instance = $instance;
        return $res;
    }
    
    static final function l ($_) {
        $args = func_get_args();
        $i = Ac_Debug_Log::getInstance();
        return call_user_func_array(array($i, 'log'), $args);
    }
    
    /**
     * Alias for Ac_Debug_Log::getInstance()
     * @return Ac_Debug_Log
     */
    static final function i () {
        return self::getInstance();
    }
    
    function log($_) {
        $args = func_get_args();
        if (class_exists('Ac_Callbacks', false))
            Ac_Callbacks::call(self::CALLBACK_LOG_WRITE, $this, $args);
    }
    
    function getTrace($asArray = false, $skip = true) {
        return Ac_Debug::getTrace($asArray, $skip === true? array('Ac_Debug', 'Ac_Debug_Log') : $skip);
    }
    
    function trace() {
        $this->log($this->getTrace(false));
    }
    
    
}