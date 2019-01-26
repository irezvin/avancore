<?php

class Ac_Result_Stage_Write extends Ac_Result_Stage_Morph {
    
    // is used to call special methods of the handlers
    protected $stageName = 'Write';    

    /**
     * @var bool
     */
    protected $writeRoot = true;

    /**
     * @var Ac_Result
     */
    protected $rootTarget = null;
    
    protected $afterWrite = array();
    
    protected $slotRenderers = array();

    function __construct(array $prototype = array()) {
        parent::__construct($prototype);
        $this->defaultTraverseClasses[] = 'Ac_I_Result_AfterWrite';
    }
    
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

    protected function beginItem($item) {
        if ($item instanceof Ac_I_Result_AfterWrite) {
            $this->afterWrite[($rId = $this->getCurrentResult().'')][$iId = $item->getStringObjectMark()] = array(
                'position' => clone $this->position,
                'stack' => $this->pushStack(true),
                'item' => $item,
            );
            if ($item instanceof Ac_I_Result_SlotRenderer) {
                $this->slotRenderers[$rId][$item->getSlotId()] = $iId;
            }
        }
        parent::beginItem($item);
    }
    
    protected function endItem($item) {
        parent::endItem($item);
        if ($item instanceof Ac_Result && (!$this->getCurrentProperty() || $this->getCurrentPropertyIsString())) {
            if ($item->getSlotId()) {
                $this->getParentResult()->addSlotContent($item);
                $this->getParentResult()->removeFromContent($item);
            } else {
                $this->writeOut($item);
            }
            $this->addSlotsToParent($item);
        } elseif (!($item instanceof Ac_I_Result_AfterWrite)) {
            $this->renderIfNecessary ($item);
        }
    }
    
    protected function addSlotsToParent($item) {
        $parent = $this->getParentResult();
        if (!$parent) $parent = $this->getRootTarget();
        if ($parent) {
            $rId = ''.$item;
            $sr = isset($this->slotRenderers[$rId])? $this->slotRenderers[$rId] : array();
            foreach ($item->getSlotContent() as $slotId => $items) {
                if (!isset($sr[$slotId])) {
                    foreach ($items as $item) $parent->addSlotContent ($item, $slotId);
                    $item->setSlotContent(array(), $slotId);
                }
            }
            if ($sr) unset($this->slotRenderers[$rId]);
        }
    }
    
    protected function writeOut($item) {
        if (!$this->parent && !$this->writeRoot) return;
        
        if ($item instanceof Ac_Result && $item->getIsObsolete()) {
            trigger_error("Trying to echo an obsolete Result", E_USER_WARNING);
        }
        
        $writer = $item->getWriter();
        $writer->setStage($this);
        $writer->setSource($item);
        
        if ($this->parent) {
            $target = $this->parent;
        } else {
            $target = $this->rootTarget;
        }
        
        $writer->setTarget($target);
        
        $item->setMerged(true);
        
        if (isset($this->afterWrite[''.$item])) {
            $this->applyAfterWriteRenderers($item);
        }
        
        if (!$target) {
            $writer->write();
            return;
        }

        $buf = $writer->write(true);
        
        if ($target === $this->parent) {
            $target->replaceObjectInContent($item, $buf);
        } else {
            $target->put($buf);
        }
        
    }
    
    protected function applyAfterWriteRenderers($item) {

        $afterWrite = $this->afterWrite[''.$item];
        $this->afterWrite[''.$item] = array();

        $this->pushStack();

        $myCurrent = $this->getCurrentResult();

        foreach ($afterWrite as $mark => $info) {

            $this->popStack($info['stack']);

            $this->position = $info['position'];
            ob_start();
            $info['item']->render($this);
            $this->replaceCurrentObject(ob_get_clean());
            $this->position->advance();
            while ($this->getCurrentResult() !== $myCurrent) {
                $this->traverseNext();
            }
        }

        $this->popStack();
        
    }
    
}