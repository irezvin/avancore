<?php

class Ae_Legacy_Session_Joomla extends Ae_Legacy_Session {

    function Ae_Legacy_Session_Joomla() {
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
        trigger_error ('Ae_Legacy_Session_Joomla::start() not implemented', E_USER_ERROR);
    }
    
    function destroy() {
        trigger_error ('Ae_Legacy_Session_Joomla::destroy() not implemented', E_USER_ERROR);
    }
    
}

?>