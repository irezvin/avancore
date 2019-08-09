<?php

class Ac_Etl_Stats {
    
    protected $items = array();
    
    function setItems(array $items) {
        foreach ($items as $k => $v) {
            if (is_str($v)) $this->items[$k] = array('caption' => $v);
        }
        $this->items = Ac_Prototyped::factoryCollection($items, 'Ac_Etl_Stats_Item', array(), 'id');
    }
    
    function getItems() {
        return $this->items;
    }
    
    function listItems() {
        return array_keys($this->items);
    }
    
    /**
     * @return Ac_Etl_Stats_Item
     */
    function addItem($item) {
        $item = Ac_Prototyped::factory($item, 'Ac_Etl_Stats_Item');
        if (!strlen($id = $item->getId())) throw new Exception("\$item->\$id must be set with setId()");
        if (isset($this->items[$id])) throw new Exception("Item with id '\$id' already exists");
        $this->items[$id] = $item;
        return $item;
    }
    
    /**
     * @return Ac_Etl_Stats_Item
     */
    function getItem($id, $dontCreate = false) {
        if (!isset($this->items[$id])) {
            if ($dontCreate) throw new Exception("No such item '$id'");
            else $this->items[$id] = new Ac_Etl_Stats_Item(array('id' => $id));
        }
        return $this->items[$id];
    }
    
    function add($id, $value) {
        $this->getItem($id)->add($value);
    }
    
    function addMany(array $pairs) {
        foreach ($pairs as $k => $v) $this->getItem($k)->add($v);
    }
    
    function getValues() {
        return Ac_Accessor::getObjectProperty($this->items, 'value');
    }
    
    function reset() {
        foreach ($this->items as $item) $item->reset();
    }
    
}