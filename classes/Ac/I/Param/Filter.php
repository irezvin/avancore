<?php

interface Ac_I_Param_Filter {
    
    function filter($value, Ac_Param $param = null);
    
    /**
     * Should return true if filter is applied after condition checking
     */
    function getIsFilterFinal();
    
}