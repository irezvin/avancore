<?php

class Ac_Js_Code implements Ac_I_Jsable {

    var $items = array();
    
    function __construct($items) {
        if (func_num_args() > 1) $this->items = func_get_args();
            else $this->items = Ac_Util::toArray($items);
    }
    
    function __toString() {
        return $this->toJs(new Ac_Js());
    }
    
    function toJs(Ac_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        $res = "";
        $allItems = Ac_Util::flattenArray($this->items);
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