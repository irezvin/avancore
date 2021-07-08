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
            $itemNames = $this->facetSet->listItems();
            $keys = array_intersect(array_keys($myValue), $itemNames);
            foreach ($keys as $key) {
                $value = $this->facetSet->getItem($key)->filterValue($myValue[$key]);
                if ($value !== false) $res[$key] = $value;
            }
            if (false !== ($n = $this->facetSet->getEmptyParamName())) {
                if (isset($myValue[$n])) $res[$n] = $myValue[$n];
            }
        }
        return $res;
    }
    
    function notifySetValueChanged() {
    }    
    
}