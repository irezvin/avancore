<?php

class Ac_Result_Stage_Morph extends Ac_Result_Stage {
    
    /**
     * @var Ac_Result_Stage_Deferreds
     */
    protected $render = null;
    
    /**
     * @var array
     */
    protected $renderStagePrototype = array();
    
    protected $defaultTraverseClasses = array ('Ac_Result', 'Ac_I_Deferred', 'Ac_I_StringObject_WithRender');
    
    protected $hasRendered = false;
    
    /**
     * @var bool
     */
    protected $renderDeferreds = true;
    
    /**
     * @var bool
     */
    protected $isBeforeStore = false;

    /**
     * @return bool
     */
    function getIsBeforeStore() {
        return $this->isBeforeStore;
    }    
    
    /**
     * @param bool $renderDeferreds
     */
    function setRenderDeferreds($renderDeferreds) {
        $this->renderDeferreds = $renderDeferreds;
    }

    /**
     * @return bool
     */
    function getRenderDeferreds() {
        return $this->renderDeferreds;
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

    /**
     * @important MUST BE CALLED BY CONCRETE CLASSES in endItem()
     */
    protected function renderIfNecessary($item) {
        if (!$this->hasRendered && $this->renderDeferreds && ($item instanceof Ac_I_Deferred || $item instanceof Ac_I_StringObject_WithRender)) {
            // let's initialize renderer only when we will encounter 
            $this->hasRendered = true;
            $this->render = $this->createRender();
            $this->render->invoke();        
            $res = true;
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * @return Ac_Result_Stage_Deferreds
     */
    protected function createRender() {
        $res = Ac_Prototyped::factory($this->renderStagePrototype, 'Ac_Result_Stage_Deferreds');
        $res->setIsBeforeStore($this->getIsBeforeStore());
        $res->startAt($this);
        return $res;
    }
    
    
}