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

    /**
     * @return Ac_Facet_Set
     */
    function getFacetSet() {
        return $this->facetSet;
    }    
    
    function createItemImpl(Ac_Facet_Item $item, array $prototype) {
        $prototype = Ac_Util::m (array('class' => $this->getDefaultItemImplClass()), $prototype);
        $res = Ac_Prototyped::factory($prototype, $this->getDefaultItemImplClass());
        $res->setItem($item);
        return $res;
    }
    
    
}