<?php

class Child_Shop_Category_Base_Object extends Sample_Shop_Category {

    
    var $_mapperClass = 'Child_Shop_Category_Mapper';
    
    /**
     * @var Child_Shop_Category_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Shop_Category_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'shopProducts' => [
                'className' => 'Child_Shop_Product',
                'mapperClass' => 'Child_Shop_Product_Mapper',
                'caption' => 'Shop products',
            ],
            'shopProductIds' => [
                'values' => [
                    'mapperClass' => 'Child_Shop_Product_Mapper',
                ],
            ],
            'id' => [
                'caption' => 'Id',
            ],
            'title' => [
                'caption' => 'Title',
            ],
            'leftCol' => [
                'caption' => 'Left Col',
            ],
            'rightCol' => [
                'caption' => 'Right Col',
            ],
            'ignore' => [
                'caption' => 'Ignore',
            ],
            'parentId' => [
                'caption' => 'Parent Id',
            ],
            'ordering' => [
                'caption' => 'Ordering',
            ],
            'depth' => [
                'caption' => 'Depth',
            ],
            'metaId' => [
                'caption' => 'Meta Id',
            ],
            'pubId' => [

                'dummyCaption' => '',
                'values' => [
                    'mapperClass' => 'Child_Publish_ImplMapper',
                ],
                'caption' => 'Pub Id',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Shop_Product 
     */
    function getShopProduct($id) {
        return parent::getShopProduct($id);
    }
    
    /**
     * @return Child_Shop_Product 
     */
    function getShopProductsItem($id) {
        return parent::getShopProductsItem($id);
    }
    
    /**
     * @param Child_Shop_Product $shopProduct 
     */
    function addShopProduct($shopProduct) {
        if (!is_a($shopProduct, 'Child_Shop_Product'))
            trigger_error('$shopProduct must be an instance of Child_Shop_Product', E_USER_ERROR);
        return parent::addShopProduct($shopProduct);
    }
    
    /**
     * @return Child_Shop_Product  
     */
    function createShopProduct($values = array()) {
        return parent::createShopProduct($values);
    }

    

  
    
}

