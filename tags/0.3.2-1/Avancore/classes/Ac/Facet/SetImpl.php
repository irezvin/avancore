<?php

abstract class Ac_Facet_SetImpl extends Ac_Prototyped {
    
    /**
     * @var Ac_Facet_Set
     */
    protected $facetSet = false;
    
    abstract function getDefaultItemImplClass();

    function setFacetSet(Ac_Facet_Set $facetSet) {
        $this->facetSet = $facetSet;
    }
    
    function getRequiredItemImplClass() {
        return $this->getDefaultItemImplClass();
    }

    /**
     * @return Ac_Facet_Set
     */
    function getFacetSet() {
        return $this->facetSet;
    }    
    
    function createItemImpl(Ac_Facet_Item $item, array $prototype) {
        $prototype = Ac_Util::m (array('class' => $this->getDefaultItemImplClass()), $prototype);
        $res = Ac_Prototyped::factory($prototype, $this->getRequiredItemImplClass());
        $res->setItem($item);
        return $res;
    }
    
    
}