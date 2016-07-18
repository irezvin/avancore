<?php

class Sample_Shop_Spec_Monitor_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    var $_hasDefaults = true;

    var $_monitorShopClassifier = false;

    var $_monitorShopSpecsCount = false;

    var $_monitorShopSpecsLoaded = false;

    var $productId = NULL;

    var $diagonal = 0;

    var $hRes = 0;

    var $vRes = 0;

    var $matrixTypeId = NULL;

    protected $preserveMetaCache = true;
    
    /**
     * @var Sample_Shop_Spec_Monitor_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Sample_Shop_Spec_Monitor';
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array ( 0 => 'monitorShopClassifier', ));
    }
 
    protected function listOwnLists() {
        
        return array ( 'monitorShopClassifier' => 'monitorShopSpecs', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'monitorShopClassifier' => 'Sample_Shop_Classifier', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'monitorShopClassifier' => array (
                'className' => 'Sample_Shop_Classifier',
                'mapperClass' => 'Sample_Shop_Classifier_Mapper',
                'otherModelIdInMethodsPrefix' => 'monitor',
                'caption' => new Ac_Lang_String('sample_shop_spec_monitor_monitor_shop_classifier'),
                'relationId' => '_monitorShopClassifier',
                'countVarName' => '_monitorShopSpecsCount',
                'referenceVarName' => '_monitorShopClassifier',
            ),
            'productId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Spec_Mapper',
                ),
                'caption' => new Ac_Lang_String('sample_shop_spec_monitor_product_id'),
            ),
            'diagonal' => array (
                'dataType' => 'float',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => new Ac_Lang_String('sample_shop_spec_monitor_diagonal'),
            ),
            'hRes' => array (
                'dataType' => 'int',
                'maxLength' => '5',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => new Ac_Lang_String('sample_shop_spec_monitor_h_res'),
            ),
            'vRes' => array (
                'dataType' => 'int',
                'maxLength' => '5',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => new Ac_Lang_String('sample_shop_spec_monitor_v_res'),
            ),
            'matrixTypeId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Classifier_Mapper',
                ),
                'objectPropertyName' => 'monitorShopClassifier',
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_shop_spec_monitor_matrix_type_id'),
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Sample_Shop_Classifier 
     */
    function getMonitorShopClassifier() {
        if ($this->_monitorShopClassifier === false) {
            $this->mapper->loadMonitorShopClassifierFor($this->mixin);
            
        }
        return $this->_monitorShopClassifier;
    }
    
    /**
     * @param Sample_Shop_Classifier $monitorShopClassifier 
     */
    function setMonitorShopClassifier($monitorShopClassifier) {
        if ($monitorShopClassifier === false) $this->_monitorShopClassifier = false;
        elseif ($monitorShopClassifier === null) $this->_monitorShopClassifier = null;
        else {
            if (!is_a($monitorShopClassifier, 'Sample_Shop_Classifier')) trigger_error('$monitorShopClassifier must be an instance of Sample_Shop_Classifier', E_USER_ERROR);
            if (!is_object($this->_monitorShopClassifier) && !Ac_Util::sameObject($this->_monitorShopClassifier, $monitorShopClassifier)) { 
                $this->_monitorShopClassifier = $monitorShopClassifier;
            }
        }
    }
    
    function clearMonitorShopClassifier() {
        $this->monitorShopClassifier = null;
    }

    /**
     * @return Sample_Shop_Classifier  
     */
    function createMonitorShopClassifier($values = array()) {
        $m = $this->getMapper('Sample_Shop_Classifier_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setMonitorShopClassifier($res);
        return $res;
    }

    
  
    
}

