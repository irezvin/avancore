<?php

class Ae_Js_Hash implements Ae_I_Jsable {
    
    var $value = array();
    
    function __construct(array $value) {
        $this->value = $value;
    }
    
    function __toString() {
        $js = new Ae_Js();
        return $this->toJs($js);
    }
    
    function toJs(Ae_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        $res = $js->toJs($this->value, $indent, $indentStep, $newLines, 1);
        return $res;
    }
    
}