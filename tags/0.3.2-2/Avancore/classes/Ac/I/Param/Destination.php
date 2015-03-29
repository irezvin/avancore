<?php

interface Ac_I_Param_Destination extends Ac_I_Param_Source {
    
    function setParamValue(array $path, $value);
    
    function deleteParamValue(array $path);
    
}