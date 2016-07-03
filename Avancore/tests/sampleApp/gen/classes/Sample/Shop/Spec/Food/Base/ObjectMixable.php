<?php

class Sample_Shop_Spec_Food_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {

    public $_hasDefaults = true;
    public $_shopSpecFoodShopSpec = false;
    public $productId = NULL;
    public $storageType = 'shelfStable';
    public $storageTerm = 0;
    public $storageTermUnit = 'days';
    
    /**
     * @var Sample_Shop_Spec_Food_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Sample_Shop_Spec_Food';
    
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
        return array_merge(parent::listOwnProperties(), array ( 0 => 'shopSpecFoodShopSpec', ));
    }
    
 
    protected function listOwnAssociations() {
        return array ( 'shopSpecFoodShopSpec' => 'Sample_Shop_Spec', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'shopSpecFoodShopSpec' => array (
                'className' => 'Sample_Shop_Spec',
                'mapperClass' => 'Sample_Shop_Spec_Mapper',
                'otherModelIdInMethodsPrefix' => 'shopSpecFood',
                'caption' => new Ac_Lang_String('sample_shop_spec_food_shop_spec_food_shop_spec'),
                'relationId' => '_shopSpecFoodShopSpec',
                'referenceVarName' => '_shopSpecFoodShopSpec',
            ),
            'productId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Spec_Mapper',
                ),
                'objectPropertyName' => 'shopSpecFoodShopSpec',
                'caption' => new Ac_Lang_String('sample_shop_spec_food_product_id'),
            ),
            'storageType' => array (
                'controlType' => 'selectList',
                'valueList' => array (
                    'shelfStable' => 'shelfStable',
                    'frozen' => 'frozen',
                    'refrigerated' => 'refrigerated',
                ),
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_shop_spec_food_storage_type'),
            ),
            'storageTerm' => array (
                'dataType' => 'int',
                'maxLength' => '3',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => new Ac_Lang_String('sample_shop_spec_food_storage_term'),
            ),
            'storageTermUnit' => array (
                'controlType' => 'selectList',
                'valueList' => array (
                    'days' => 'days',
                    'months' => 'months',
                    'years' => 'years',
                ),
                'caption' => new Ac_Lang_String('sample_shop_spec_food_storage_term_unit'),
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Sample_Shop_Spec 
     */
    function getShopSpecFoodShopSpec() {
        if ($this->_shopSpecFoodShopSpec === false) {
            $this->mapper->loadShopSpecFoodShopSpecsFor($this->mixin);
            
        }
        return $this->_shopSpecFoodShopSpec;
    }
    
    /**
     * @param Sample_Shop_Spec $shopSpecFoodShopSpec 
     */
    function setShopSpecFoodShopSpec($shopSpecFoodShopSpec) {
        if ($shopSpecFoodShopSpec === false) $this->_shopSpecFoodShopSpec = false;
        elseif ($shopSpecFoodShopSpec === null) $this->_shopSpecFoodShopSpec = null;
        else {
            if (!is_a($shopSpecFoodShopSpec, 'Sample_Shop_Spec')) trigger_error('$shopSpecFoodShopSpec must be an instance of Sample_Shop_Spec', E_USER_ERROR);
            if (!is_object($this->_shopSpecFoodShopSpec) && !Ac_Util::sameObject($this->_shopSpecFoodShopSpec, $shopSpecFoodShopSpec)) { 
                $this->_shopSpecFoodShopSpec = $shopSpecFoodShopSpec;
            }
        }
    }
    
    function clearShopSpecFoodShopSpec() {
        $this->shopSpecFoodShopSpec = null;
    }

    /**
     * @return Sample_Shop_Spec  
     */
    function createShopSpecFoodShopSpec($values = array()) {
        $m = $this->getMapper('Sample_Shop_Spec_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setShopSpecFoodShopSpec($res);
        return $res;
    }

    
  
    
}

