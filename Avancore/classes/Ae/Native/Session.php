<?php

Ae_Dispatcher::loadClass('Ae_Session');

class Ae_Native_Session extends Ae_Session {
    
    function Ae_Native_Session() {
        $this->start();  
    }
    
    function get($varName, $default = false) {
        if (isset($_SESSION) && isset($_SESSION[$varName])) $res = $_SESSION[$varName];
            else $res = $default;
        return $res;
    }
    
    function set($varName, $value) {
        if (!isset($_SESSION)) $this->start();
        $_SESSION[$varName] = $value;
    }
    
    function start() {
        session_start();
    }
    
    function destroy() {
        session_destroy();
    }
    
}

?>