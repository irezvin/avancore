<?php

class Ac_Decorator_Print extends Ac_Decorator {
    
    var $extra = "";
    
    var $die = false;
    
    function apply($value) {
        if ($this->die) Ac_Debug::savageMode();
        if (strlen($this->extra)) echo ($this->extra);
        var_dump($value);
        if ($this->die) die();
        return $value;
    }
    
}