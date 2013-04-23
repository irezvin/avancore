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
    function createSelectForItem(Ac_Facet_ItemImpl $itemImpl = null) {
        $proto = $this->getSelectPrototype();
        $set = $this->getFacetSet();
        $usedFacets = $set->getValueOrder();
        if ($itemImpl) $usedFacets = array_diff($usedFacets, array($itemImpl->getItem()->getName()));
        foreach ($usedFacets as $name) $set->getItem($name)->getImpl()->applyToSelectPrototype($proto);
        $select = new Ac_Sql_Select(new Ac_Sql_Db_Ae(), $proto);
        foreach ($usedFacets as $name) $set->getItem($name)->getImpl()->applyToSelect($select);
        return $select;
    }

    
}