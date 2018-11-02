<?php

abstract class Ac_Result_Writer extends Ac_Prototyped {
    
    /**
     * @var Ac_Result
     */
    protected $target = null;

    /**
     * @var Ac_Result
     */
    protected $source = null;
    
    /**
     * @var Ac_Result_Stage
     */
    protected $stage = null;

    function setTarget(Ac_Result $target = null) {
        $this->target = $target;
    }

    /**
     * @return Ac_Result
     */
    function getTarget() {
        return $this->target;
    }
    
    function setSource(Ac_Result $source) {
        $this->source = $source;
    }

    function setStage(Ac_Result_Stage $stage = null) {
        $this->stage = $stage;
    }
    
    function initAtStage(Ac_Result_Stage $stage) {
        $this->setStage($stage);
        if (!$this->source) $this->source = $stage->getCurrentResult();
        if (!$this->target) $this->target = $stage->getParentResult();
    }

    /**
     * @return Ac_Result_Stage
     */
    function getStage() {
        return $this->stage;
    }    

    /**
     * @return Ac_Result
     */
    function getSource() {
        return $this->source;
    }
    
    protected function requiresTarget() {
        return true;
    }
    
    protected function requiresStage() {
        return false;
    }
    
    function writeResult(Ac_Result $result, $return = false) {
        $this->setSource($result);
        $this->write();
    }
    
    /**
     * Does necessary merging operation.
     * ECHOs string output (usually to replace $target in the content).
     * 
     * @param bool $return Whether to return string result instead of ECHOing it
     * 
     * @return string
     * @throws Ac_E_InvalidUsage
     */
    function write($return = false) {
        if (!($r = $this->getSource())) {
            throw new Ac_E_InvalidUsage("setSource() first");
        }
        $r->setMerged(true);
        $t = $this->getTarget();
        if ($this->requiresTarget() && !$t) {
            throw new Ac_E_InvalidUsage("setTarget() first");
        }
        $s = $this->getStage();
        if ($this->requiresStage() && !$this->getStage()) {
            throw new Ac_E_InvalidUsage("setStage() first");
        }
        if ($return) ob_start();
        $this->implWrite($r, $t, $s);
        if ($return) {
            return ob_get_clean();
        }
    }
    
    abstract protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null);
    
}
