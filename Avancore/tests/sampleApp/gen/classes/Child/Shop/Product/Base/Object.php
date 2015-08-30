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
        if ($pi === false) $pi = array (
            'shopCategories' => array (
                'className' => 'Child_Shop_Category',
                'mapperClass' => 'Child_Shop_Category_Mapper',
            ),
            'shopCategoryIds' => array (
                'values' => array (
                    'mapperClass' => 'Child_Shop_Category_Mapper',
                ),
            ),
            'pubId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_Publish_ImplMapper',
                ),
            ),
            'notePerson' => array (
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
            ),
            'productId' => array (
                'values' => array (
                    'mapperClass' => 'Child_Shop_Product_Mapper',
                ),
            ),
            'noteAuthorId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_Person_Mapper',
                ),
            ),
        );
    
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

