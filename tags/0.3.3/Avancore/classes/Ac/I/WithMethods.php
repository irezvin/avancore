<?php

interface Ac_I_WithMethods {
    
    /**
     * Returns TRUE if such method can be called
     * 
     * @param string $methodName
     * @return bool
     */
    function hasMethod($methodName);
    
}