<?php

/**
 * 
 */

/**
 * Interface for Mixables that can be shared within several Mixins
 */
interface Ac_I_Mixable_Shared extends Ac_I_Mixable {
    
    function callMixinMethod(Ac_I_Mixin $mixin, $method, array $arguments = array());
    
    function getMixinProperty(Ac_I_Mixin $mixin, $property);
    
    function setMixinProperty(Ac_I_Mixin $mixin, $property, $value);
    
    function issetMixinProperty(Ac_I_Mixin $mixin, $property, $value);
    
    function unsetMixinProperty(Ac_I_Mixin $mixin, $property, $value);
    
}