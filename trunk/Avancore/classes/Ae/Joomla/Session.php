<?php

Ae_Dispatcher::loadClass('Ae_Session');

class Ae_Joomla_Session extends Ae_Session {

    function Ae_Joomla_Session() {
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
        trigger_error ('Ae_Joomla_Session::start() not implemented', E_USER_ERROR);
    }
    
    function destroy() {
        trigger_error ('Ae_Joomla_Session::destroy() not implemented', E_USER_ERROR);
    }
    
}

?>