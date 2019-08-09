<?php

class Ac_Decorator_Debug_ObjectToClass extends Ac_Decorator {
    
    function apply($value) {
        if (is_object($value)) $value = Ac_Util::typeClass ($value);
        return $value;
    }
    
}