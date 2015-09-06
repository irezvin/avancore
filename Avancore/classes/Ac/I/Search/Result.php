<?php

interface Ac_I_Search_Result extends Iterator {
    
    /**
     * @return array
     */
    function getQuery();

    function getSort();
    
    function getLimit();
    
    function getOffset();
    
    function getTotalCount();
    
    /**
     * @return Ac_I_Search_Provider
     */
    function getSearchProvider();
    
}