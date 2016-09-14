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

class ExampleRendered implements Ac_I_StringObject_WithRender {
    
    var $string;
    
    protected $stringObjectMark = '';
        
    function __construct($string = 'someString') {
        $this->string = $string;
    }
    
    function getRenderedString() {
        return $this->string;
    }
    
    // ---- Ac_I_StringObject ----

    /**
     * @param string $stringObjectMark
     */
    function setStringObjectMark($stringObjectMark) {
        $this->stringObjectMark = $stringObjectMark;
    }

    /**
     * @return string
     */
    function getStringObjectMark() {
        return $this->stringObjectMark;
    }    
    
    function __toString() {
        if (!strlen($this->stringObjectMark)) Ac_StringObject::register($this);
        return $this->getStringObjectMark();
    }
    
    function __clone() {
        if (strlen($this->stringObjectMark)) Ac_StringObject::onClone($this);
    }
    
    function __wakeup() {
        Ac_StringObject::onWakeup($this);
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

class ExampleEvaluator2 implements Ac_I_Deferred_Evaluator {

    static $sfx = '';
    
    function evaluateDeferreds(array $deferreds) {
        $res = array();
        foreach ($deferreds as $k => $def) {
            $res[$k] = ''.$def.self::$sfx;
        }
        return $res;
    }    
    
}

class ExampleDeferred2 extends Ac_Deferred {
    
    static $defArg = '*def*';
    
    var $debData = '';
    
    function getEvaluatorArg() {
        if ($this->evaluatorArg === false) $res = self::$defArg;
            else $res = $this->evaluatorArg;
        return $res;
    }
    
    function getEvaluatorPrototype() {
        if (!$this->evaluatorPrototype) return array('class' => 'ExampleEvaluator2');
        else return $this->evaluatorPrototype;
    }
    
}
