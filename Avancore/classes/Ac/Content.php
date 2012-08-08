<?php

abstract class Ac_Content implements Ac_I_DeferredString, Ac_I_WithOutput, Ac_I_Prototyped {
    
    protected $mimeType = false;

    /**
     * @TODO add decorator support & tests for it
     */
    protected $decorator = false;
    
    protected $cacheAsValue = false;
    
    protected $isDeferred = false;
    
    protected $deferredStringMark = false;
    
    static $debugInstances = 0;
    
    function __toString() {
        if ($this->isDeferred) return Ac_Value_Impl_DeferredString::getMarkForString($this);
            else return $this->getEvaluated();
    }
    
    abstract function getEvaluated();
    
    /**
     * To "merge to" $content means add $this content' data to the content $content
     * @param Ac_Content $content 
     */
    function mergeToContent(Ac_Content $content) {
        if (get_class($content) == get_class($this)) {
            $this->mergeToSameClass($content);
        } else {
            $comm = self::findCommonParent($t = get_class($this), $c = get_class($content));
            if (!strlen($comm) || !is_subclass_of($comm, 'Ac_Content') || $comm !== 'Ac_Content') {
                throw new Exception("Cannot find common parent class for classes '{$t}' and '{$c}' in Ac_Content tree");
            }
            $compatible = $this->demoteToParent($comm);
            $compatible->mergeToSameClass($content);
        }
    }
    
    protected static function getAllParents($class) {
        $res = array();
        while (strlen($c = get_parent_class($class))) array_unshift($res, $c);
        return $res;
    }
    
    protected static function findCommonParent($class1, $class2) {
        $a = self::getAllParents($class1);
        $b = self::getAllParents($class2);
        $n = min(count($a), count($b));
        $res = false;
        for ($i = 0; ($i < $n - 1) && $a[$i] == $b[$i]; $i++) {
            $res = $a[$i];
        }
        return $res;
    }
    
    /**
     * @param string $parentClass 
     * @return Ac_Content
     */
    protected function demoteToParent($parentClass) {
        if (!is_subclass_of($this, $parentClass)) {
            throw new Ac_E_InvalidCall(get_class($this)." is not subclass of \$parentClass '$parentClass'");
        }
        $curr = $this;
        do {
            $directParentClass = get_parent_class($curr);
            $parent = new $directParentClass(); // TODO: use factory??
            $curr->mergeToParentClass($parent);
        } while (get_class($curr) !== $parentClass);
        return $curr;
    }
    
    /**
     * Should merge this content with the content of the same or compatible class
     * @param Ac_Content $content 
     */
    protected function mergeToSameClass(Ac_Content $content) {
        throw new Exception("Call to abstract method");
    }
    
    /**
     * Should merge this content with the content of exactly the parent class
     * @param Ac_Content $content 
     */
    protected function mergeToParentClass(Ac_Content $content) {
        throw new Exception("Call to abstract method");
    }

    /**
     * Immediately evaluates and outputs the content.
     * Is intended to be called by Ac_Response_Output instances directly into the browser or CMS' output buffer.
     * Concrete subclasses may optimize the implementation.
     */
    function output($callback = null) {
        if ($callback !== null) call_user_func($callback, $this->getEvaluated());
            else echo $this->getEvaluated();
    }
    
    /**
     * Whether this StructuredText should act as a deffered string and __toString() should return deferred placeholder 
     * instead of actual buffer content
     * 
     * @param bool $isDeferred 
     */
    function setIsDeferred($isDeferred) {
        $this->isDeferred = (bool) $isDeferred;
    }

    /**
     * @return bool
     */
    function getIsDeferred() {
        return $this->isDeferred;
    }    

    /**
     * Whether the Content should be converted to string before storing in the cache
     * @param bool $cacheAsValue 
     */
    function setCacheAsValue($cacheAsValue) {
        $this->cacheAsValue = $cacheAsValue;
    }

    function getCacheAsValue() {
        return $this->cacheAsValue;
    }    

    function setMimeType($mimeType) {
        $this->mimeType = $mimeType;
    }

    function getMimeType() {
        return $this->mimeType;
    }    
    
    function setDecorator($decorator) {
        $this->decorator = $decorator;
    }

    function getDecorator() {
        return $this->decorator;
    }

    function setDeferredStringMark($deferredStringMark) {
        if ($this->deferredStringMark) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->deferredStringMark = $deferredStringMark;
    }

    function getDeferredStringMark() {
        return $this->deferredStringMark;
    }
    
    function hasPublicVars() {
        return false;
    }    
    
    function __construct(array $options = array()) {
        if (self::$debugInstances) Ac_Debug::reportConstruct($this);
        if ($options) Ac_Accessor::setObjectProperty($this, $options, null, true);
    }
    
    function __destruct() {
        if (self::$debugInstances) Ac_Debug::reportDestruct($this);
    }
    
    function __clone() {
        if (self::$debugInstances) Ac_Debug::reportConstruct($this);
    }
    
    
}