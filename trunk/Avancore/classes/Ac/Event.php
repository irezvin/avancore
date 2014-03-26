<?php

abstract class Ac_Event {

    const EVENT_ON_ALL_EVENTS = 'onAllEvents';
    
    protected static $stack = array();

    protected static $current = false;
    
    static function triggerEvent($issuer, $eventHandlers, $event, array $arguments = array()) {
        $a = isset($eventHandlers[Ac_Event::EVENT_ON_ALL_EVENTS]);
        $e = isset($eventHandlers[$event]);
        $res = array();
        if ($a || $e) {
            array_push(self::$stack, array($event, $issuer, $arguments, self::$current));
            self::$current = false;
            
            // do the stuff
            if ($e) {
                foreach($eventHandlers[$event] as $objectOrCallback) {
                    if (is_object($objectOrCallback)) $callback = array($objectOrCallback, $event);
                        else $callback = $objectOrCallback;
                    $res[] = call_user_func_array($callback, $arguments);
                }
            }
            if ($a) {
                foreach ($eventHandlers[Ac_Event::EVENT_ON_ALL_EVENTS] as $objectOrCallback) {
                    if (is_object($objectOrCallback)) $callback = array($objectOrCallback, $event);
                        else $callback = $objectOrCallback;
                    if (is_callable($callback)) $res[] = call_user_func_array($callback, $arguments);
                }
            }
            foreach($hh as $objectOrCallback) {
                if (is_object($objectOrCallback)) $callback = array($objectOrCallback, $event);
            }
            $tmp = array_pop(self::$stack);
            self::$current = $tmp[3];
        }
        return $res;
    }
    
    /**
     * @return Ac_Event_Data
     */
    static function getCurrent() {
        if (self::$current === false) {
            if (count(self::$stack)) {
                end(self::$stack);
                $tmp = current(self::$stack);
                self::$current = new Ac_Event_Data($tmp[0], $tmp[1], $tmp[2]);
            } else {
                self::$current = null;
            }
        }
        return self::$current;
    }
    
    static function addEventListener(array & $eventHandlers, $objectOrCallback, $event) {
        $eventHandlers[$event][] = $objectOrCallback;
    }
    
    static function deleteEventListener(array & $eventHandlers, $objectOrCallback, $event = null) {
        if (is_null($event))  // remove all listeners
            $l = array_keys($eventHandlers); 
        else 
            $l = isset($eventHandlers[$event])? array($event) : array();
        foreach ($l as $key) {
            foreach ($eventHandlers[$key] as $subKey => $handler) {
                if ($handler === $objectOrCallback) unset($eventHandlers[$key][$subKey]);
            }
        }
    }
    
    static function getEventListeners(array $eventHandlers, $event = null) {
        if (is_null($event)) $res = $eventHandlers;
            else $res = isset($eventHandlers[$event])? $eventHandlers[$event] : array();
        return $res;
    }
    
    
}