<?php

class Child_Shop_Spec_Base_Object extends Sample_Shop_Spec {

    
    var $_mapperClass = 'Child_Shop_Spec_Mapper';
    
    /**
     * @var Child_Shop_Spec_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Shop_Spec_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'shopProduct' => [
                'className' => 'Child_Shop_Product',
                'mapperClass' => 'Child_Shop_Product_Mapper',
                'caption' => 'Shop product',
            ],
            'productId' => [
                'values' => [
                    'mapperClass' => 'Child_Shop_Product_Mapper',
                ],
                'caption' => 'Product Id',
            ],
            'detailsUrl' => [
                'caption' => 'Details Url',
            ],
            'specsType' => [
                'caption' => 'Specs Type',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Shop_Product 
     */
    function getShopProduct() {
        return parent::getShopProduct();
    }
    
    /**
     * @param Child_Shop_Product $shopProduct 
     */
    function setShopProduct($shopProduct) {
        if ($shopProduct && !is_a($shopProduct, 'Child_Shop_Product')) 
            trigger_error('$shopProduct must be an instance of Child_Shop_Product', E_USER_ERROR);
        return parent::setShopProduct($shopProduct);
    }
    
    /**
     * @return Child_Shop_Product  
     */
    function createShopProduct($values = array()) {
        return parent::createShopProduct($values);
    }

    
  
    
}

