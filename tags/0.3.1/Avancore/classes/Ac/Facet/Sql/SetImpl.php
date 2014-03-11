<?php

class Ac_Facet_Sql_SetImpl extends Ac_Facet_SetImpl {
    
    /**
     * @var Ac_Sql_Select
     */
    protected $selectPrototype = array();
        
    protected $withCounts = null;

    protected $countColName = false;
    
    function getDefaultItemImplClass() {
        return 'Ac_Facet_Sql_ItemImpl';
    }
    
    function getRequiredItemImplClass() {
        return 'Ac_Facet_Sql_I_ItemImpl';
    }

    function setSelectPrototype(array $selectPrototype) {
        $this->selectPrototype = $selectPrototype;
    }

    function getSelectPrototype() {
        return $this->selectPrototype;
    }

    /**
     * @return Ac_Sql_Select
     */
    function createSelect() {
        return $this->createSelectForItem(null);
    }

    function setWithCounts($withCounts) {
        if (!is_null($withCounts)) $withCounts = (bool) $withCounts;
        $this->withCounts = $withCounts;
    }

    function getWithCounts() {
        if (is_null($this->withCounts)) return $this->countColName !== false;
        else 
            return $this->withCounts;
    }

    function setCountColName($countColName) {
        $this->countColName = $countColName;
    }

    function getCountColName() {
        return $this->countColName;
    }    
    
    /**
     * @return Ac_Sql_Select
     */
    function createSelectForItem(Ac_Facet_Sql_I_ItemImpl $currValuesImpl = null) {
        $proto = $this->getSelectPrototypeWithFacets($currValuesImpl);
        $select = new Ac_Sql_Select(new Ac_Sql_Db_Ae(), $proto);
        $this->applyFacetsToSelectInstance($select, $currValuesImpl);
        return $select;
    }
    
    function applyFacetsToSelectInstance(Ac_Sql_Select $select, Ac_Facet_Sql_I_ItemImpl $currValuesImpl = null) {
        $set = $this->getFacetSet();
        $usedFacets = $set->getValueOrder();
        $alwaysApply = array();
        foreach ($this->facetSet->getItems() as $name => $item) {
            $impl = $item->getImpl();
            if ($impl instanceof Ac_Facet_Sql_I_ItemImpl && !array_key_exists($name, $usedFacets) && $impl->getAlwaysApply()) {
                $alwaysApply[$name] = $impl;
            }
        }
        foreach ($usedFacets as $name) {
            $impl = $set->getItem($name)->getImpl();
            if ($impl instanceof Ac_Facet_Sql_I_ItemImpl) {
                $impl->applyToSelect($select, $currValuesImpl);
            }
        }
        foreach ($alwaysApply as $impl) $impl->applyToSelect($select, $currValuesImpl);
    }
    
    /**
     * @return Ac_Sql_Select
     */
    function getSelectPrototypeWithFacets(Ac_Facet_Sql_I_ItemImpl $currValuesImpl = null) {
        $proto = $this->getSelectPrototype();
        $proto['distinct'] = true;
        $set = $this->getFacetSet();
        $usedFacets = $set->getValueOrder();
        //if ($itemImpl) $usedFacets = array_diff($usedFacets, array($itemImpl->getItem()->getName()));
        $alwaysApply = array();
        foreach ($this->facetSet->getItems() as $name => $item) {
            $impl = $item->getImpl();
            if ($impl instanceof Ac_Facet_Sql_I_ItemImpl && !array_key_exists($name, $usedFacets) && $impl->getAlwaysApply()) {
                $alwaysApply[$name] = $impl;
            }
        }
        foreach ($usedFacets as $name) {
            $impl = $set->getItem($name)->getImpl();
            if ($impl instanceof Ac_Facet_Sql_I_ItemImpl) {
                $impl->applyToSelectPrototype($proto, $currValuesImpl);
            }
        }
        foreach ($alwaysApply as $impl) $impl->applyToSelectPrototype($proto, $currValuesImpl);
        return $proto;
    }

    
}