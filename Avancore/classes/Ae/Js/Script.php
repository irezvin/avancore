<?php

class Ae_Js_Script extends Ae_Js_Code {

    var $src = false;

    function __toString() {
        $a = array('type' => 'text/javascript');
        if (strlen($this->src)) $a['src'] = $this->src;
        $res = Ae_Util::mkElement('script', parent::toJs(new Ae_Js()), $a);
        return $res;
    }

    function toJs(Ae_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        $res =
            "function() {\n"
            .parent::toJs($js, $indent, $indentStep, $newLines)
            ."\n}";
        return $res;
    }

}