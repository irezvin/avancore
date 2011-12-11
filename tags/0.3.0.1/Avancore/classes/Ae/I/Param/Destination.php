<?php

interface Ae_I_Param_Destination extends Ae_I_Param_Source {
    
    function setValue(array $path, $value);
    
    function deleteValue(array $path);
    
}