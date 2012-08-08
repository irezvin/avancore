<?php

/**
 * @todo Rename to Ac_Content_Structured; add getPlaceholderDefaults() with prototypes of placeholders with given names (classes, options); 
 * check for allowedPlaceholders, isAppendable() and we will get omni-base for all structured content (CMSBlock etc)
 */
class Ac_Content_StructuredText extends Ac_Content_WithCharset implements Ac_I_WithCleanup, Ac_I_WithClone {

    const mergeRecursive = 'recursive';
    const mergeAppend = 'append';
    const mergeReplace = 'replace';
    
    const refsLeaveBoth = 'leaveBoth';
    const refsLeaveOriginal = 'leaveOriginal';
    const refsLeaveMerged = 'leaveMerged';
    
    protected $showBuffer = true;
    
    protected $buffer = array();
    
    protected $placeholders = array();
    
    protected static $stack = array();
    
    protected $capture = false;
    
    protected $removedFromStack = false;
    
    protected $name = false;
    
    protected $mergeMode = self::mergeRecursive;
    
    protected $refsOnMerge = self::refsLeaveBoth;
    
    protected $cloneOnMerge = false;
    
    protected $evLock = 0;
    
    function setName($name) {
        if ($this->name !== false && $this->name !== $name) 
            throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->name = $name;
    }

    function getName() {
        return $this->name;
    }    
    
    function setMergeMode($mergeMode) {
        if (!in_array($mergeMode, $m = array(self::mergeRecursive, self::mergeAppend, self::mergeReplace))) 
            throw Ac_E_InvalidCall::outOfSet ('mergeMode', $mergeMode, $m);
        $this->mergeMode = $mergeMode;
    }

    function getMergeMode() {
        return $this->mergeMode;
    }    
    
    function setRefsOnMerge($refsOnMerge) {
        if (!in_array($refsOnMerge, $m = array(self::refsLeaveBoth, self::refsLeaveOriginal, self::refsLeaveMerged))) 
            throw Ac_E_InvalidCall::outOfSet ('refsOnMerge', $refsOnMerge, $m);
        $this->refsOnMerge = $refsOnMerge;
    }

    function getRefsOnMerge() {
        return $this->refsOnMerge;
    }        
    
    function setCloneOnMerge($cloneOnMerge) {
        $this->cloneOnMerge = $cloneOnMerge;
    }

    function getCloneOnMerge() {
        return $this->cloneOnMerge;
    }    
    
    /**
     * Whether contents of the buffer should be returned by __toString()
     * @param boolean $showBuffer 
     */
    function setShowBuffer($showBuffer) {
        $this->showBuffer = (bool) $showBuffer;
    }

    /**
     * @return bool
     */
    function getShowBuffer() {
        return $this->showBuffer;
    }    
    
    /**
     * @return Ac_Current_StructuredText
     */
    static function current() {
        if ($c = count(self::$stack)) return self::$stack[$c-1];
            else return null;
    }
    
    /**
     * @throws Ac_E_InvalidCall 
     */
    static function out($_) {
        if (!($curr = self::current())) throw new Ac_E_InvalidUsage("Call to ::out() without ->begin()");
        for ($i = 0; $i < func_num_args(); $i++)
            $curr->append(func_get_arg($i));
    }
    
    function begin($placeholder = false, $replace = false) {
        $ph = $placeholder !== false? $this->getPlaceholder($placeholder, true) : $this;
        array_push(self::$stack, $ph);
        Ac_Buffer::begin(array($ph, 'append'), false, false, true, array($ph, 'end'), array(), array(1));
    }
    
    function end() {
        if (func_num_args() > 0 && func_get_arg(0) == 1) {
            if (!($curr = self::current()))
                throw new Ac_E_InvalidUsage("Call to ->end() without ->begin()");
            array_pop(self::$stack);
        } else {
            Ac_Buffer::end();
        }
    }
    
    /**
     * @param string|array $placeholder Name or array path to the placeholder
     * @param boolean $create Create placeholder if it does not exist (will append reference to the buffer)
     * 
     * @return Ac_Content_StructuredText|null
     */
    function getPlaceholder($placeholder, $create = false) {
        $noRef = func_num_args() > 2? (bool) func_get_arg(2) : false;
        $res = null;
        if (is_array($placeholder)) {
            $curr = $this;
            while ($curr && count($placeholder)) {
                $top = array_shift($placeholder);
                $curr = $curr->getPlaceholder($top, $create, $noRef);
            }
            if (!count($placeholder)) $res = $curr;
        } else {
            if (isset($this->placeholders[$placeholder])) $res = $this->placeholders[$placeholder];
            elseif ($create) {
                $res = $this->createPlaceholder($placeholder);
                $this->placeholders[$placeholder] = $res;
                if (!$noRef) $this->append($res->createRef());
            }
        }
        return $res;
    }
    
