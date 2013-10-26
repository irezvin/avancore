<?php

class Ac_Decorator_VarDump extends Ac_Decorator {
    
    var $base64decode = false;
    
    var $unserialize = false;
    
    var $dump = true;
    
    function apply($value) {
        if ($this->base64decode) {
            $value = base64_decode($value);
        }
        if ($this->unserialize) {
            $value = unserialize($value);
        }
        if ($this->dump) {
            if(extension_loaded('xdebug')) {
                ob_start();
                var_dump($value);
                return ob_get_clean();
            }
            else {
                return ini_get('html_errors')? nl2br(str_replace(' ', '&nbsp;', htmlspecialchars(print_r($value, true)))) : print_r($value, true);
            }
        }
    }
    
}