<?php

class Ac_Js_New extends Ac_Js_Call {
    
    function __construct($constructorId, $_ = null) {
        $args = func_get_args();
        $cArgs = array_slice($args, 1);
        parent::__construct($constructorId, $cArgs, true); 
    }
    
}