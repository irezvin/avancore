<?php

interface Ac_I_Param_Destination extends Ac_I_Param_Source {
    
    function setValue(array $path, $value);
    
    function deleteValue(array $path);
    
}