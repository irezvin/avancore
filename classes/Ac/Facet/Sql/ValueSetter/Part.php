<?php

class Ac_Facet_Sql_ValueSetter_Part extends Ac_Facet_Sql_ValueSetter {
    
    protected $partName = false;

    function setPartName($partName) {
        $this->partName = $partName;
    }

    function getPartName() {
        return $this->partName;
    }
    
    function setValue (Ac_Facet_Sql_ItemImpl $impl, Ac_Sql_Select $select) {
        if ($this->partName === false) throw new Exception("setPartName() first");
        $select->getPart($this->partName)->bind($this->remapEmptyValue($impl, $impl->getValue()));
    }
    
//    function unsetValue (Ac_Facet_Sql_ItemImpl $impl, Ac_Sql_Select $select) {
//        
//    }
    
}