<?php

class Ac_StringObject_Render implements Ac_I_StringObject_WithRender {
    
    protected $heldObject = null;
    
    protected $stringObjectMark = false;
    
    /**
     * @var string
     */
    protected $renderMethod = false;

    /**
     * @var array
     */
    protected $renderArgs = false;
    
    function __construct($heldObject = null, $renderMethod = '__toString', $renderArgs = array()) {
        Ac_StringObject::onConstruct($this);
        $this->heldObject = $heldObject;
        $this->renderMethod = $renderMethod;
        $this->renderArgs = $renderArgs;
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
    
    /**
     * @param string $renderMethod
     */
    function setRenderMethod($renderMethod) {
        $this->renderMethod = $renderMethod;
    }

    /**
     * @return string
     */
    function getRenderMethod() {
        return $this->renderMethod;
    }

    function setRenderArgs(array $renderArgs) {
        $this->renderArgs = $renderArgs;
    }

    /**
     * @return array
     */
    function getRenderArgs() {
        return $this->renderArgs;
    }    
    
    function getRenderedString() {
        return call_user_func_array(array($this->heldObject, $this->renderMethod), $this->renderArgs);
    }
    
}