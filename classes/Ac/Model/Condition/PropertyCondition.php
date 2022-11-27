<?php

class Ac_Model_Condition_PropertyCondition extends Ac_Model_Condition_MultiCondition
    implements Ac_Model_Condition_Helper_ModelAccessorProviderInterface {
    
    use Ac_Model_Condition_Helper_ModelAccessorProviderTrait { 
        
        getModelAccessor as protected origGetModelAccessor; 
        
    }
    
    protected $defaultConditionsClass = Ac_Model_Condition_PropertyCondition::class;
    
    static $defaultAccessor = null;
    
    var $property = null;
    
    function getModelAccessor() {
        if ($this->modelAccessor) return $this->modelAccessor;
        $provider = $this->findParent(Ac_Model_Condition_Helper_ModelAccessorProviderInterface::class);
        if ($provider) return $provider->getModelAccessor();
        if (!self::$defaultAccessor) self::$defaultAccessor = new Ac_Model_Condition_Helper_GetObjectProperty;
        return self::$defaultAccessor;
    }
    
    function getPropertyValue($model, & $exists = null) {
        return $this->getModelAccessor()->getPropertyValue($model, $this->property, $exists);
    }
    
    function test($value) {
        if ($this->property === null) return parent::test($value);
        $propertyValue = $this->getPropertyValue($value);
        $res = parent::test($propertyValue);
        return $res;
    }
    
}