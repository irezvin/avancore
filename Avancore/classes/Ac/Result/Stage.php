<?php

class Ac_Result_Stage extends Ac_Prototyped {
    
    // is used to call special methods of the handlers
    protected $stageName = '';    
    
    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @var bool
     */
    protected $isComplete = false;
    
    /**
     * @var Ac_Result
     */
    protected $root = null;

    
    // ---- traversal-related
    
    
    protected $current = null;
    
    /**
     * @var Ac_Result
     */
    protected $parent = null;

    protected $stack = array();
    
    protected $stackProps = array('current', 'parent', 'position', 'isAscend');
    
    protected $defaultTraverseClasses = 'Ac_Result';
    
    protected $beginItemCallback = null;

    protected $endItemCallback = null;
    
    function setRoot(Ac_Result $root) {
        if ($root !== ($oldRoot = $this->root)) {
            $this->root = $root;
            if ($oldRoot) throw new Ac_E_InvalidUsage("Can setRoot() only once");
        }
    }

    /**
     * @return Ac_Result
     */
    function getRoot() {
        return $this->root;
    }

    /**
     * @return bool
     */
    function getIsActive() {
        return $this->isActive;
    }

    /**
     * @return bool
     */
    function getIsComplete() {
        return $this->isComplete;
    }

    // ---- result tree traversal ----
    
    function setBeginItemCallback($beginItemCallback) {
        if ($beginItemCallback) {
            if (!is_callable($beginItemCallback)) 
                throw Ac_E_InvalidCall::wrongType("beginItemCallback", $beginItemCallback, array("callable", "null"));
        } else {
            $beginItemCallback = null;
        }
        $this->beginItemCallback = $beginItemCallback;
    }

    function getBeginItemCallback() {
        return $this->beginItemCallback;
    }

    function setEndItemCallback($endItemCallback) {
        if ($endItemCallback) {
            if (!is_callable($endItemCallback)) 
                throw Ac_E_InvalidCall::wrongType("endItemCallback", $endItemCallback, array("callable", "null"));
        } else {
            $endItemCallback = null;
        }
        $this->endItemCallback = $endItemCallback;
    }
    
    function getEndItemCallback() {
        return $this->endItemCallback;
    }    
    
    protected function beginItem($item) {
        if ($this->beginItemCallback) {
            call_user_func($this->beginItemCallback, $item, $this, 'beginItem');
        }
    }
    
    protected function endItem($item) {
        if ($this->endItemCallback) {
            call_user_func($this->endItemCallback, $item, $this, 'endItem');
        }
    }
    
    protected $beganCurrent = false;
    
    protected $isAscend = false;
    
    protected $traversalClasses = 'Ac_Result';
    
    /**
     * @var Ac_Result_Position
     */
    protected $position = false;
    
    protected $positionIsSet = false;
    
    protected $noAdvance = false;
 
    protected function traverse($classes = null) {
        if (is_null($classes)) $classes = $this->defaultTraverseClasses;
        
        if (!$this->positionIsSet) $this->resetTraversal($classes);
            else $this->positionIsSet = false;
            
        while ($this->traverseNext()) {
        }
    }

    protected function resetTraversal($classes = null) {
        if (is_null($classes)) $classes = $this->defaultTraverseClasses;
        
        $this->parent = null;
        $this->current = $this->root;
        $this->stack = array();
        $this->isAscend = false;
        $this->traversalClasses = $classes;
        $this->isActive = true;
        $this->isComplete = false;
    }

    protected function pushStack($retOnly = false) {
        $res = array();
        foreach ($this->stackProps as $p) {
            $res[$p] = $this->$p;
        }
        if (!$retOnly) $this->stack[] = $res;
        return $res;
    }
    
    protected function popStack($fromWhat = false) {
        if ($fromWhat === false) $fromWhat = array_pop($this->stack);
        foreach ($fromWhat as $k => $v) $this->$k = $v;
    }
    
    /**
     * Checks if current, parent or root item should be overriden by 
     * ReplaceWith or OverrideMode properties
     */
    protected function checkOverride() {
        $old = $this->current;
        $was = array($this->current);
        while (($repl = $this->current->getReplaceWith()) && ($repl !== $this->current)) {
            $this->current = $repl;
            if (in_array($repl, $was, true)) throw new Exception("Cyclic reference detected in Ac_Result::getReplaceWith()");
            $was[] = $repl;
        }
        if ($this->current !== $old) {
            if ($this->position) {
                $this->position->replaceCurrentObject($this->current);
            }
        }
        if ($this->current->getOverrideMode() == Ac_Result::OVERRIDE_PARENT) {
            if ($this->parent) {
                $new = $this->current;
                $this->popStack();
                $this->position->replaceCurrentObject($new);
                $this->current = $new;
            }
        } elseif ($this->current->getOverrideMode() == Ac_Result::OVERRIDE_ALL) {
            $curr = $this->current;
            // goto root node
            array_splice($this->stack, 1);
            $this->popStack();
            $this->current->setReplaceWith($curr);
            $this->current = $curr;
        }
    }
 
    protected $switchedCurrent = false;
    
