<?php

/**
 * 
 */

/**
 * @TODO ability of mixables to trigger their own events
 */
class Ac_Mixin_WithEvents extends Ac_Mixin implements Ac_I_WithEvents {
    
    protected $eventHandlers = array();
    
    function addEventListener($objectOrCallback, $event) {
        return Ac_Event::addEventListener($this->eventHandlers, $objectOrCallback, $event);
    }
    
    function getEventListeners($event) {
        return Ac_Event::getEventListener($this->eventHandlers, $event);
    }
    
    function deleteEventListener($objectOrCallback, $event = null) {
        return Ac_Event::deleteEventListener($this->eventHandlers, $objectOrCallback, $event);
    }
    
    // This method is PUBLIC by intention to allow mixables to define their own events
    function triggerEvent($event, array $arguments = array()) {
        return Ac_Event::triggerEvent($this, $this->eventHandlers, $event, $arguments);
    }
    
}