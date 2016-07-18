<?php

class Child_Shop_Spec_Food_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    protected $preserveMetaCache = true;
    
    /**
     * @var Child_Shop_Spec_Food_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Child_Shop_Spec_Food';
    
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
        return array_merge(parent::listOwnProperties(), array ( ));
    }
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'shopSpecFoodShopSpec' => array (
                'className' => 'Child_Shop_Spec',
                'mapperClass' => 'Child_Shop_Spec_Mapper',
                'caption' => 'Shop spec',
            ),
            'productId' => array (
                'values' => array (
                    'mapperClass' => 'Child_Shop_Spec_Mapper',
                ),
                'caption' => 'Product Id',
            ),
            'storageType' => array (
                'caption' => 'Storage Type',
            ),
            'storageTerm' => array (
                'caption' => 'Storage Term',
            ),
            'storageTermUnit' => array (
                'caption' => 'Storage Term Unit',
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Shop_Spec 
     */
    function getShopSpecFoodShopSpec() {
        return parent::getShopSpecFoodShopSpec();
    }
    
    /**
     * @param Child_Shop_Spec $shopSpecFoodShopSpec 
     */
    function setShopSpecFoodShopSpec($shopSpecFoodShopSpec) {
        if ($shopSpecFoodShopSpec && !is_a($shopSpecFoodShopSpec, 'Child_Shop_Spec')) 
            trigger_error('$shopSpecFoodShopSpec must be an instance of Child_Shop_Spec', E_USER_ERROR);
        return parent::setShopSpecFoodShopSpec($shopSpecFoodShopSpec);
    }
    
    /**
     * @return Child_Shop_Spec  
     */
    function createShopSpecFoodShopSpec($values = array()) {
        return parent::createShopSpecFoodShopSpec($values);
    }

    
  
    
}

