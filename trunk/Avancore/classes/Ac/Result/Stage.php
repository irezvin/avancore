<?php

class Ac_Result_Stage extends Ac_Prototyped {

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
    
    protected $defaultTraverseClasses = 'Ac_Result';
    
    
    
    
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

//    function setCurrent(Ac_Result $current) {
//        $this->currentResult = $current;
//    }

    // ---- result tree traversal ----
    
    protected function invokeHandlers(Ac_Result $result = null, $stageName, $args = null) {
        if ($result && ($handlers = $result->getHandlers())) {
            
            $methodName = 'handle'.$stageName;
            $args = func_get_args();
            array_splice($args, 0, 2, array($this, $result));
            
            $methodName2 = 'handleDefault';
            $args2 = $args; array_splice($args2, 0, 0, array($stageName));
            
            foreach ($handlers as $handler) {
                if (is_callable($call = array($handler, $methodName)))
                    call_user_func_array($call, $args);
                elseif (is_callable($call = array($handler, $methodName2)))
                    call_user_func_array($call, $args);
            }
        }
    }
    
    protected function beginItem($item) {
        if ($item instanceof Ac_Result) {
            $this->invokeHandlers($this->parent, 'beforeChild', $item);
            $this->invokeHandlers($item, 'beginStage');
        }
    }
    
    protected function endItem($item) {
        if ($item instanceof Ac_Result) {
            $this->invokeHandlers($item, 'endStage');
            $this->invokeHandlers($this->parent, 'afterChild', $item);
        }
    }
    
    protected $beganCurrent = false;
    
    protected $isAscend = false;
    
    protected $traversalClasses = 'Ac_Result';
    
    /**
     * @var Ac_Result_Stage_Position
     */
    protected $position = false;
 
    protected function traverse($classes = null) {
        if (is_null($classes)) $classes = $this->defaultTraverseClasses;
        
        $this->resetTraversal($classes);
        while ($this->traverseNext()) {
        };
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
    
    protected function traverseNext() {
        if ($this->current) {
            $this->isActive = true;
            $doneCurrent = false;
            $switchedCurrent = false;
            if (!$this->isAscend) {
                
                $this->beginItem($this->current);
                
                $fc = false;
                if ($this->current instanceof Ac_Result) {
                    $position = new Ac_Result_Stage_Position($this->current, $this->traversalClasses);
                    $fc = $position->advance();
                    if ($fc) {
                        array_push($this->stack, array($this->current, $this->parent, $this->position));
                        $this->parent = $this->current;
                        $this->current = $fc;
                        $this->position = $position;
                        $switchedCurrent = true;
                    }
                }
                
                if (!$fc) {
                    $this->endItem($this->current);
                    $doneCurrent = true;
                }
            }
            if (!$switchedCurrent) {
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
                    if (count($this->stack)) {
                        list ($this->current, $this->parent, $this->position) = array_pop($this->stack);
                        $this->isAscend = true;
                    } else {
                        $this->parent = null;
                        if ($this->isAscend) $this->endItem ($this->current);
                        $this->current = null;
                    }
                }
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
        if ($this->isChangeable()) {
            $this->position->insertAfter($content, true);
        } else {
            throw new Ac_E_InvalidUsage("Cannot put() when not in position; check with getIsChangeable() first");
        }
    }

    /**
     * Replaces self::getCurrentObject() with $content (object or string)
     */
    function replaceCurrentObject($content) {
        if ($this->isChangeable()) {
            $this->position->replaceCurrentObject($content, true);
        } else {
            throw new Ac_E_InvalidUsage("Cannot put() when not in position; check with getIsChangeable() first");
        }
    }
    
}