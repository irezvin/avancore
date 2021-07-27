<?php

abstract class Ac_Facet_ItemView extends Ac_Prototyped {
    
    /**
     * @var Ac_Facet_Item
     */
    protected $item = false;

    function setItem(Ac_Facet_Item $item) {
        $this->item = $item;
    }

    /**
     * @return Ac_Facet_Item
     */
    function getItem() {
        return $this->item;
    }    
    
    function getMultiple() {
        return $this->getItem()->getMultiple();
    }
    
    function getHtmlName() {
        return $this->item->getFacetSet()->getParamName().'['.$this->item->getName().']';
    }
    
    function getHtmlId() {
        $res = str_replace('[', '_', $this->getHtmlName());
        $res = str_replace(']', '', $res);
        return $res;
    }
    
    function getExpandedPossibleValues() {
        $vv = $this->item->getPossibleValues();
        $res = array();
        foreach ($vv as $k => $item) {
            if (!is_array($item)) {
                $item = array('title' => $item, 'value' => is_numeric($k)? $item : $k);
            }
            if (!array_key_exists('title', $item)) $item['title'] = $item['value'];
            $res[$item['value']] = $item;
        }
        return $res;
    }
    
    function filterValue($value) {
        if (is_scalar($value)) {
            if ($this->item->getEmptyValue() !== false && $value == $this->item->getEmptyValue()) {
                $value = $this->item->getEmptyValue();
            } else {
                if ($this->item->getValueIsNatural()) {
                    if (!is_numeric($value) || $value <= 0) $value = false;
                } else {
                    $value = trim(strip_tags($value));
                    if (!strlen($value)) $value = false;
                }
            }
        } elseif ($this->getMultiple() && is_array($value)) {
            $res = array();
            foreach ($value as $v) {
                $v = $this->filterValue($v);
                if ($v !== false) $res[] = $v;
            }
            return $res;
        } else {
            $value = false;
        }
        return $value;
    }
    
    function notifySetValueChanged() {
    }
    
    function notifyItemValueChanged() {
    }
    
    abstract function renderItem(Ac_Controller_Response_Html $response);
    
}