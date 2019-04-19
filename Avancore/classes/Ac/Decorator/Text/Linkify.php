<?php

class Ac_Decorator_Text_Linkify extends Ac_Decorator {
    
    var $attribs = array();
    
    function apply($value) {
        if (!is_string($value)) return;
        $value = str_replace('&quot;', '"{{tmp}}"', $value);
        preg_match_all('#https?://[^\'"\\s]+(&quot;)?#ui', $value, $matches);
        $tr = array();
        foreach ($matches[0] as $match) {
            if (substr($match, -3) === '...') continue;
            $attr = $this->attribs;
            $attr['href'] = $match;
            $tr[$match] = Ac_Util::mkElement('a', $match, $attr);
        };
        $value = strtr($value, $tr);
        $value = str_replace('"{{tmp}}"', '&quot;', $value);
        return $value;
    }
    
}