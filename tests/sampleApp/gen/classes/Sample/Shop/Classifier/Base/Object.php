<?php

class Sample_Shop_Classifier_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_shopClassifierType = false;

    var $_monitorShopSpecs = false;

    var $_shopSpecsCount = false;

    var $_shopSpecsLoaded = false;

    var $id = NULL;

    var $title = '';

    var $type = '';
    
    var $_mapperClass = 'Sample_Shop_Classifier_Mapper';
    
    /**
     * @var Sample_Shop_Classifier_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Shop_Classifier_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'shopClassifierType', 1 => 'monitorShopSpecs', ]));
    }
    
 
    protected function listOwnLists() {
        
        return [ 'monitorShopSpecs' => 'shopSpecs', ];
    }

    
 
    protected function listOwnAssociations() {
        return [ 'shopClassifierType' => 'Sample_Shop_Classifier_Type', 'monitorShopSpecs' => 'Sample_Shop_Spec', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'shopClassifierType' => [
                'className' => 'Sample_Shop_Classifier_Type',
                'mapperClass' => 'Sample_Shop_Classifier_Type_Mapper',

                'caption' => new Ac_Lang_String('sample_shop_classifier_shop_classifier_type'),
                'relationId' => '_shopClassifierType',
                'referenceVarName' => '_shopClassifierType',
            ],
            'monitorShopSpecs' => [
                'className' => 'Sample_Shop_Spec',
                'mapperClass' => 'Sample_Shop_Spec_Mapper',
                'otherModelIdInMethodsPrefix' => 'monitor',

                'caption' => new Ac_Lang_String('sample_shop_classifier_monitor_shop_specs'),
                'relationId' => '_monitorShopSpecs',
                'countVarName' => '_shopSpecsCount',
                'referenceVarName' => '_monitorShopSpecs',
            ],
            'id' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_classifier_id'),
            ],
            'title' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_shop_classifier_title'),
            ],
            'type' => [
                'controlType' => 'selectList',
                'maxLength' => '16',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Classifier_Type_Mapper',
                ],
                'objectPropertyName' => 'shopClassifierType',

                'caption' => new Ac_Lang_String('sample_shop_classifier_type'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }
        
    
    /**
     * @return Sample_Shop_Classifier_Type 
     */
    function getShopClassifierType() {
        if ($this->_shopClassifierType === false) {
            $this->mapper->loadShopClassifierTypeFor($this);
            
        }
        return $this->_shopClassifierType;
    }
    
    /**
     * @param Sample_Shop_Classifier_Type $shopClassifierType 
     */
    function setShopClassifierType($shopClassifierType) {
        if ($shopClassifierType === false) $this->_shopClassifierType = false;
        elseif ($shopClassifierType === null) $this->_shopClassifierType = null;
        else {
            if (!is_a($shopClassifierType, 'Sample_Shop_Classifier_Type')) trigger_error('$shopClassifierType must be an instance of Sample_Shop_Classifier_Type', E_USER_ERROR);
            if (!is_object($this->_shopClassifierType) && !Ac_Util::sameObject($this->_shopClassifierType, $shopClassifierType)) { 
                $this->_shopClassifierType = $shopClassifierType;
            }
        }
    }
    
    function clearShopClassifierType() {
        $this->shopClassifierType = null;
    }

    /**
     * @return Sample_Shop_Classifier_Type  
     */
    function createShopClassifierType($values = array()) {
        $m = $this->getMapper('Sample_Shop_Classifier_Type_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setShopClassifierType($res);
        return $res;
    }

    

    function countMonitorShopSpecs() {
        if (is_array($this->_monitorShopSpecs)) return count($this->_monitorShopSpecs);
        return 0;
        
    }

    function listMonitorShopSpecs() {
        if (!is_array($this->_monitorShopSpecs)) $this->_monitorShopSpecs = array();
        return array_keys($this->_monitorShopSpecs);
    }
    
    /**
     * @return bool
     */
    function isMonitorShopSpecsLoaded() {
        return $this->_shopSpecsLoaded;
    }
    
    /**
     * @return Sample_Shop_Spec 
     */
    function getMonitorShopSpec($id) {
        
        if (!isset($this->_monitorShopSpecs[$id])) trigger_error ('No such Shop spec: \''.$id.'\'', E_USER_ERROR);
        return $this->_monitorShopSpecs[$id];
    }
    
    /**
     * @return Sample_Shop_Spec 
     */
    function getMonitorShopSpecsItem($id) {
        return $this->getMonitorShopSpec($id);
    }
    
    /**
     * @return Sample_Shop_Spec[] 
     */
    function getAllMonitorShopSpecs() {
        $res = [];
        foreach ($this->listMonitorShopSpecs() as $id)
            $res[] = $this->getMonitorShopSpec($id);
        return $res;
    }
    
    /**
     * @param Sample_Shop_Spec $monitorShopSpec 
     */
    function addMonitorShopSpec($monitorShopSpec) {
        if (!is_a($monitorShopSpec, 'Sample_Shop_Spec')) trigger_error('$monitorShopSpec must be an instance of Sample_Shop_Spec', E_USER_ERROR);
        $this->listMonitorShopSpecs();
        $this->_monitorShopSpecs[] = $monitorShopSpec;
        
        
    }

    /**
     * @return Sample_Shop_Spec  
     */
    function createMonitorShopSpec($values = array()) {
        $m = $this->getMapper('Sample_Shop_Spec_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addMonitorShopSpec($res);
        return $res;
    }
    
  
    
}

