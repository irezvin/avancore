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
    
    
    /**
     * @var Ac_Result
     */
    protected $current = null;
    
    /**
     * @var Ac_Result
     */
    protected $parent = null;

    protected $stack = array();
    
    
    
    
    protected $currentProperty = false;

    protected $currentPosition = false;

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
//        $this->current = $current;
//    }

    /**
     * @return Ac_Result
     */
    function getCurrent() {
        return $this->current;
    }

//    function setCurrentProperty($currentProperty) {
//        $this->currentProperty = $currentProperty;
//    }
//
//    function getCurrentProperty() {
//        return $this->currentProperty;
//    }
//
//    function setCurrentPosition($currentPosition) {
//        $this->currentPosition = $currentPosition;
//    }
//
//    function getCurrentPosition() {
//        return $this->currentPosition;
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
    
    protected function beginItem(Ac_Result $item) {
        $this->invokeHandlers($this->parent, 'beforeChild', $item);
        $this->invokeHandlers($item, 'beginStage');
    }
    
    protected function endItem(Ac_Result $item) {
        $this->invokeHandlers($item, 'endStage');
        $this->invokeHandlers($this->parent, 'afterChild', $item);
    }
    
    protected function getFirstChild(Ac_Result $item) {
        // TODO: better implementation
        
        $sr = array_values(Ac_Util::flattenArray($item->getSubResults()));
        if (count($sr)) $res = $sr[0];
        else $res = null;
        return $res;
    }
    
    protected function getNextSibling(Ac_Result $item, Ac_Result $parent = null) {
        // TODO: better implementation using PHP' "internal array pointers"

        if ($parent) {
            $fa = Ac_Util::flattenArray($parent->getSubResults());
            $v = array_values($fa);
            $res = null;
            if (($key = array_search($item, $v, true)) !== false) {
                array_splice($v, 0, $key + 1);
                $res = array_shift($v);
            }
        } else {
            $res = null;
        }
        return $res;
    }
    
    protected $beganCurrent = false;
    
    protected $isAscend = false;
    
    protected $traversalClasses = 'Ac_Result';
 
    protected function traverse($classes = 'Ac_Result') {
        $this->resetTraversal($classes);
        while ($this->traverseNext()) {
        };
    }

    protected function resetTraversal($classes) {
        $this->parent = null;
        $this->current = $this->root;
        $this->stack = array();
        $this->isAscend = false;
        $this->traversalClasses = $classes;
    }
    
    protected function traverseNext() {
        if ($this->current) {
            $doneCurrent = false;
            $switchedCurrent = false;
            if (!$this->isAscend) {
                $this->beginItem($this->current);
                $fc = $this->getFirstChild($this->current);
                if ($fc) {
                    array_push($this->stack, array($this->current, $this->parent));
                    $this->parent = $this->current;
                    $this->current = $fc;
                    $switchedCurrent = true;
                } else {
                    $this->endItem($this->current);
                    $doneCurrent = true;
                }
            }
            if (!$switchedCurrent) {
                $ns = $this->getNextSibling($this->current, $this->parent);
                if ($ns) {
                    $this->isAscend = false;
                    if (!$doneCurrent) {
                        $this->endItem($this->current);
                        $doneCurrent = true;
                    }
                    $this->current = $ns;
                } else {
                    if (count($this->stack)) {
                        list ($this->current, $this->parent) = array_pop($this->stack);
                        $this->isAscend = true;
                    } else {
                        $this->parent = null;
                        if ($this->isAscend) $this->endItem ($this->current);
                        $this->current = null;
                    }
                }
            }
            $res = (bool) $this->current;
        } else {
            $res = false;
        }
        return $res;
    }
    
}