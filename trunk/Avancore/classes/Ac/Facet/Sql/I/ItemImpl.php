<?php

interface Ac_Facet_Sql_I_ItemImpl {
    
    function applyToSelectPrototype(array & $prototype, Ac_Facet_Sql_I_ItemImpl $currValuesImpl = null);
    
    function applyToSelect(Ac_Sql_Select $select, Ac_Facet_Sql_I_ItemImpl $currValuesImpl = null);

    function setAlwaysApply($alwaysApply);

    function getAlwaysApply();
    
    /**
     * @return Ac_Facet_Item
     */
    function getItem();

}