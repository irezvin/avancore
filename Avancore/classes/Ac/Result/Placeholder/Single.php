<?php

class Ac_Result_Placeholder_Single extends Ac_Result_Placeholder {
    
    function offsetSet($offset, $value) {
        $this->items = array();
        $this->items[$offset] = $value;
    }
    
    function offsetUnset($offset) {
        if (array_key_exists($offset, $this->items))
            unset($this->items[$offset]);
    }
    
    function addItems($items) {
        $items = self::toArray($items);
        if (count($items)) {
            $kk  = array_keys($items);
            $k = array_pop($kk);
            $this->items = array($k => $items[$k]);
        }
    }
    
    function mergeWith(Ac_Result_Placeholder $other) {
        return $this->addItems($other->getItems());
    }
    
}