    function listPlaceholders() {
        return array_keys($this->placeholders);
    }
    
    function hasPlaceholder($placeholder) {
        return $this->getPlaceholder($placeholder) !== null;
    }
    
    /**
     * @return Ac_Content_StructuredText_PlaceholderRef 
     */
    function createRef($target = null) {
        $res = new Ac_Content_StructuredText_PlaceholderRef($this, $target);
        return $res;
    }
    
    /**
     * @return Ac_Content_StructuredText $this instance for a chaining
     */
    function setText($text, $placeholder = false) {
        $noRef = func_num_args() > 2? (bool) func_get_arg(2) : false;
        if ($placeholder !== false) $this->getPlaceholder($placeholder, true, $noRef)->setText($text);
        else {
            if (count(self::$stack)) Ac_Buffer::flush();
            $this->buffer = array($text);
        }
        return $this;
    }
    
    /**
     *
     * @param mixed|Ac_Content_StructuredText $text
     * @return Ac_Content_StructuredText $this instance for a chaining
     */
    function append($text, $placeholder = false, $replace = false) {
        $noRef = func_num_args() > 3? (bool) func_get_arg(3) : false;
        if (count(self::$stack)) Ac_Buffer::flush();
        if ($replace === true) {
            $this->setText($text, $placeholder, $noRef);
        }
        else {
            if ($placeholder !== false) {
                $this->getPlaceholder($placeholder, true, $noRef)->append($text, false, $replace, $noRef);
            } else {
                if (is_object($text) && $text instanceof Ac_Content_StructuredText && strlen($nm = $text->getName())) {
                    if (isset($this->placeholders[$nm])) {
                        if ($replace) $this->replacePlaceholder ($nm, $text);
                        else {
                            // untested...
                            $text->mergeToContent($this->placeholders[$nm]);
                        }
                    } else {
                        $this->placeholders[$nm] = $text;
                        if (!$noRef) $this->buffer[] = $text->createRef();
                    }
                } else {
                    $this->buffer[] = $text;
                }
            }
        }
        return $this;
    }
    
    function putPlaceholder(Ac_Content_StructuredText $ph, $placeholder = false, $replace = false, $withRef = false) {
        $this->append($ph, $placeholder, $replace, !$withRef);
    }
    
    /**
     * @return Ac_Content_StructuredText $this instance for a chaining
     */
    function prepend($text, $placeholder = false) {
        if ($placeholder !== false) {
            $this->getPlaceholder($placeholder, true)->prepend($text);
        } else {
            array_unshift($this->buffer, $text);
        }
        return $this;
    }
    
    function getBuffer($asString = false) {
        // TODO: add current StructuredText to the deferred evaluation context?
        return $asString? implode("", $this->buffer) : $this->buffer;
    }
    
    function getEvaluated() {
        if ($this->evLock) {
            trigger_error("Cyclic reference detected in Ac_Content_StructuredText tree", E_USER_WARNING);
            return '';
        }
        $this->evLock++;
        if ($this->showBuffer) $res = $this->getBuffer(true);
        else {
            Ac_Value_Impl_Deferred::evaluateDeferreds($this->buffer);
            $res = "";
        }
        $this->evLock--;
        return $res;
    }
    
    function __toString() {
        if ($this->isDeferred) return Ac_Value_Impl_DeferredString::getMarkForString($this);
            else {
                return $this->getEvaluated();
            }
    }
    
    function output($callback = null) {
        foreach ($this->buffer as $item) {
            if (is_object($item) && $item instanceof Ac_I_WithOutput) {
                $item->output($callback);
            } else {
                if ($callback !== null) {
                    call_user_func($callback, $item);
                } else {
                    echo $item;
                }
            }
        }
    }
    
    /**
     * @return Ac_Content_StructuredText
     */
    protected function createPlaceholder($name = false) {
        $res = new Ac_Content_StructuredText;
        if (strlen($name)) $res->setName($name);
        return $res;
    }

