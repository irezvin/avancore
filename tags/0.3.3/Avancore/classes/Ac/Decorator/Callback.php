<?php

class Ac_Decorator_Callback extends Ac_Decorator {
    
    var $callback = false;
    
    var $args = false;
    
    var $valueArgIndex = 0;
    
    var $assignReturnValue = true;
    
    function apply($value) {
        if ($this->callback !== false) {
            if (is_array($this->args)) $args = $this->args;
                else $args = array();
            array_splice($args, $this->valueArgIndex, 0, array(& $value));
            $ret = call_user_func_array($this->callback, $args);
            if ($this->assignReturnValue) $value = $ret;
        }
        return $value;
    }
    
}