<?php

class Ac_Deferred implements Ac_I_Deferred_ResultAware, Ac_I_Deferred_Substitute, Ac_I_StringObject {
    
    protected $evaluatorPrototype = false;

    /**
     * @var bool
     */
    protected $evaluateBeforeStore = false;

    protected $evaluatorArg = false;
    
    protected $stringObjectMark = false;
    
    function getEvaluatorPrototype() {
        return $this->evaluatorPrototype;
    }
    
    function setEvaluatorPrototype($evaluatorPrototype) {
        $this->evaluatorPrototype = $evaluatorPrototype;
    }

    function setEvaluatorArg($evaluatorArg) {
        $this->evaluatorArg = $evaluatorArg;
    }

    function getEvaluatorArg() {
        if ($this->evaluatorArg === false) $res = $this;
            else $res = $this->evaluatorArg;
        return $res;
    }
    
    function shouldEvaluate(Ac_Result_Stage_Morph $stage) {
        return $stage->getIsBeforeStore()? $this->evaluateBeforeStore : true;
    }
    
    /**
     * @param bool $evaluateBeforeStore
     */
    function setEvaluateBeforeStore($evaluateBeforeStore) {
        $this->evaluateBeforeStore = (bool) $evaluateBeforeStore;
    }

    /**
     * @return bool
     */
    function getEvaluateBeforeStore() {
        return $this->evaluateBeforeStore;
    }    

    function __construct($evaluatorPrototype = false, $evaluateBeforeStore = false, $evaluatorArg = false) {
        if ($evaluatorPrototype !== false) $this->evaluatorPrototype = $evaluatorPrototype;
        $this->evaluateBeforeStore = $evaluateBeforeStore;
        if ($evaluatorArg !== false) $this->evaluatorArg = $evaluatorArg;
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