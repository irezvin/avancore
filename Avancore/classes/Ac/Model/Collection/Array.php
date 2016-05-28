<?php

class Ac_Model_Collection_Array extends Ac_Model_Collection_Abstract {
    
    /**
     * @var array
     */
    protected $items = array();

    function setItems(array $items) {
        if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
        $this->items = $items;
    }

    /**
     * @return array
     */
    function getItems() {
        return $this->items;
    }
    
    protected function doFetchGroup($offset, $length) {
        $this->fetches++;
        if (!$length) $length = null;
        if (!$offset && !$length) return $this->items;
        return array_slice($this->items, $offset, $length);
    }

    protected function doCount() {
        return count($this->items);
    }
        
}