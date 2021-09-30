<?php

/**
 * 
 */

/**
 * @TODO ability of mixables to trigger their own events
 */
class Ac_Mixin_WithEvents extends Ac_Mixin implements Ac_I_WithEvents {

    use Ac_Event_TraitWithEvents;
    
}