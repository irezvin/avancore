<?php

class Ac_Result_Writer_Replace extends Ac_Result_Writer {

    /**
     * @var bool
     */
    protected $innerWriter = false;

    /**
     * @var bool
     */
    protected $replaceAll = false;

    /**
     * @param bool $innerWriter
     */
    function setInnerWriter($innerWriter) {
        $this->innerWriter = $innerWriter;
    }

    /**
     * @return bool
     */
    function getInnerWriter() {
        return $this->innerWriter;
    }

    /**
     * @param bool $replaceAll
     */
    function setReplaceAll($replaceAll) {
        $this->replaceAll = $replaceAll;
    }

    /**
     * @return bool
     */
    function getReplaceAll() {
        return $this->replaceAll;
    }    
    
    protected function requiresTarget() {
        return true;
    }
    
    protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        $repl = false;
        if ($s && $s instanceof Ac_Result_Stage_Write) {
            if ($s->getCurrentObject() === $r && $s->getParentResult() === $t) {
                $s->replaceParentObject($r, $this->replaceAll);
                $repl = true;
            }
        }
        if (!$repl) $t->setReplaceWith($r);
        $r->setWriter($this->innerWriter);
    }    
    
}