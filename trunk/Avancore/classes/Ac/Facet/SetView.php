<?php

abstract class Ac_Facet_SetView extends Ac_Prototyped {
    
    /**
     * @var Ac_Facet_Set
     */
    protected $facetSet = false;
    
    function setFacetSet(Ac_Facet_Set $facetSet) {
        $this->facetSet = $facetSet;
    }

    /**
     * @return Ac_Facet_Set
     */
    function getFacetSet() {
        return $this->facetSet;
    }    
    
    abstract function renderSet(Ac_Legacy_Controller_Response_Html $response);
    
    function getJsResponse() {
        
    }
    
    function getValue($source) {
        $res = array();
        $myValue = Ac_Util::getArrayByPath($source, Ac_Util::pathToArray($this->facetSet->getParamName()), array());
        if (is_array($myValue)) {
            $keys = array_intersect(array_keys($myValue), $this->facetSet->listItems());
            foreach ($keys as $key) {
                $value = $this->facetSet->getItem($key)->filterValue($myValue[$key]);
                if ($value !== false) $res[$key] = $value;
            }
        }
        return $res;
    }
    
}