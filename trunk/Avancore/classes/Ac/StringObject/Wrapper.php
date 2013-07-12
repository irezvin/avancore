<?php

class Ac_StringObject_Wrapper implements Ac_I_StringObjectWrapper {
    
    protected $heldObject = null;
    
    protected $stringObjectMark = false;
    
    function __construct($heldObject = null) {
        Ac_StringObject::onConstruct($this);
        $this->heldObject = $heldObject;
    }
    
    function __clone() {
        Ac_StringObject::onClone($this);
    }
    
    function __wakeup() {
        Ac_StringObject::onWakeup($this);
    }
    
    function setStringObjectMark($stringObjectMark) {
        $this->stringObjectMark = $stringObjectMark;
    }

    function getStringObjectMark() {
        return $this->stringObjectMark;
    }
    
    function __toString() {
        return $this->stringObjectMark;
    }

    function setHeldObject($heldObject) {
        $this->heldObject = $heldObject;
    }

    function getHeldObject() {
        return $this->heldObject;
    }    
    
}