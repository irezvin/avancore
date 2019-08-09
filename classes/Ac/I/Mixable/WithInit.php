<?php

interface Ac_I_Mixable_WithInit {
    
    /**
     * This function is called by mixin' constructor if the mixable is assigned in prototype
     * object or is mixin's core mixable.
     * 
     * Note: onMixnInit is called only when all known properties are already assigned
     * 
     * @param array $extraProperties Subset of prototype array consting of not-found properties
     *      
     * 
     * @param Ac_I_Mixin $mixin Mixin object that is being initialized (most probably it is
     *       same mixin that was passed to last registerMixin() call; the parameter is provided
     *       for shared mixins' comfort)
     * 
     * @returns array($acquiredProperties) List of properties that were acquired by Mixable (properties
     *      not acquired by any of the mixins may cause errors)
     */
    function handleMixinInit (array $extraProperties, Ac_I_Mixin $mixin);
    
}