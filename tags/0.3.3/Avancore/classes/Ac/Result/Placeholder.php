<?php

class Ac_Result_Placeholder extends Ac_Prototyped implements ArrayAccess, IteratorAggregate {
    
    protected $items = array();
    
    protected $template = array('class' => 'Ac_Result_Placeholder_Template');
    
    protected $id = false;
    
    /**
     * @var bool
     */
    protected $overwriteOnMerge = false;

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
        if (is_null($offset)) $this->items[] = $value;
            else $this->items[$offset] = $value;
    }
    
    function offsetUnset($offset) {
        if (array_key_exists($offset, $this->items))
            unset($this->items[$offset]);
    }
    
    function getItems() {
        return $this->items;
    }
    
    function getItemsForWrite(Ac_Result_Writer $writer) {
        return $this->items;
    }
    
    function clearItems() {
        $this->items = array();
    }

    static function toArray($items, $forceScalarToArray = false) {
        if (is_object($items)) {
            if ($items instanceof Ac_Placeholder) {
                $res = $items->getItems();
            } else {
                if ($items instanceof IteratorAggregate) $items = $items->getIterator();
                if ($items instanceof Iterator) {
                    $res = array();
                    foreach ($items as $k => $v) $res[$k] = $v;
                } else {
                    $res = Ac_Util::toArray($items);
                }
            }
        } else {
            if (is_array($items)) $res = $items;
            elseif ($forceScalarToArray) $res = array($items);
            else $res = Ac_Util::toArray ($items);
        }
        return $res;
    }
    
    function setItems($items) {
        $this->clearItems();
        $items = self::toArray($items);
        if ($items) $this->addItems($items);
    }

    function hasItem($item) {
        return in_array($item, $this->items, true);
    }
    
    function addItem($item) {
        $this->offsetSet(null, $item);
    }
    
    function addItems($items) {
        $items = self::toArray($items, true);
        $this->items = array_merge($this->items, $items);
    }
    
    function mergeWith(Ac_Result_Placeholder $other) {
        if ($other->overwriteOnMerge) $this->clearItems();
        $this->doMergeWith($other);
    }
    
    protected function doMergeWith(Ac_Result_Placeholder $other) {
        Ac_Util::ms($this->items, $other->getItems());
    }
    
    function removeItem($item) {
        foreach (array_keys($this->items, $item, true) as $k) {
            unset($this->items[$k]);
        }
    }
    
    function write(Ac_Result_Writer $writer) {
        $this->getTemplateInstance()->writePlaceholder($this, $writer);
    }

    /**
     * @param bool $overwriteOnMerge
     */
    function setOverwriteOnMerge($overwriteOnMerge) {
        $this->overwriteOnMerge = $overwriteOnMerge;
    }

    /**
     * @return bool
     */
    function getOverwriteOnMerge() {
        return $this->overwriteOnMerge;
    }    
    
}