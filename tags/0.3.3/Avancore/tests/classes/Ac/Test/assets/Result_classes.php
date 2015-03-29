<?php

class StringObjectCache extends Ac_StringObject {
    
    static function getStrings() {
        return Ac_StringObject::$strings;
    }
    
    static function setStrings(array $strings) {
        Ac_StringObject::$strings = $strings;
    }
    
    static function clearStrings() {
        Ac_StringObject::$strings = array();
    }
    
}

class AllHandler implements Ac_I_Result_Handler {

    static $log = array();
    
    var $myLog = null;
    
    var $name = false;
    
    static function so($name, & $log = null) {
        return new Ac_StringObject_Wrapper(new self($name, $log));
    }
    
    function __construct($name, & $log = null) {
        $this->name = $name;
        if (!is_null($log)) $this->myLog = & $log;
            else $this->myLog = & self::$log;
    }
    
    function handleDefault($event, $stage, $result) {
        $aa = func_get_args();
        array_unshift($aa, $this->name);
        foreach ($aa as $k => $v) 
            if (is_object($v)) {
                if ($v instanceof Ac_Result) $aa[$k] = $v->getDebugData();
                elseif (method_exists($v, '__toString')) $aa[$k] = ''.$v;
                else $aa[$k] = get_class($v);
            }
        self::$log[] = implode('; ', $aa);
    }
    
}

class StageIterator extends Ac_Result_Stage {
    
    var $travLog = array();
    
    var $defaultTraverseClasses = array('Ac_Result', 'Ac_I_StringObject');
    
    function traverse($classes = null) {
        return parent::traverse($classes);
    }
    
    function resetTraversal($classes = null) {
        $this->travLog = array();
        return parent::resetTraversal($classes);
    }
    
    function traverseNext() {
        return parent::traverseNext();
    }
    
    function invokeHandlers(Ac_Result $result = null, $stageName, $args = null) {
        $args = func_get_args();
        if ($result) {
            $aa = $args;
            foreach ($aa as $k => $v) 
                if (is_object($v) && $v instanceof Ac_Result) $aa[$k] = $v->getDebugData();
            $this->travLog[] = implode(' ',$aa);
        }
        return call_user_func_array(array('Ac_Result_Stage', 'invokeHandlers'), $args);
    }
    
    function getIsAscend() {
        return $this->isAscend;
    }
    
}

class BunchResult extends Ac_Result {
    
    var $bunch = array();

    function touchStringObjects() {
        parent::touchStringObjects();
    }
    
    protected function doGetTraversableBunch($classes = false) {
        if ($classes !== false) {
            $res = Ac_Util::getObjectsOfClass($this->bunch, $classes);
        } else {
            $res = $this->bunch;
        }
        return $res;
    }
    
    function addToList($property, $object, $position) {
        array_splice($this->bunch[$property], $position, 0, array($object));
        $this->touchStringObjects();
    }
    
    function removeFromList($property, $object) {
        $k = array_search($object, array_values($this->bunch[$property]), true);
        if ($k !== false) {
            array_splice($this->bunch[$property], $k, 1);
        }
        $this->touchStringObjects();
    }
    
}

class FooResult extends Ac_Result {
}

class BarResult extends Ac_Result {
}


class TestStreamValue extends Ac_Value_Stream {
    
    var $outputCalled = 0;
    
    function output($callback = null) {
        $this->outputCalled++;
        return parent::output($callback);
    }
    
}