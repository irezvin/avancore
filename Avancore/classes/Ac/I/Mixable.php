<?php

/**
 * 
 */

/**
 * Object that can be 'mixed' into a 'mixin' (can't explain better). Something like a dynamic trait.
 * Inspired by koowa framework (which probably borrowed it from somewhere else).
 */
interface Ac_I_Mixable {
    
    /**
     * Returns ID of mixable object. Numeric values, empty strings or scalars that resolve to empty strings
     * mean that mixable doesn't provide any particluar ID and mixin may assign any ID to it.
     * 
     * @return string
     */
    function getMixableId();
    
    /**
     * Returns list of properties that are exposed to the mixin
     * @return array Array of strings - property names
     */
    function listMixinProperties();
    
    /**
     * Returns list of methods that are exposed to the mixin
     * @return array Array of strings - method names
     */
    function listMixinMethods();
    
    /**
     * Is called when $this mixable is added to the mixin $mixin
     * 
     * @param Ac_I_Mixin $mixin
     */
    function registerMixin(Ac_I_Mixin $mixin);
    
    /**
     * Is called when $this mixable is removed from mixin $mixin
     * 
     * @param Ac_I_Mixin $mixin
     */
    function unregisterMixin(Ac_I_Mixin $mixin);
    
}