<?php

class Ac_Js_Val implements Ac_I_Jsable {
    
    var $value = '';
    
    function __construct($value) {
        $this->value = $value;
    }
    
    function __toString() {
        $js = new Ac_Js();
        return $this->toJs($js);
    }
    
    function toJs(Ac_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        $res = $js->toJs($this->value, $indent, $indentStep, $newLines);
        return $res;
    }
    
}