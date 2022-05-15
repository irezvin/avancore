<?php

class Sample_Shop_Spec_Food_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    var $_hasDefaults = true;

    var $_shopSpecFoodShopSpec = false;

    var $productId = NULL;

    var $storageType = 'shelfStable';

    var $storageTerm = 0;

    var $storageTermUnit = 'days';

    protected $preserveMetaCache = true;
    
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
    function getApp() {
        return parent::getApp();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), [ 0 => 'shopSpecFoodShopSpec', ]);
    }
    
 
    protected function listOwnAssociations() {
        return [ 'shopSpecFoodShopSpec' => 'Sample_Shop_Spec', ];
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = [
            'shopSpecFoodShopSpec' => [
                'className' => 'Sample_Shop_Spec',
                'mapperClass' => 'Sample_Shop_Spec_Mapper',
                'otherModelIdInMethodsPrefix' => 'shopSpecFood',

                'caption' => new Ac_Lang_String('sample_shop_spec_food_shop_spec_food_shop_spec'),
                'idPropertyName' => 'productId',
                'relationId' => '_shopSpecFoodShopSpec',
                'referenceVarName' => '_shopSpecFoodShopSpec',
            ],
            'productId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Spec_Mapper',
                ],
                'assocPropertyName' => 'shopSpecFoodShopSpec',

                'caption' => new Ac_Lang_String('sample_shop_spec_food_product_id'),
            ],
            'storageType' => [
                'controlType' => 'selectList',
                'valueList' => [
                    'shelfStable' => 'shelfStable',
                    'frozen' => 'frozen',
                    'refrigerated' => 'refrigerated',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_shop_spec_food_storage_type'),
            ],
            'storageTerm' => [
                'dataType' => 'int',
                'maxLength' => '3',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_spec_food_storage_term'),
            ],
            'storageTermUnit' => [
                'controlType' => 'selectList',
                'valueList' => [
                    'days' => 'days',
                    'months' => 'months',
                    'years' => 'years',
                ],

                'caption' => new Ac_Lang_String('sample_shop_spec_food_storage_term_unit'),
            ],
        ];
    
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

