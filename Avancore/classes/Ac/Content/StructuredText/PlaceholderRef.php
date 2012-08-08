<?php

class Ac_Content_StructuredText_PlaceholderRef implements Ac_I_WithOutput, Ac_I_WithCleanup {
    
    /**
     * @var Ac_Content_StructuredText
     */
    protected $placeholder = null;
    
    protected $target = null;
    
    static $debugInstances = 0;
    
    function output($callback = null) {
        if ($t = $this->getTargetPlaceholder())
            return $t->output($callback);
        else
            return null;
    }

    function __construct(Ac_Content_StructuredText $placeholder = null, $target = null) {
        if (!is_null($placeholder)) $this->setPlaceholder($placeholder);
        if (!is_null($target)) $this->setTarget($target);
        if (self::$debugInstances) Ac_Debug::reportConstruct($this);
    }
    
    function __destruct() {
        if (self::$debugInstances) Ac_Debug::reportDestruct($this);
    }
     
    function __clone() {
        if (self::$debugInstances) Ac_Debug::reportConstruct($this);
    }
   
    function __toString() {
        if ($t = $this->getTargetPlaceholder())
            $res = $t->__toString();
        else
            $res = '';
        return $res;
    }
    
    function setPlaceholder(Ac_Content_StructuredText $placeholder) {
        $this->placeholder = $placeholder;
    }

    /**
     * @return Ac_Content_StructuredText
     */
    function getPlaceholder() {
        return $this->placeholder;
    }

    /**
     * @return Ac_Content_StructuredText
     */
    function getTargetPlaceholder($create = false) {
        $res = $this->target === null? 
            $this->placeholder : 
            $this->placeholder->getPlaceholder($this->target, $create);
        return $res;
    }
    
    function setTarget($target) {
        $this->target = $target;
    }

    function getTarget() {
        return $this->target;
    }    
    
    function getCleanupArrayRefs() {
    }
    
    function invokeCleanup() {
        $this->placeholder = null;
    }
    
    
}