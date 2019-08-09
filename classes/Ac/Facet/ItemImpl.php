<?php

abstract class Ac_Facet_ItemImpl extends Ac_Prototyped {
    
    /**
     * @var Ac_Facet_Item
     */
    protected $item = false;
    
    protected $value = false;
    
    public function __construct(array $prototype = array()) {
        $this->value = Ac_Facet_ValueNotSet::instance();
        parent::__construct($prototype);
    }

    function setItem(Ac_Facet_Item $item) {
        $this->item = $item;
    }

    /**
     * @return Ac_Facet_Item
     */
    function getItem() {
        return $this->item;
    }    
    
    function getEmptyValue() {
        return $this->item->getEmptyValue();
    }

    function setValue($value = null) {
        if (func_num_args() == 0) $value = Ac_Facet_ValueNotSet::instance();
        $this->value = $value;
    }

    function getValue() {
        if (Ac_Facet_ValueNotSet::is($this->value)) return $this->item->getValue();
        else return $this->value;
    }
    
    abstract function getPossibleValues();
    
}