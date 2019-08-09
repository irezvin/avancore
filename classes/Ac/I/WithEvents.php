<?php

interface Ac_I_WithEvents {
    
    function addEventListener($objectOrCallback, $event);
    
    function getEventListeners($event);
    
    function deleteEventListener($objectOrCallback, $event = null);
    
}