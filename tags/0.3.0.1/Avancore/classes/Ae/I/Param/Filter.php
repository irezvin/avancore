<?php

interface Ae_I_Param_Filter {
    
    function filter($value, Ae_Param $param = null);
    
    /**
     * Should return true if filter is applied after condition checking
     */
    function getIsFilterFinal();
    
}