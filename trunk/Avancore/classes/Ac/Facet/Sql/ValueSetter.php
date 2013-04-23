<?php

abstract class Ac_Facet_Sql_ValueSetter {
    
    abstract function setValue (Ac_Facet_Sql_ItemImpl $impl, Ac_Sql_Select $select);
    
    function isEmptyValue(Ac_Facet_Sql_ItemImpl $impl, $value) {
        if (($v = $impl->getItem()->getEmptyValue()) !== false && $v === $value) {
            $res = true;
        } else $res = false;
        return $res;
    }
    
    //function unsetValue (Ac_Facet_Sql_ItemImpl $impl, Ac_Sql_Select $select);
    
}