<?php

class Ae_Debug_Log {

    private static $instance = null;
    
    const CALLBACK_LOG_WRITE = 'CALLBACK_LOG_WRITE';
    
    /**
     * @return Ae_Debug_Log
     */
    static final function getInstance() {
        if (!self::$instance) self::$instance = new Ae_Debug_Log();
        return self::$instance;
    }
    
    /**
     * Sets new instance; returns old one
     * 
     * @return Ae_Debug_Log
     */
    static final function setInstance(Ae_Debug_Log $instance) {
        $res = self::$instance;
        self::$instance = $instance;
        return $res;
    }
    
    static final function l ($_) {
        $args = func_get_args();
        $i = Ae_Debug_Log::getInstance();
        return call_user_func_array(array($i, 'log'), $args);
    }
    
    /**
     * Alias for Ae_Debug_Log::getInstance()
     * @return Ae_Debug_Log
     */
    static final function i () {
        return self::getInstance();
    }
    
    function log($_) {
        $args = func_get_args();
        if (class_exists('Ae_Callbacks', false))
            Ae_Callbacks::call(self::CALLBACK_LOG_WRITE, $this, $args);
    }
    
    function getTrace($asArray = false, $skip = true) {
        if ($skip === true) $skip = array('Ae_Debug_Log');
        $bt = debug_backtrace();
        if ($skip) {
            $classes = Ae_Util::toArray($skip);
            $i = 0;
            while (isset($bt[$i]['class']) && in_array($bt[$i]['class'], $skip)) $i++;
            $bt = array_slice($bt, $i);
        }
        $c = count($bt);
        $s = array();
        foreach ($bt as $i => $arr) {
            $string = array();
            $string[] = sprintf("%4d.", $c - $i);
            if (isset($arr['function'])) {
                $fn = $arr['function'];
                if (isset($arr['class'])) {
                    $type = isset($arr['type'])? $arr['type'] : '::';
                    $fn = $arr['class'].$type.$fn;
                }
                $string[] = $fn;
            }
            if (isset($arr['file'])) $string[] = 'in '.$arr['file'];
            if (isset($arr['line'])) $string[] = '# '.$arr['line'];
            $s[] = $asArray? $string : implode(" ", $string);
        }
        $res = $asArray? $s : implode("\n", $s);
        return $res;
    }
    
    function trace() {
        $this->log($this->getTrace(false));
    }
    
    
}