    protected function traverseNext() {
        if ($this->current) {
            $this->isActive = true;
            $doneCurrent = false;
            $this->switchedCurrent = false;
            if (!$this->isAscend) {
                
                if ($this->current instanceof Ac_Result) $this->checkOverride();
                $this->beginItem($this->current);
                
                $fc = false;
                if ($this->current instanceof Ac_Result) {
                    $position = new Ac_Result_Position($this->current, $this->traversalClasses);
                    $fc = $position->advance();
                    if ($fc) {
                        $this->pushStack();
                        $this->parent = $this->current;
                        $this->current = $fc;
                        $this->position = $position;
                        $this->switchedCurrent = true;
                    }
                }
                
                if (!$fc) {
                    $this->endItem($this->current);
                    $doneCurrent = true;
                }
            }
            if (!$this->switchedCurrent) {
                do { // we have to iterate while we ascend to prevent double visiting
                    if ($this->position) $ns = $this->position->advance();
                    else $ns = null;
                    if ($ns) {
                        $this->isAscend = false;
                        if (!$doneCurrent) {
                            $this->endItem($this->current);
                            $doneCurrent = true;
                        }
                        $this->current = $ns;
                    } else {
                            if ($this->isAscend) $this->endItem ($this->current);
                            if (count($this->stack)) {
                                $this->popStack();
                                $this->isAscend = true;
                            } else {
                                $this->parent = null;
                                $this->current = null;
                            }
                    }
                    $doneCurrent = false;
                } while ($this->isAscend && $this->current);
            }
            $res = $this->current;
        } else {
            $res = false;
        }
        if (!$res) {
            $this->isActive = false;
            $this->isComplete = true;
        }
        return $res;
    }

    /**
     * @return Ac_Result
     */
    function getCurrentResult() {
        if ($this->current instanceof Ac_Result) $res = $this->current;
        elseif ($this->position) $res = $this->position->getResult();
        else $res = null;
        return $res;
    }
    
    /**
     * @return Ac_Result
     */
    function getParentResult() {
        return $this->parent;
    }
    
    function getStack() {
        if (!$this->position) return array();
        $res = array($this->position->getPosition(true));
        foreach (array_reverse(array_keys($this->stack)) as $k) {
            if (!$this->stack[$k]['position']) continue;
            $res[] = $this->stack[$k]['position']->getPosition(true);
        }
        return $res;
    }
    
    /**
     * @return object
     */
    function getCurrentObject() {
        $res = null;
        if ($this->position) $res = $this->position->getObject();
        return $res;
    }
    
    function isAtRoot() {
        if (!$this->isActive) $res = null;
        else $res = (bool) $this->current === $this->root;
        return $res;
    }
    
    function getCurrentProperty() {
        $res = null;
        if ($this->position) {
            $pos = $this->position->getPosition();
            $res = $pos[0];
        }
        return $res;
    }
    
    function getCurrentPropertyIsString() {
        $res = null;
        if ($this->position) $res = $this->position->getIsString();
        return $res;
    }
    
    function getCurrentOffset() {
        $res = null;
        if ($this->position) {
            $pos = $this->position->getPosition();
            $res = $pos[1];
        }
        return $res;
    }
    
    function getIsChangeable() {
        return (bool) ($this->position && !$this->position->getIsDone());
    }
    
    /**
     * Puts content immediately after the current object (self::getCurrentObject())
     * @param string|object $content
     */
    function put($content) {
        if ($this->getIsChangeable()) {
            $this->position->insertAfter($content, true);
        } else {
            throw new Ac_E_InvalidUsage("Cannot put() when not in position; check with getIsChangeable() first");
        }
    }

    /**
     * Replaces self::getCurrentObject() with $content (object or string)
     */
    function replaceCurrentObject($content) {
        if ($this->getIsChangeable()) {
            $this->position->replaceCurrentObject($content, true);
        } else {
            throw new Ac_E_InvalidUsage("Cannot replaceCurrentObject() when not in position; check with getIsChangeable() first");
        }
    }
    
    function replaceParentObject(Ac_Result $replaceWith, $replaceRoot = false) {
        if (!count($this->stack))
            throw new Ac_E_InvalidUsage("Cannot replaceParentObject() when not at child node");
        $this->popStack();
        $this->current->setReplaceWith($replaceWith);
        if ($replaceRoot) $this->current->setOverrideMode(Ac_Result::OVERRIDE_ALL);
        $this->switchedCurrent = true;
        $this->checkOverride();
    }
    
    function setApplication(Ac_Application $application) {
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        if ($this->application === false) {
            return Ac_Application::getDefaultInstance();
        }
        return $this->application;
    }    
    
    protected function startAt(Ac_Result_Stage $other) {
        foreach (array('root', 'current', 'parent', 'stack', 'isActive', 'isAscend') as $p)
            $this->$p = $other->$p;
        $this->position = clone $other->position;
        $this->positionIsSet = true;
    }
    
    function invoke() {
        if ($this->isComplete) throw new Ac_E_InvalidUsage("invoke() already called; check with getIsComplete() first");
        $this->traverse();
    }
    
}