<?php

class Ac_Decorator_Text_FixJson extends Ac_Decorator {
    
    var $unescapeUnicode = false;
    
    function apply($value) {
        if ($this->unescapeUnicode) $value = preg_replace('/\\\u(\w\w\w\w)/', array($this, 'unescapeCallback'), $value);
        return $value;
    }
    
    protected function unescapeCallback($matches) {
        return '&#'.hexdec($matches[1]).';';
    }
    
}