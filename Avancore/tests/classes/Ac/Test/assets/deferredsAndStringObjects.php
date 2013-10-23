<?php

class ExampleDeferred extends Ac_Deferred {
    
    var $firstPart = '1';
    
    var $secondPart = ' 2';
    
    function __construct($firstPart, $secondPart = ' 2', $evaluateBeforeStore = false) {
        $this->firstPart = $firstPart;
        $this->secondPart = $secondPart;
        parent::__construct(false, $evaluateBeforeStore);
    }
    
    function getEvaluatorPrototype() {
        $res = parent::getEvaluatorPrototype();
        if ($res === false) $res = array();
        if (is_array($res)) $res = Ac_Util::m(array('class' => 'ExampleEvaluator', 'secondPart' => $this->secondPart), $res);
        return $res;
    }
    
}

class ExampleRendered {
    
    var $string;
    
    function __construct($string = 'someString') {
        $this->string = $string;
    }
    
    function __toString() {
        return $this->string;
    }
    
}

class ExampleEvaluator extends Ac_Prototyped implements Ac_I_Deferred_Evaluator {
    
    var $secondPart = ' 2';
    
    static $thirdPart = ' 3';
    
    function hasPublicVars() {
        return true;
    }
    
    function evaluateDeferreds(array $deferreds) {
        $res = array();
        foreach ($deferreds as $k => $def) {
            if ($def instanceof ExampleDeferred) $firstPart = $def->firstPart;
                else $firstPart = ''.$def;
            $res[$k] = $firstPart . $this->secondPart . self::$thirdPart;
        }
        return $res;
    }
    
}
