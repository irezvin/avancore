<?php

class Ac_Result_SlotRenderer extends Ac_Prototyped implements Ac_I_Result_SlotRenderer {
    
    protected $stringObjectMark = false;
    
    protected $slotId = false;
    
    protected $contentIfEmpty = '';

    protected $before = '';

    protected $after = '';

    protected $separator = '\n';
    
    function setSlotId($slotId) {
        $this->slotId = $slotId;
    }

    function getSlotId() {
        return $this->slotId;
    }    
    
    // ---- Ac_I_Result_AfterWrite ----
    
    function render(Ac_Result_Stage_Write $stage) {
        if ($this->slotId === false) throw new Ac_E_InvalidUsage ("\$slotId must not be empty");
        $r = $stage->getCurrentResult();
        if (!$r) throw new Ac_E_InvalidUsage ("\$stage->getCurrentResult() must not be empty");
        $s = $r->getSlotContent($this->slotId);
        if ($s) {
            echo $this->before;
            echo implode($this->separator, $s);
            echo $this->after;
            $r->setSlotContent(array(), $this->slotId);
        } else {
            echo $this->contentIfEmpty;
        }
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

    function setContentIfEmpty($contentIfEmpty) {
        $this->contentIfEmpty = $contentIfEmpty;
    }

    function getContentIfEmpty() {
        return $this->contentIfEmpty;
    }

    function setBefore($before) {
        $this->before = $before;
    }

    function getBefore() {
        return $this->before;
    }

    function setAfter($after) {
        $this->after = $after;
    }

    function getAfter() {
        return $this->after;
    }

    function setSeparator($separator) {
        $this->separator = $separator;
    }

    function getSeparator() {
        return $this->separator;
    }    
    
}