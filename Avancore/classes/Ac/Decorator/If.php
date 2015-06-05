<?php

class Ac_Decorator_If extends Ac_Decorator {
    
    var $ifDec = false;
    
    var $if = 0;
    
    var $then = array('class' => 'Ac_Decorator_Const');
    
    var $else = false;
    
    var $strict = false;
    
    var $callback = false;
    
    function apply($value) {
        $match = false;
        $compare = $value;
        
        if ($this->ifDec) $compare = Ac_Decorator::decorate ($this->ifDec, $compare, $this->ifDec);
        
        if ($this->callback) $match = call_user_func($this->callback, $compare);
        elseif (is_array($this->if)) $match = in_array($compare, $this->if, $this->strict);
        else $match = $this->strict? $compare === $this->if : $compare == $this->if;
        
        if ($match) {
            if ($this->then) $value = Ac_Decorator::decorate ($this->then, $value, $this->then);
        } else {
            if ($this->else) $value = Ac_Decorator::decorate ($this->else, $value, $this->else);
        }
        
        return $value;
        
    }
    
}