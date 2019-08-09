<?php

interface Ac_I_WithCleanup {

    /**
     * Can return data in format
     * (0 => & $array, 1 => & $array, 'allowedClass1,allowedClass2' => & $array) 
     */
    function getCleanupArrayRefs();
    
    function invokeCleanup();
    
}