<?php

class Ac_Decorator_Text_MakeLink extends Ac_Decorator {
    
    var $attribs = array();
    
    var $placeholder = false;
    
    var $linkText = false;
    
    function apply($value) {
        if (!strlen($value)) $res = '';
        else {
	        $attribs = $this->attribs;
	        if (!isset($attribs['href']) || !strlen($attribs['href'])) $attribs['href'] = $value;
            $res = Ac_Util::mkElement('a', $this->linkText !== false? $this->linkText : $value, $attribs);
            if (strlen($this->placeholder)) $res = str_replace($this->placeholder, $value, $res);
        }
        return $res;
    }
    
}