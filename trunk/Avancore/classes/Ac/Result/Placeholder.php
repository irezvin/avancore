<?php

class Ac_Result_Placeholder extends Ac_Prototyped implements ArrayAccess, IteratorAggregate {
    
    protected $items = array();
    
    protected $template = array('class' => 'Ac_Result_Placeholder_Template');
    
    protected $id = false;

    function setId($id) {
        if ($this->id !== false && $id !== $this->id) throw new Ac_E_InvalidCall("Can setId() only once");
        $this->id = $id;
    }

    function getId() {
        if ($this->id === false) return '<untitled placeholder>';
        return $this->id;
    }
    
    function setTemplate($template) {
        if (is_object($template) && !($template instanceof Ac_I_Result_PlaceholderTemplate))
            throw Ac_E_InvalidCall::wrongClass ('template', $template, 'Ac_I_Result_PlaceholderTemplate');
        $this->template = $template;
    }

    function getTemplate() {
        return $this->template;
    }
    
    /**
     * @return Ac_I_Result_PlaceholderTemplate
     */
    function getTemplateInstance() {
        if (is_object($this->template)) return $this->template;
        else return Ac_Prototyped::factory($this->template, 'Ac_I_Result_PlaceholderTemplate');
    }
    
    function getIterator() {
        return new ArrayIterator($this->items);
    }
    
    function offsetExists($offset) {
        return array_key_exists($offset, $this->items);
    }
    
    function offsetGet($offset) {
        return isset($this->items[$offset])? $this->items[$offset] : null;
    }
    
    function offsetSet($offset, $value) {
        $this->items[$offset] = $value;
    }
    
    function offsetUnset($offset) {
        if (array_key_exists($offset, $this->items))
            unset($this->items[$offset]);
    }
    
    function getItems() {
        return $this->items;
    }
    
    function hasItem($item) {
        return in_array($item, $this->items, true);
    }
    
    function addItems(array $items) {
        $this->items = array_merge($this->items, $items);
    }
    
    function removeItem($item) {
        foreach (array_keys($this->items, $item, true) as $k) {
            unset($this->items[$k]);
        }
    }
    
    function mergeWith(Ac_Result_Placeholder $other) {
        Ac_Util::ms($this->items, $other->getItems());
    }
    
    function write(Ac_Result_Writer $writer) {
        $this->getTemplateInstance()->writePlaceholder($this, $writer);
    }
    
}