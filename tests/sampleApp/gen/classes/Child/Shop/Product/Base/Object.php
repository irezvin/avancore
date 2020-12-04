<?php

class Child_Shop_Product_Base_Object extends Sample_Shop_Product {

    
    var $_mapperClass = 'Child_Shop_Product_Mapper';
    
    /**
     * @var Child_Shop_Product_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Shop_Product_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'shopCategories' => [
                'className' => 'Child_Shop_Category',
                'mapperClass' => 'Child_Shop_Category_Mapper',
                'caption' => 'Shop categories',
            ],
            'shopCategoryIds' => [
                'values' => [
                    'mapperClass' => 'Child_Shop_Category_Mapper',
                ],
            ],
            'referencedShopProducts' => [
                'className' => 'Child_Shop_Product',
                'mapperClass' => 'Child_Shop_Product_Mapper',
                'caption' => 'Shop products',
            ],
            'referencedShopProductIds' => [
                'values' => [
                    'mapperClass' => 'Child_Shop_Product_Mapper',
                ],
            ],
            'referencingShopProducts' => [
                'className' => 'Child_Shop_Product',
                'mapperClass' => 'Child_Shop_Product_Mapper',
                'caption' => 'Shop products',
            ],
            'referencingShopProductIds' => [
                'values' => [
                    'mapperClass' => 'Child_Shop_Product_Mapper',
                ],
            ],
            'shopSpec' => [
                'className' => 'Child_Shop_Spec',
                'mapperClass' => 'Child_Shop_Spec_Mapper',
                'caption' => 'Shop spec',
            ],
            'id' => [
                'caption' => 'Id',
            ],
            'sku' => [
                'caption' => 'Sku',
            ],
            'title' => [
                'caption' => 'Title',
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
            'notePerson' => [
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
                'caption' => 'People',
            ],
            'productId' => [
                'values' => [
                    'mapperClass' => 'Child_Shop_Product_Mapper',
                ],
                'caption' => 'Product Id',
            ],
            'note' => [
                'caption' => 'Note',
            ],
            'noteAuthorId' => [

                'dummyCaption' => '',
                'values' => [
                    'mapperClass' => 'Child_Person_Mapper',
                ],
                'caption' => 'Note Author Id',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Shop_Category 
     */
    function getShopCategory($id) {
        return parent::getShopCategory($id);
    }
    
    /**
     * @return Child_Shop_Category 
     */
    function getShopCategoriesItem($id) {
        return parent::getShopCategoriesItem($id);
    }
    
    /**
     * @param Child_Shop_Category $shopCategory 
     */
    function addShopCategory($shopCategory) {
        if (!is_a($shopCategory, 'Child_Shop_Category'))
            trigger_error('$shopCategory must be an instance of Child_Shop_Category', E_USER_ERROR);
        return parent::addShopCategory($shopCategory);
    }
    
    /**
     * @return Child_Shop_Category  
     */
    function createShopCategory($values = array()) {
        return parent::createShopCategory($values);
    }

    

        
    
    /**
     * @return Child_Shop_Product 
     */
    function getReferencedShopProduct($id) {
        return parent::getReferencedShopProduct($id);
    }
    
    /**
     * @return Child_Shop_Product 
     */
    function getReferencedShopProductsItem($id) {
        return parent::getReferencedShopProductsItem($id);
    }
    
    /**
     * @param Child_Shop_Product $referencedShopProduct 
     */
    function addReferencedShopProduct($referencedShopProduct) {
        if (!is_a($referencedShopProduct, 'Child_Shop_Product'))
            trigger_error('$referencedShopProduct must be an instance of Child_Shop_Product', E_USER_ERROR);
        return parent::addReferencedShopProduct($referencedShopProduct);
    }
    
    /**
     * @return Child_Shop_Product  
     */
    function createReferencedShopProduct($values = array()) {
        return parent::createReferencedShopProduct($values);
    }

    

        
    
    /**
     * @return Child_Shop_Product 
     */
    function getReferencingShopProduct($id) {
        return parent::getReferencingShopProduct($id);
    }
    
    /**
     * @return Child_Shop_Product 
     */
    function getReferencingShopProductsItem($id) {
        return parent::getReferencingShopProductsItem($id);
    }
    
    /**
     * @param Child_Shop_Product $referencingShopProduct 
     */
    function addReferencingShopProduct($referencingShopProduct) {
        if (!is_a($referencingShopProduct, 'Child_Shop_Product'))
            trigger_error('$referencingShopProduct must be an instance of Child_Shop_Product', E_USER_ERROR);
        return parent::addReferencingShopProduct($referencingShopProduct);
    }
    
    /**
     * @return Child_Shop_Product  
     */
    function createReferencingShopProduct($values = array()) {
        return parent::createReferencingShopProduct($values);
    }

    

        
    
    /**
     * @return Child_Shop_Spec 
     */
    function getShopSpec() {
        return parent::getShopSpec();
    }
    
    /**
     * @param Child_Shop_Spec $shopSpec 
     */
    function setShopSpec($shopSpec) {
        if ($shopSpec && !is_a($shopSpec, 'Child_Shop_Spec')) 
            trigger_error('$shopSpec must be an instance of Child_Shop_Spec', E_USER_ERROR);
        return parent::setShopSpec($shopSpec);
    }
    
    /**
     * @return Child_Shop_Spec  
     */
    function createShopSpec($values = array()) {
        return parent::createShopSpec($values);
    }

    
        
    
    /**
     * @return Child_Person 
     */
    function getNotePerson() {
        return parent::getNotePerson();
    }
    
    /**
     * @param Child_Person $notePerson 
     */
    function setNotePerson($notePerson) {
        if ($notePerson && !is_a($notePerson, 'Child_Person')) 
            trigger_error('$notePerson must be an instance of Child_Person', E_USER_ERROR);
        return parent::setNotePerson($notePerson);
    }
    
    /**
     * @return Child_Person  
     */
    function createNotePerson($values = array()) {
        return parent::createNotePerson($values);
    }

    
  
    
}

