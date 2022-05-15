<?php

class Child_Shop_Spec_Laptop_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    protected $preserveMetaCache = true;
    
    /**
     * @var Child_Shop_Spec_Laptop_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Child_Shop_Spec_Laptop';
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Child 
     */
    function getApp() {
        return parent::getApp();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), []);
    }
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = [
            'shopSpecLaptopShopSpec' => [
                'className' => 'Child_Shop_Spec',
                'mapperClass' => 'Child_Shop_Spec_Mapper',
                'caption' => 'Shop spec',
            ],
            'productId' => [
                'values' => [
                    'mapperClass' => 'Child_Shop_Spec_Mapper',
                ],
                'caption' => 'Product Id',
            ],
            'weight' => [
                'caption' => 'Weight',
            ],
            'battery' => [
                'caption' => 'Battery',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Shop_Spec 
     */
    function getShopSpecLaptopShopSpec() {
        return parent::getShopSpecLaptopShopSpec();
    }
    
    /**
     * @param Child_Shop_Spec $shopSpecLaptopShopSpec 
     */
    function setShopSpecLaptopShopSpec($shopSpecLaptopShopSpec) {
        if ($shopSpecLaptopShopSpec && !is_a($shopSpecLaptopShopSpec, 'Child_Shop_Spec')) 
            trigger_error('$shopSpecLaptopShopSpec must be an instance of Child_Shop_Spec', E_USER_ERROR);
        return parent::setShopSpecLaptopShopSpec($shopSpecLaptopShopSpec);
    }
    
    /**
     * @return Child_Shop_Spec  
     */
    function createShopSpecLaptopShopSpec($values = array()) {
        return parent::createShopSpecLaptopShopSpec($values);
    }

    
  
    
}

