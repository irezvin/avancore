<?php

class Sample_Shop_Classifier_Type_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_shopClassifier = false;

    var $_shopClassifierCount = false;

    var $_shopClassifierLoaded = false;

    var $type = NULL;
    
    var $_mapperClass = 'Sample_Shop_Classifier_Type_Mapper';
    
    /**
     * @var Sample_Shop_Classifier_Type_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Shop_Classifier_Type_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), array ( 0 => 'shopClassifier', )));
    }
    
 
    protected function listOwnLists() {
        
        return array ( 'shopClassifier' => 'shopClassifier', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'shopClassifier' => 'Sample_Shop_Classifier', );
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = array (
            'shopClassifier' => array (
                'className' => 'Sample_Shop_Classifier',
                'mapperClass' => 'Sample_Shop_Classifier_Mapper',
                'caption' => new Ac_Lang_String('sample_shop_classifier_type_shop_classifier'),
                'relationId' => '_shopClassifier',
                'countVarName' => '_shopClassifierCount',
                'referenceVarName' => '_shopClassifier',
            ),
            'type' => array (
                'maxLength' => '16',
                'caption' => new Ac_Lang_String('sample_shop_classifier_type_type'),
            ),
        );
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }

    function countShopClassifier() {
        if (is_array($this->_shopClassifier)) return count($this->_shopClassifier);
        if ($this->_shopClassifierCount === false) {
            $this->mapper->loadAssocCountFor($this, '_shopClassifier');
        }
        return $this->_shopClassifierCount;
        
    }

    function listShopClassifier() {
        if (!$this->_shopClassifierLoaded) {
            $this->mapper->loadShopClassifierFor($this);
        }
        return array_keys($this->_shopClassifier);
    }
    
    /**
     * @return bool
     */
    function isShopClassifierLoaded() {
        return $this->_shopClassifierLoaded;
    }
    
    /**
     * @return Sample_Shop_Classifier 
     */
    function getShopClassifier($id) {
        if (!$this->_shopClassifierLoaded) {
            $this->mapper->loadShopClassifierFor($this);
        }
        
        if (!isset($this->_shopClassifier[$id])) trigger_error ('No such Shop classifier: \''.$id.'\'', E_USER_ERROR);
        return $this->_shopClassifier[$id];
    }
    
    /**
     * @return Sample_Shop_Classifier 
     */
    function getShopClassifierItem($id) {
        return $this->getShopClassifier($id);
    }
    
    /**
     * @param Sample_Shop_Classifier $shopClassifier 
     */
    function addShopClassifier($shopClassifier) {
        if (!is_a($shopClassifier, 'Sample_Shop_Classifier')) trigger_error('$shopClassifier must be an instance of Sample_Shop_Classifier', E_USER_ERROR);
        $this->listShopClassifier();
        $this->_shopClassifier[] = $shopClassifier;
        
        $shopClassifier->_shopClassifierType = $this;
        
    }

    /**
     * @return Sample_Shop_Classifier  
     */
    function createShopClassifier($values = array()) {
        $m = $this->getMapper('Sample_Shop_Classifier_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addShopClassifier($res);
        return $res;
    }
    
  
    
}

