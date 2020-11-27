<?php

class Ac_Js_Script extends Ac_Js_Code {

    var $src = false;

    function __toString() {
        $a = array('type' => 'text/javascript');
        if (strlen($this->src)) $a['src'] = $this->src;
        $res = Ac_Util::mkElement('script', "//<![CDATA[\n".$this->toRawCode()."\n//]]>", $a);
        return $res;
    }
    
    function toRawCode() {
        return parent::toJs(new Ac_Js());
    }

    function toJs(Ac_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        $res =
            "function() {\n"
            .$this->toRawCode()
            ."\n}";
        return $res;
    }

}