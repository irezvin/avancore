<?php

/**
 * Object that has method output() optimized for echoing object' content into the output stream 
 */
interface Ac_I_WithOutput {
    
    /**
     * Outputs object to the output stream or to callback function
     * @param $callback If provided, it will be used instead of echo()
     */
    function output($callback = null);
    
}