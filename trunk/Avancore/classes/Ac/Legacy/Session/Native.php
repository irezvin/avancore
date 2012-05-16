<?php

class Ac_Legacy_Session_Native extends Ac_Legacy_Session {
    
    function Ac_Legacy_Session_Native() {
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