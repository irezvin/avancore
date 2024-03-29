<?php

class Sample_Shop_Spec_Laptop_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    var $_hasDefaults = true;

    var $_shopSpecLaptopShopSpec = false;

    var $productId = NULL;

    var $weight = 0;

    var $battery = '';

    protected $preserveMetaCache = true;
    
    /**
     * @var Sample_Shop_Spec_Laptop_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Sample_Shop_Spec_Laptop';
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), [ 0 => 'shopSpecLaptopShopSpec', ]);
    }
    
 
    protected function listOwnAssociations() {
        return [ 'shopSpecLaptopShopSpec' => 'Sample_Shop_Spec', ];
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = [
            'shopSpecLaptopShopSpec' => [
                'className' => 'Sample_Shop_Spec',
                'mapperClass' => 'Sample_Shop_Spec_Mapper',
                'otherModelIdInMethodsPrefix' => 'shopSpecLaptop',

                'caption' => new Ac_Lang_String('sample_shop_spec_laptop_shop_spec_laptop_shop_spec'),
                'idPropertyName' => 'productId',
                'relationId' => '_shopSpecLaptopShopSpec',
                'referenceVarName' => '_shopSpecLaptopShopSpec',
            ],
            'productId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Spec_Mapper',
                ],
                'assocPropertyName' => 'shopSpecLaptopShopSpec',

                'caption' => new Ac_Lang_String('sample_shop_spec_laptop_product_id'),
            ],
            'weight' => [
                'dataType' => 'float',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_spec_laptop_weight'),
            ],
            'battery' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_shop_spec_laptop_battery'),
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Sample_Shop_Spec 
     */
    function getShopSpecLaptopShopSpec() {
        if ($this->_shopSpecLaptopShopSpec === false) {
            $this->mapper->loadShopSpecLaptopShopSpecsFor($this->mixin);
            
        }
        return $this->_shopSpecLaptopShopSpec;
    }
    
    /**
     * @param Sample_Shop_Spec $shopSpecLaptopShopSpec 
     */
    function setShopSpecLaptopShopSpec($shopSpecLaptopShopSpec) {
        if ($shopSpecLaptopShopSpec === false) $this->_shopSpecLaptopShopSpec = false;
        elseif ($shopSpecLaptopShopSpec === null) $this->_shopSpecLaptopShopSpec = null;
        else {
            if (!is_a($shopSpecLaptopShopSpec, 'Sample_Shop_Spec')) trigger_error('$shopSpecLaptopShopSpec must be an instance of Sample_Shop_Spec', E_USER_ERROR);
            if (!is_object($this->_shopSpecLaptopShopSpec) && !Ac_Util::sameObject($this->_shopSpecLaptopShopSpec, $shopSpecLaptopShopSpec)) { 
                $this->_shopSpecLaptopShopSpec = $shopSpecLaptopShopSpec;
            }
        }
    }
    
    function clearShopSpecLaptopShopSpec() {
        $this->shopSpecLaptopShopSpec = null;
    }

    /**
     * @return Sample_Shop_Spec  
     */
    function createShopSpecLaptopShopSpec($values = array()) {
        $m = $this->getMapper('Sample_Shop_Spec_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setShopSpecLaptopShopSpec($res);
        return $res;
    }

    
  
    
}

