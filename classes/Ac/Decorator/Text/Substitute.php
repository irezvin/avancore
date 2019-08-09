<?php

class Ac_Decorator_Text_Substitute extends Ac_Decorator {
    
    var $placeholder = '{{value}}';
    
    var $template = '{{value}}';
    
    var $textIfEmptyValue = false;
    
    function apply($value) {
        if (!strlen($value) && $this->textIfEmptyValue !== false) $res = $this->textIfEmptyValue;
        else {
            $res = str_replace($this->placeholder, $value, $this->template);
        }
        return $res;
    }
    
}