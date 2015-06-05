<?php

class Ac_Decorator_Text_Hsc extends Ac_Decorator {
    
    var $nl2br = false;
    var $htmlspecialchars = true;
    var $hscFlags = ENT_QUOTES;
    var $doubleEncode = false;
    var $encoding = "UTF-8";
    
    function apply ($value) {
        if ($this->htmlspecialchars) $value = htmlspecialchars ($value, $this->hscFlags, $this->encoding, $this->doubleEncode);
        if ($this->nl2br) $value = nl2br($value);
        return $value;
    }
    
}