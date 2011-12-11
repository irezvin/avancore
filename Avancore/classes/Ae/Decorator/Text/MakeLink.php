<?php

class Ae_Decorator_Text_MakeLink extends Ae_Decorator {
    
    var $attribs = array();
    
    function apply($value) {
        if (!strlen($value)) $res = '';
        else {
	        $attribs = $this->attribs;
	        if (!isset($attribs['href']) || !strlen($attribs['href'])) $attribs['href'] = $value;
            $res = Ae_Util::mkElement('a', $value, $attribs);
        }
        return $res;
    }
    
}