<?php

class Child_Shop_Classifier_Type_Base_Object extends Sample_Shop_Classifier_Type {

    
    var $_mapperClass = 'Child_Shop_Classifier_Type_Mapper';
    
    /**
     * @var Child_Shop_Classifier_Type_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Shop_Classifier_Type_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'shopClassifier' => [
                'className' => 'Child_Shop_Classifier',
                'mapperClass' => 'Child_Shop_Classifier_Mapper',
                'caption' => 'Shop classifier',
            ],
            'type' => [
                'caption' => 'Type',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Shop_Classifier 
     */
    function getShopClassifier($id) {
        return parent::getShopClassifier($id);
    }
    
    /**
     * @return Child_Shop_Classifier 
     */
    function getShopClassifierItem($id) {
        return parent::getShopClassifierItem($id);
    }
    
    /**
     * @param Child_Shop_Classifier $shopClassifier 
     */
    function addShopClassifier($shopClassifier) {
        if (!is_a($shopClassifier, 'Child_Shop_Classifier'))
            trigger_error('$shopClassifier must be an instance of Child_Shop_Classifier', E_USER_ERROR);
        return parent::addShopClassifier($shopClassifier);
    }
    
    /**
     * @return Child_Shop_Classifier  
     */
    function createShopClassifier($values = array()) {
        return parent::createShopClassifier($values);
    }

    

  
    
}

