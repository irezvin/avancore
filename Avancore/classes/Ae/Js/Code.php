<?php

class Ae_Js_Code implements Ae_I_Jsable {

    var $items = array();
    
    function __construct($items) {
        if (func_num_args() > 1) $this->items = func_get_args();
            else $this->items = Ae_Util::toArray($items);
    }
    
    function __toString() {
        return $this->toJs(new Ae_Js());
    }
    
    function toJs(Ae_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        $res = "";
        $allItems = Ae_Util::flattenArray($this->items);
        foreach ($allItems as & $item) {
            if (is_string($item)) {
                $s = $item;
            } 
            else $s = $js->toJs($item, $indent, $indentStep, $newLines);
            $res .= $s;
        }
        return $res;
    }
    
}