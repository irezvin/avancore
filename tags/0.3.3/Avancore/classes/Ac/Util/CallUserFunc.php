<?php

class Ac_Util_CallUserFunc {
    
    var $call = array();
    
    var $args = array();
    
    /**
     * @param callable $call
     * @param $_ Any extra arguments
     */
    function __construct($call) {
        $a = func_get_args();
        $this->call = $call;
        $this->args = array_slice($a, 1);
    }
    
    function __toString() {
        return (string) call_user_func_array($this->call, $this->args);
    }
    
}