<?php

class Ac_Result_Stage_Write extends Ac_Result_Stage_Morph {

    /**
     * @var bool
     */
    protected $writeRoot = true;

    /**
     * @var Ac_Result
     */
    protected $rootTarget = null;
    
    /**
     * @param bool $writeRoot
     */
    function setWriteRoot($writeRoot) {
        $this->writeRoot = $writeRoot;
    }

    /**
     * @return bool
     */
    function getWriteRoot() {
        return $this->writeRoot;
    }

    function setRootTarget(Ac_Result $rootTarget) {
        $this->rootTarget = $rootTarget;
    }

    /**
     * @return Ac_Result
     */
    function getRootTarget() {
        return $this->rootTarget;
    }    

    protected function writeOut($item) {
        if ((bool) $this->parent || $this->writeRoot) {
            $item->setMerged(true);
            $writer = $item->getWriter();
            $writer->setStage($this);
            $writer->setSource($item);
            $target = null;
            if ($this->parent) {
                $target = $this->parent;
            } elseif ($this->writeRoot) {
                $target = $this->rootTarget;
            }
            $writer->setTarget($target);
            if ($item instanceof Ac_Result && $item->getIsObsolete()) {
                trigger_error("Trying to echo an obsolete Result", E_USER_WARNING);
            }
            $res = $writer->write((bool) $target);
            if ($target) {
                if ($target === $this->parent) {
                    $target->replaceObjectInContent($item, $res);
                } else {
                    $target->put($res);
                }
            }
        }
    }
    
    protected function endItem($item) {
        parent::endItem($item);
        if ($item instanceof Ac_Result && (!$this->getCurrentProperty() || $this->getCurrentPropertyIsString())) {
            $this->writeOut($item);
        } else $this->renderIfNecessary ($item);
    }

    function write() {
        if ($this->isComplete) throw new Ac_E_InvalidUsage("write() already called; check with getIsComplete() first");
        $this->traverse();
    }
    
}