<?php

class Ac_Request_Src {
    
    protected $varName = 'get';
    
    protected static $instances = array();

    /**
     * @return Ac_Request_Src 
     */
    static function factory($varName) {
        if (is_array($k = $varName)) $k = implode('/', $k);
        if (!isset(self::$instances[$k])) self::$instances[$k] = new Ac_Request_Src($varName);
        return self::$instances[$k];
    }
    
    static function get() {
        return self::factory('get');
    }
    
    static function post() {
        return self::factory('post');
    }
    
    static function cookie() {
        return self::factory('cookie');
    }
    
    static function request() {
        return self::factory('request');
    }
    
    static function env() {
        return self::factory('env');
    }
    
    static function server() {
        return self::factory('server');
    }
    
    static function gpc() {
        return self::factory(array('get', 'post', 'cookie'));
    }
        
    function __construct($varName) {
        $this->varName = $varName;
    }
    
    function getVarName() {
        return $this->varName;
    }
    
    function getValue(Ac_Request $request, $path, $defaultValue = null, & $found = false) {
        return $request->getValueFrom($this->varName, $path, $defaultValue, $found);
    }
    
}