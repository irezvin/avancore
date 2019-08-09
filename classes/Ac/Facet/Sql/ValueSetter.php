<?php

abstract class Ac_Facet_Sql_ValueSetter {
    
    protected $emptyValues = array('', NULL);

    function setEmptyValues($emptyValues) {
        $this->emptyValues = $emptyValues;
    }

    function getEmptyValues() {
        return $this->emptyValues;
    }    
    
    abstract function setValue (Ac_Facet_Sql_ItemImpl $impl, Ac_Sql_Select $select);
    
    function remapEmptyValue(Ac_Facet_Sql_ItemImpl $impl, $value) {
        $res = array();
        if (is_array($value)) {
            foreach ($value as $v) {
                if ($this->isEmptyValue($impl, $v)) $res = array_merge($res, $this->emptyValues);
                else $res[] = $v;
            }
        } else {
            if ($this->isEmptyValue($impl, $value)) $res = $this->emptyValues;
            else $res = $value;
        }
        return $res;
    }
    
    function isEmptyValue(Ac_Facet_Sql_ItemImpl $impl, $value) {
        if (($v = $impl->getEmptyValue()) !== false && $v === $value) {
            $res = true;
        } else $res = false;
        return $res;
    }
    
    //function unsetValue (Ac_Facet_Sql_ItemImpl $impl, Ac_Sql_Select $select);
    
}