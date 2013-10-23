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
     * @var Ac_Result_Stage_Render
     */
    protected $render = null;
    
    protected $defaultTraverseClasses = array ('Ac_Result', 'Ac_I_Deferred', 'Ac_I_StringObject_WithRender');
    
    protected $hasRendered = false;
    
    /**
     * @var array
     */
    protected $renderStagePrototype = array();
    
    /**
     * @var bool
     */
    protected $doRender = true;
    
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
        } elseif (!$this->hasRendered && $this->doRender && ($item instanceof Ac_I_Deferred || $item instanceof Ac_I_StringObject_WithRender)) {
            // let's initialize renderer only when we will encounter 
            $this->render = Ac_Prototyped::factory($this->renderStagePrototype, 'Ac_Result_Stage_Render');
            $this->hasRendered = true;
            $this->render->startAt($this);
            $this->render->renderDeferreds();
        }
    }

    function write() {
        if ($this->isComplete) throw new Ac_E_InvalidUsage("write() already called; check with getIsComplete() first");
        $this->traverse();
    }
    
    /**
     * @param bool $doRender
     */
    function setDoRender($doRender) {
        $this->doRender = $doRender;
    }

    /**
     * @return bool
     */
    function getDoRender() {
        return $this->doRender;
    }

    function setRenderStagePrototype(array $renderStagePrototype) {
        $this->renderStagePrototype = $renderStagePrototype;
    }

    /**
     * @return array
     */
    function getRenderStagePrototype() {
        return $this->renderStagePrototype;
    }    
    
}