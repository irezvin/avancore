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
        } elseif ($item instanceof Ac_I_Result_AfterWrite) {
            /*$this->afterWrite[$item->getStringObjectMark()] = array(
                'position' => clone $this->position,
                'item' => $item,
            );*/
        } else $this->renderIfNecessary ($item);
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
            
            if (isset($this->afterWrite[''.$item])) { // found any AfterWrite renderers
                
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
    
}