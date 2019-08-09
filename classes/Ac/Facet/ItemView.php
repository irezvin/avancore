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
    
    abstract function renderItem(Ac_Legacy_Controller_Response_Html $response);
    
}