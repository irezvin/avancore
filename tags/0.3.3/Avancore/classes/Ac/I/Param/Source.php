<?php

interface Ac_I_Param_Source {

    function hasParamValue(array $path);
    
    function getParamValue(array $path, $default = null, & $found = null);
    
}