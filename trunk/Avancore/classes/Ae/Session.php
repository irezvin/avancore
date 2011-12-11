<?php

class Ae_Session {

    function Ae_Session() {
        trigger_error ('Attempt to instantiate abstract class', E_USER_ERROR);
    }
    
    function get($varName, $default = false) {
        trigger_error ('Call to abstract method', E_USER_ERROR);
    }
    
    function set($varName, $value) {
        trigger_error ('Call to abstract method', E_USER_ERROR);
    }
    
    function start() {
        trigger_error ('Call to abstract function', E_USER_ERROR);
    }
    
    function destroy() {
        trigger_error ('Call to abstract function', E_USER_ERROR);
    }
    
}

?>