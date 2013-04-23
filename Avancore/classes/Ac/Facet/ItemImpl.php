<?php

abstract class Ac_Facet_ItemImpl extends Ac_Prototyped {
    
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
    
    abstract function getPossibleValues();

    protected $debug = false;

    function setDebug($debug) {
        $this->debug = (bool) $debug;
    }

    function getDebug() {
        return $this->debug;
    }    
    
}