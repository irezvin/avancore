<?php

class Ac_Result_Placeholder_Unique extends Ac_Result_Placeholder {
    
    function getHash($item) {
        return ''.$item;
    }
    
    function offsetExists($offset) {
        return array_key_exists($offset, $this->items);
    }
    
    function offsetGet($offset) {
        return isset($this->items[$offset])? $this->items[$offset] : null;
    }
    
    function offsetSet($offset, $value) {
        $hash = $this->getHash($value);
        if (!array_key_exists($hash, $this->items)) {
            $this->items[$hash] = $value;
        }
    }
    
    function offsetUnset($offset) {
        if (array_key_exists($offset, $this->items))
            unset($this->items[$offset]);
    }
    
    function getItems() {
        return array_values($this->items);
    }
    
    function hasItem($item) {
        return array_key_exists($this->getHash($item), $this->items);
    }
    
    function addItems($items) {
        $items = self::toArray($items);        
        foreach ($items as $value) {
            $hash = $this->getHash($value);
            if (!array_key_exists($hash, $this->items)) {
                $this->items[$hash] = $value;
            }
        }
    }
    
    function removeItem($item) {
        $hash = $this->getHash($item);
        unset($this->items[$hash]);
    }
    
    function mergeWith(Ac_Result_Placeholder $other) {
        if ($other instanceof Ac_Result_Placeholder_Unique)
            $this->items = array_merge($this->items, array_diff_key($other->items, $this->items));
        else 
            $this->addItems($other->getItems());
    }
    
    
}