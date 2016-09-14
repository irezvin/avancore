<?php

class Child_Shop_Classifier_Base_Object extends Sample_Shop_Classifier {

    
    var $_mapperClass = 'Child_Shop_Classifier_Mapper';
    
    /**
     * @var Child_Shop_Classifier_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Shop_Classifier_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = array (
            'shopClassifierType' => array (
                'className' => 'Child_Shop_Classifier_Type',
                'mapperClass' => 'Child_Shop_Classifier_Type_Mapper',
                'caption' => 'Shop classifier type',
            ),
            'monitorShopSpecs' => array (
                'className' => 'Child_Shop_Spec',
                'mapperClass' => 'Child_Shop_Spec_Mapper',
                'caption' => 'Shop specs',
            ),
            'id' => array (
                'caption' => 'Id',
            ),
            'title' => array (
                'caption' => 'Title',
            ),
            'type' => array (
                'values' => array (
                    'mapperClass' => 'Child_Shop_Classifier_Type_Mapper',
                ),
                'caption' => 'Type',
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Shop_Classifier_Type 
     */
    function getShopClassifierType() {
        return parent::getShopClassifierType();
    }
    
    /**
     * @param Child_Shop_Classifier_Type $shopClassifierType 
     */
    function setShopClassifierType($shopClassifierType) {
        if ($shopClassifierType && !is_a($shopClassifierType, 'Child_Shop_Classifier_Type')) 
            trigger_error('$shopClassifierType must be an instance of Child_Shop_Classifier_Type', E_USER_ERROR);
        return parent::setShopClassifierType($shopClassifierType);
    }
    
    /**
     * @return Child_Shop_Classifier_Type  
     */
    function createShopClassifierType($values = array()) {
        return parent::createShopClassifierType($values);
    }

    
        
    
    /**
     * @return Child_Shop_Spec 
     */
    function getMonitorShopSpec($id) {
        return parent::getMonitorShopSpec($id);
    }
    
    /**
     * @return Child_Shop_Spec 
     */
    function getMonitorShopSpecsItem($id) {
        return parent::getMonitorShopSpecsItem($id);
    }
    
    /**
     * @param Child_Shop_Spec $monitorShopSpec 
     */
    function addMonitorShopSpec($monitorShopSpec) {
        if (!is_a($monitorShopSpec, 'Child_Shop_Spec'))
            trigger_error('$monitorShopSpec must be an instance of Child_Shop_Spec', E_USER_ERROR);
        return parent::addMonitorShopSpec($monitorShopSpec);
    }
    
    /**
     * @return Child_Shop_Spec  
     */
    function createMonitorShopSpec($values = array()) {
        return parent::createMonitorShopSpec($values);
    }

    

  
    
}

