<?php

class Ac_Decorator_Text_MakeLink extends Ac_Decorator {
    
    var $attribs = array();
    
    function apply($value) {
        if (!strlen($value)) $res = '';
        else {
	        $attribs = $this->attribs;
	        if (!isset($attribs['href']) || !strlen($attribs['href'])) $attribs['href'] = $value;
            $res = Ac_Util::mkElement('a', $value, $attribs);
        }
        return $res;
    }
    
}