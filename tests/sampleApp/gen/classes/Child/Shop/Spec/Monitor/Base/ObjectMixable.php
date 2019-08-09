<?php

class Child_Shop_Spec_Monitor_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    protected $preserveMetaCache = true;
    
    /**
     * @var Child_Shop_Spec_Monitor_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Child_Shop_Spec_Monitor';
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array ( ));
    }
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'monitorShopClassifier' => array (
                'className' => 'Child_Shop_Classifier',
                'mapperClass' => 'Child_Shop_Classifier_Mapper',
                'caption' => 'Shop classifier',
            ),
            'productId' => array (
                'values' => array (
                    'mapperClass' => 'Child_Shop_Spec_Mapper',
                ),
                'caption' => 'Product Id',
            ),
            'diagonal' => array (
                'caption' => 'Diagonal',
            ),
            'hRes' => array (
                'caption' => 'H Res',
            ),
            'vRes' => array (
                'caption' => 'V Res',
            ),
            'matrixTypeId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_Shop_Classifier_Mapper',
                ),
                'caption' => 'Matrix Type Id',
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Shop_Classifier 
     */
    function getMonitorShopClassifier() {
        return parent::getMonitorShopClassifier();
    }
    
    /**
     * @param Child_Shop_Classifier $monitorShopClassifier 
     */
    function setMonitorShopClassifier($monitorShopClassifier) {
        if ($monitorShopClassifier && !is_a($monitorShopClassifier, 'Child_Shop_Classifier')) 
            trigger_error('$monitorShopClassifier must be an instance of Child_Shop_Classifier', E_USER_ERROR);
        return parent::setMonitorShopClassifier($monitorShopClassifier);
    }
    
    /**
     * @return Child_Shop_Classifier  
     */
    function createMonitorShopClassifier($values = array()) {
        return parent::createMonitorShopClassifier($values);
    }

    
  
    
}

