<?php

class Ac_Response_Http extends Ac_Response {

    function __construct() {
        parent::__construct();
        $this->mergeRegistry(
            array(
                'headers' => new Ac_Registry(),
                'session' => new Ac_Registry(),
                'cookie' => new Ac_Registry(),
            )
        );
    }
    
    function setHeaders($headers, $headerName = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('headers'));
        return call_user_func_array(array($this, 'setRegistry'), $args);
    }

    function getHeaders($headers, $headerName = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('headers'));
        return call_user_func_array(array($this, 'getRegistry'), $args);
    }
    
    function setSession($session, $varName = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('session'));
        return call_user_func_array(array($this, 'setRegistry'), $args);
    }

    function getSession($session, $headerName = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('session'));
        return call_user_func_array(array($this, 'getRegistry'), $args);
    }
    
    function setCookie($cookie, $varName = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('cookie'));
        return call_user_func_array(array($this, 'setRegistry'), $args);
    }

    function getCookie($cookie, $headerName = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('cookie'));
        return call_user_func_array(array($this, 'getRegistry'), $args);
    }
    
}