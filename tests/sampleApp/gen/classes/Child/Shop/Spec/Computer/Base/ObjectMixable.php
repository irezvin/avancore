<?php

class Child_Shop_Spec_Computer_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    protected $preserveMetaCache = true;
    
    /**
     * @var Child_Shop_Spec_Computer_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Child_Shop_Spec_Computer';
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), []);
    }
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = [
            'shopSpecComputerShopSpec' => [
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
            'hdd' => [
                'caption' => 'Hdd',
            ],
            'ram' => [
                'caption' => 'Ram',
            ],
            'os' => [
                'caption' => 'Os',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Shop_Spec 
     */
    function getShopSpecComputerShopSpec() {
        return parent::getShopSpecComputerShopSpec();
    }
    
    /**
     * @param Child_Shop_Spec $shopSpecComputerShopSpec 
     */
    function setShopSpecComputerShopSpec($shopSpecComputerShopSpec) {
        if ($shopSpecComputerShopSpec && !is_a($shopSpecComputerShopSpec, 'Child_Shop_Spec')) 
            trigger_error('$shopSpecComputerShopSpec must be an instance of Child_Shop_Spec', E_USER_ERROR);
        return parent::setShopSpecComputerShopSpec($shopSpecComputerShopSpec);
    }
    
    /**
     * @return Child_Shop_Spec  
     */
    function createShopSpecComputerShopSpec($values = array()) {
        return parent::createShopSpecComputerShopSpec($values);
    }

    
  
    
}