    protected function replacePlaceholder($placeholder, Ac_Content_StructuredText $replaceWith = null, $refsOnly = false, & $buffer = null) {
        $res = false;
        $ph = null;
        if ($placeholder === false) $ph = $this;
        elseif (is_object($placeholder) && $placeholder instanceof Ac_Content_StructuredText) {
            $ph = $placeholder;
            if (!$refsOnly) {
                foreach ($this->placeholders as $name => $phObj) 
                    if ($phObj === $ph) {
                        $res = true;
                        if ($replaceWith) $this->placeholders[$name] = $ph;
                        else unset($this->placeholders[$name]);
                    }
            }
        }
        elseif (is_array($placeholder)) {
            if (count($placeholder)) {
                $last = array_pop($placeholder);
                if ($top = $this->getPlaceholder (array_slice($placeholder, 0, -1)))
                    $res = $top->replacePlaceholder($last, $replaceWith, $refsOnly);
            } else {
                throw new Ac_E_InvalidCall("Array argument to replacePlaceholder() should contain at leas one segment!");
            }
        } else {
            if (isset($this->placeholders[$placeholder])) {
                $ph = $this->placeholders[$placeholder];
                if (!$refsOnly) {
                    if ($replaceWith) $this->placeholders[$placeholder] = $replaceWith;
                        else unset($this->placeholders[$placeholder]);
                }
                $res = true;
            }
        }
        if ($ph) {
            if (!is_null($buffer)) $buf = & $buffer;
                else $buf = & $this->buffer;
                
            foreach ($buf as $k => $item) {
                if (
                    is_object($item) 
                    && $item instanceof Ac_Content_StructuredText_PlaceholderRef 
                    && $item->getPlaceholder() === $ph
                ) {
                    if ($replaceWith) {
                        $buf[$k] = clone $this->buffer[$k];
                        $buf[$k]->setPlaceholder($replaceWith);
                    }
                    else unset($buf[$k]);
                }
            }
        }
        return $res;
    }
    
    function clear() {
        Ac_Impl_Cleanup::clean($this);
    }
    
    protected function mergeToSameClass(Ac_Content_StructuredText $content) {
        if ($this->mergeMode == self::mergeAppend) {
            // Temporarily remove the name to avoid the collisions in $content' placeholders
            $tmp = $this->name;
            $this->name = false;
            if ($this->cloneOnMerge()) $content->append($this->createClone());
                else $content->append($this);
            $this->name = $tmp;
        }
        elseif ($this->mergeMode == self::mergeReplace) {
            $content->clear();
            $tmp = $this->mergeMode;
            $this->mergeMode = self::mergeRecursive;
            $this->mergeToSameClass($content);
            $this->mergeMode = $tmp;
        }
        elseif ($this->mergeMode == self::mergeRecursive) {
            $merged = $this->cloneOnMerge? $this->createClone() : $this;
            $oBuf = $content->buffer;
            $mBuf = $merged->buffer;

            foreach ($merged->placeholders as $name => $ph) {
                if (isset($content->placeholders[$name])) {
                    
                    // A difficult case: we have to drop merged' placeholder in favour of one of $content
                    // replace or remove references according to $refsOnMerge value
                    
                    // TODO: decide: should we do remove references only if they occur in the other buffer?
                    
                    $ph->mergeToContent($content->placeholders[$name]);
            
                    if ($this->refsOnMerge === self::refsLeaveOriginal) {
                        
                        // Remove references to the placeholders in merged' buffer
                        $content->replacePlaceholder($merged->placeholders[$name], null, true, $mBuf);
                        
                        // This is done to rewrite refs in nested placeholders' buffers (empty array is provided to avoid buffer changes)
                        $tmp = array();
                        $content->replacePlaceholder($merged->placeholders[$name], $content->placeholders[$name], false, $tmp);
                        
                    } else {
                        
                        $this->replacePlaceholder($merged->placeholders[$name], $content->placeholders[$name], false, $mBuf);
                        
                        if ($this->refsOnMerge === self::refsLeaveMerged) {
                            // Remove references to original placeholder in the buffer
                            $content->replacePlaceholder($content->placeholders[$name], null, true, $oBuf);
                        }
                        
                    }
                }
                else $content->placeholders[$name] = $ph;
            }
            $content->buffer = array_merge($oBuf, $mBuf);
        }
    }
    
    /**
     * Should merge this content with the content of exactly the parent class
     * @param Ac_Content $content 
     */
    protected function mergeToParentClass(Ac_Content $content) {
        throw new Exception("Cannot merge anything to Ac_Content since it's an abstract class");
    }
    
    function getCleanupArrayRefs() {
        return array(
            'Ac_Content_StructuredText,Ac_Content_StructuredText_PlaceholderRef' => array(& $this->buffer, & $this->placeholders)
        );
    }
    
    function invokeCleanup() {
    }
    
    /**
     * @return Ac_Content_StructuredText 
     */
    function createClone() {
        $c = get_class($this);
        $res = clone $this;
        $res->replacePlaceholder($this, $res, true);
        foreach ($res->placeholders as $k => $ph) {
            $clone = $ph->createClone();
            $res->placeholders[$k] = $clone;
            $res->replacePlaceholder($ph, $clone, true);
        }
        foreach ($res->buffer as $k => $v) {
            if (is_object($v) && $v instanceof Ac_Content_StructuredText) $res->buffer[$k] = $v->createClone();
        }
        return $res;
    }
    
}