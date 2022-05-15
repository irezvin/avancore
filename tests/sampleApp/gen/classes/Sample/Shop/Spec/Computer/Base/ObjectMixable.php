<?php

class Sample_Shop_Spec_Computer_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    var $_hasDefaults = true;

    var $_shopSpecComputerShopSpec = false;

    var $productId = NULL;

    var $hdd = 0;

    var $ram = 0;

    var $os = '';

    protected $preserveMetaCache = true;
    
    /**
     * @var Sample_Shop_Spec_Computer_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Sample_Shop_Spec_Computer';
    
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
        return array_merge(parent::listOwnProperties(), [ 0 => 'shopSpecComputerShopSpec', ]);
    }
    
 
    protected function listOwnAssociations() {
        return [ 'shopSpecComputerShopSpec' => 'Sample_Shop_Spec', ];
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = [
            'shopSpecComputerShopSpec' => [
                'className' => 'Sample_Shop_Spec',
                'mapperClass' => 'Sample_Shop_Spec_Mapper',
                'otherModelIdInMethodsPrefix' => 'shopSpecComputer',

                'caption' => new Ac_Lang_String('sample_shop_spec_computer_shop_spec_computer_shop_spec'),
                'idPropertyName' => 'productId',
                'relationId' => '_shopSpecComputerShopSpec',
                'referenceVarName' => '_shopSpecComputerShopSpec',
            ],
            'productId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Spec_Mapper',
                ],
                'assocPropertyName' => 'shopSpecComputerShopSpec',

                'caption' => new Ac_Lang_String('sample_shop_spec_computer_product_id'),
            ],
            'hdd' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_spec_computer_hdd'),
            ],
            'ram' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_spec_computer_ram'),
            ],
            'os' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_shop_spec_computer_os'),
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Sample_Shop_Spec 
     */
    function getShopSpecComputerShopSpec() {
        if ($this->_shopSpecComputerShopSpec === false) {
            $this->mapper->loadShopSpecComputerShopSpecsFor($this->mixin);
            
        }
        return $this->_shopSpecComputerShopSpec;
    }
    
    /**
     * @param Sample_Shop_Spec $shopSpecComputerShopSpec 
     */
    function setShopSpecComputerShopSpec($shopSpecComputerShopSpec) {
        if ($shopSpecComputerShopSpec === false) $this->_shopSpecComputerShopSpec = false;
        elseif ($shopSpecComputerShopSpec === null) $this->_shopSpecComputerShopSpec = null;
        else {
            if (!is_a($shopSpecComputerShopSpec, 'Sample_Shop_Spec')) trigger_error('$shopSpecComputerShopSpec must be an instance of Sample_Shop_Spec', E_USER_ERROR);
            if (!is_object($this->_shopSpecComputerShopSpec) && !Ac_Util::sameObject($this->_shopSpecComputerShopSpec, $shopSpecComputerShopSpec)) { 
                $this->_shopSpecComputerShopSpec = $shopSpecComputerShopSpec;
            }
        }
    }
    
    function clearShopSpecComputerShopSpec() {
        $this->shopSpecComputerShopSpec = null;
    }

    /**
     * @return Sample_Shop_Spec  
     */
    function createShopSpecComputerShopSpec($values = array()) {
        $m = $this->getMapper('Sample_Shop_Spec_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setShopSpecComputerShopSpec($res);
        return $res;
    }

    
  
    
}

