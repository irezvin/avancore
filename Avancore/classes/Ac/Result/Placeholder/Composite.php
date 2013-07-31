<?php

class Ac_Result_Placeholder_Composite extends Ac_Result_Placeholder implements ArrayAccess {
    
    protected $placeholders = array();

    protected $usedPlaceholders = array();
    
    protected function setPlaceholders(array $prototypes) {
        $this->placeholders = $prototypes;
    }
    
    function listPlaceholders($onlyUsed = false) {
        return $onlyUsed? array_keys($this->usedPlaceholders) : array_keys($this->placeholders);
    }
    
    function listUsedPlaceholders() {
        return array_keys($this->usedPlaceholders);
    }

    /**
     * @return Ac_Result_Placeholder
     */
    function getPlaceholder($id) {
        if (!isset($this->placeholders[$id])) throw Ac_E_InvalidCall::noSuchItem ('placeholder', $id, 'listPlaceholders');
        if (!isset($this->usedPlaceholders[$id])) {
            $this->placeholders[$id] = Ac_Prototyped::factory($this->placeholders[$id], 'Ac_Result_Placeholder');
            $this->placeholders[$id]->setId($id);
            $this->usedPlaceholders[$id] = true;
        }
        return $this->placeholders[$id];
    }
    
    function __get($name) {
        return $this->getPlaceholder($name);
    }
    
    function __isset($name) {
        return isset($this->placeholders[$name]);
    }
    
    function __set($name, $value) {
        $this->getPlaceholder($name)->setItems($value);
    }
    
    function offsetGet($offset) {
        if (isset($offset, $this->placeholders)) $res = $this->getPlaceholder ($offset);
        else $res = parent::offsetGet($offset);
        return $res;
    }
    
    function offsetSet($offset, $value) {
        if (isset($this->placeholders[$offset])) $this->getPlaceholder($offset)->setItems($value);
        else parent::offsetSet($offset, $value);
    }
    
    function offsetUnset($offset) {
        if (isset($offset, $this->placeholders)) $this->getPlaceholder($offset)->clearItems();
        else parent::offsetUnset($offset);
    }
    
    function offsetExists($offset) {
        if (isset($offset, $this->placeholders)) return true;
        else parent::offsetExists($offset);
    }

        
    function clearItems() {
        $this->items = array();
        foreach (array_keys($this->usedPlaceholders) as $i)
            $this->placeholders[$i]->clearItems();
    }
    
    function getItems() {
        $res = $this->items;
        foreach (array_keys($this->usedPlaceholders) as $p) {
            if ($it = $this->placeholders[$p]->getItems())
                $res[$p] = $it;
        }
        return $res;
    }
    
    function getItemsForWrite(Ac_Result_Writer $writer) {
        if (!$this->usedPlaceholders) return parent::getItemsForWrite ($writer);
        $res = array();
        foreach (array_keys($this->usedPlaceholders) as $p) {
            if ($it = $this->placeholders[$p]->getItems()) {
                ob_start();
                $this->placeholders[$p]->write($writer);
                $res[$p] = ob_get_clean();
            }
        }
        $res = array_merge($res, parent::getItemsForWrite($writer));
        return $res;
    }

    function addItems($items) {
        foreach (array_intersect_key($items, $this->placeholders) as $id => $value) {
            $this->getPlaceholder($id)->addItems($value);
        }
        parent::addItems(array_diff_key($items, $this->placeholders));
    }
    
    protected function doMergeWith(Ac_Result_Placeholder $other) {
        if ($other instanceof Ac_Result_Placeholder_Composite) {
            $common = array_intersect(array_keys($this->placeholders), $other->listUsedPlaceholders());
            foreach ($common as $p) {
                $this->getPlaceholder($p)->mergeWith($other->getPlaceholder($p));
            } 
            $this->addItems(array_diff_key($other->getItems(), array_flip($common)));
        } else {
            return $this->addItems($other->getItems());
        }
    }
    
    protected function initFromPrototype(array $prototype = array(), $strictParams = null) {
        $gp = parent::initFromPrototype($prototype, false);
        $prototype = array_diff_key($prototype, array_flip($gp));
        foreach (array_intersect_key($prototype, $this->placeholders) as $k => $v) {
            $this->getPlaceholder($k)->setItems($v);
            unset($prototype[$k]);
            $gp[] = $v;
        }
        if ($prototype) $gp = array_merge($gp, parent::initFromPrototype($prototype, true));
        return $gp;
    }
    
}