<?php

class Ac_Callbacks {

    var $_callbacks = array();
    
    private static $instance = false;
    
    /**
     * @return Ac_Callbacks
     */
    function getInstance() {
        if (!self::$instance) self::$instance = new Ac_Callbacks();
        return self::$instance;
    }
    
    function setInstance(Ac_Callbacks $instance) {
        self::$instance = $instance;
    }
    
    function addHandler($callbackId, $callback, $handlerId = false) {
        if (!$this->findHandler($callbackId, $callback)) {
            if ($handlerId === false) {
                if (!isset($this->_callbacks[$callbackId])) $handlerId = 1;
                else {
                    $handlerId = 1;
                    while (array_key_exists($handlerId, $this->_callbacks[$callbackId])) $handlerId++;
                }
            }
            $this->_callbacks[$callbackId][$handlerId] = $callback;
        }
        return $handlerId;
    }
    
    function removeHandlerById($callbackId, $handlerId) {
        $res = false;
        if (isset($this->_callbacks[$callbackId]) && isset($this->_callbacks[$callbackId][$handlerId])) { 
            unset($this->_callbacks[$callbackId][$handlerId]);
            $res = true;
        }
        return $res;
    }
    
    function findHandler($callbackId, $callback) {
        $res = false;
        if (isset($this->_callbacks[$callbackId])) {
            foreach ($this->_callbacks[$callbackId] as $i => $cb) {
                $match = false;
                if (is_array($callback)) {
                    if (is_array($cb) && $cb[1] == $callback[1]) {
                        if (is_object($callback[0]) && is_object($cb[0])) {
                            if (Ac_Util::sameObject($callback[0], $cb[0])) {
                                $match = true;
                            }
                        } elseif (!is_object($callback[0]) && !is_object($cb[0])) $match = ($callback === $cb);
                    }
                }
                if ($match) {
                    $res = $match;
                    break;
                }
            }
        }
        return $res;
    }
    
    function removeHandler($callbackId, $callback) {
        if (($id = $this->findHandler($callbackId, $callback)) !== false) {
            unset($this->_callbacks[$callbackId][$id]);
            $res = true;
        } else $res = false;
        return $res;
    }
    
    function listCallbacks() {
        $res = array_keys($this->_callbacks);
        return $res;
    }
    
    function getHandlers($callbackId) {
        if (isset($this->_callbacks[$callbackId])) {
            $res = $this->_callbacks[$callbackId];
        } else {
            $res = array();
        }
    }
    
    /**
     * @param $callbackId
     * @param $params
     * @static
     */
    function call($callbackId, $params) {
        if (!isset($this) || !is_a($this, 'Ac_Callbacks')) $cb = & Ac_Callbacks::getInstance();
            else $cb = & $this;
            
        $res = null;

        if (isset($cb->_callbacks[$callbackId])) {
            $stack = debug_backtrace();
            $args = array();
            if (isset($stack[0]["args"])) {
                $c = count($stack[0]["args"]);
                for($i=1; $i < count($stack[0]["args"]); $i++) {
                    $args[$i] = & $stack[0]["args"][$i];
                }
            }
            foreach ($cb->_callbacks[$callbackId] as $callback) {
                $res = call_user_func_array($callback, $args);
            }
        }
        return $res;
    }
    
}