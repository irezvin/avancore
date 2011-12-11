<?php

interface Ae_I_Param_Source {

    function hasValue(array $path);
    
    function getValue(array $path, $default = null, & $found = null);
    
}