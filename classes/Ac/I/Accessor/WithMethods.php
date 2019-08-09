<?php

/**
 * 
 */

/**
 * Interface for classes that support introspection of 'overloaded' methods
 */
interface Ac_I_Accessor_WithMethods extends Ac_I_Accessor, Ac_I_WithMethods {
    
    /**
     * Returns list of registered methods
     * 
     * @return array
     */
    function listMethods();
    
}