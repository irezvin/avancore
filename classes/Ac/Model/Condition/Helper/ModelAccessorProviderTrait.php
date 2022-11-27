<?php

trait Ac_Model_Condition_Helper_ModelAccessorProviderTrait {
    
    /**
     * @var Ac_Model_Condition_Helper_ModelAccessorInterface
     */
    protected $modelAccessor = null;
    
    /**
     * @return Ac_Model_Condition_Helper_ModelAccessorInterface
     */
    function getModelAccessor() {
        return $this->modelAccessor;
    }
    
    function setModelAccessor($modelAccessor) {
        $this->modelAccessor = Ac_Prototyped::factory($modelAccessor, 'Ac_Model_Condition_Helper_ModelAccessorProviderInterface');
    }
    
}