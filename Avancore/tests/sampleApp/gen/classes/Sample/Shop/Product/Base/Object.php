<?php

class Sample_Shop_Product_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $_shopCategories = false;
    public $_shopCategoriesCount = false;
    public $_shopCategoriesLoaded = false;
    public $_shopCategoryIds = false;
    public $id = NULL;
    public $sku = '';
    public $title = '';
    public $metaId = NULL;
    public $pubId = NULL;
    public $_notePerson = false;
    public $_noteShopProductsCount = false;
    public $_noteShopProductsLoaded = false;
    public $productId = NULL;
    public $note = '';
    public $noteAuthorId = NULL;
    
    var $_mapperClass = 'Sample_Shop_Product_Mapper';
    
    /**
     * @var Sample_Shop_Product_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Shop_Product_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), array ( 0 => 'shopCategories', 1 => 'shopCategoryIds', 7 => 'notePerson', )));
    }
    
 
    protected function listOwnLists() {
        
        return array ( 'shopCategories' => 'shopCategories', 'notePerson' => 'noteShopProducts', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'shopCategories' => 'Sample_Shop_Category', 'notePerson' => 'Sample_Person', );
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = array (
            'shopCategories' => array (
                'className' => 'Sample_Shop_Category',
                'mapperClass' => 'Sample_Shop_Category_Mapper',
                'caption' => new Ac_Lang_String('sample_shop_product_shop_categories'),
                'relationId' => '_shopCategories',
                'countVarName' => '_shopCategoriesCount',
                'nnIdsVarName' => '_shopCategoryIds',
                'referenceVarName' => '_shopCategories',
            ),
            'shopCategoryIds' => array (
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Category_Mapper',
                ),
                'showInTable' => false,
            ),
            'id' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => new Ac_Lang_String('sample_shop_product_id'),
            ),
            'sku' => array (
                'maxLength' => '255',
                'caption' => new Ac_Lang_String('sample_shop_product_sku'),
            ),
            'title' => array (
                'maxLength' => '255',
                'caption' => new Ac_Lang_String('sample_shop_product_title'),
            ),
            'metaId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_shop_product_meta_id'),
            ),
            'pubId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Publish_ImplMapper',
                ),
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_shop_product_pub_id'),
            ),
            'notePerson' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'note',
                'caption' => new Ac_Lang_String('sample_shop_product_note_person'),
                'relationId' => '_notePerson',
                'countVarName' => '_noteShopProductsCount',
                'referenceVarName' => '_notePerson',
            ),
            'productId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '11',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ),
                'caption' => new Ac_Lang_String('sample_shop_product_product_id'),
            ),
            'note' => array (
                'controlType' => 'textArea',
                'caption' => new Ac_Lang_String('sample_shop_product_note'),
            ),
            'noteAuthorId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'objectPropertyName' => 'notePerson',
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_shop_product_note_author_id'),
            ),
        );
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }

    function countShopCategories() {
        if (is_array($this->_shopCategories)) return count($this->_shopCategories);
        if ($this->_shopCategoriesCount === false) {
            $this->mapper->loadAssocCountFor($this, '_shopCategories');
        }
        return $this->_shopCategoriesCount;
        
    }

    function listShopCategories() {
        if (!$this->_shopCategoriesLoaded) {
            $this->mapper->loadShopCategoriesFor($this);
        }
        return array_keys($this->_shopCategories);
    }
    
    /**
     * @return bool
     */
    function isShopCategoriesLoaded() {
        return $this->_shopCategoriesLoaded;
    }
    
    /**
     * @return Sample_Shop_Category 
     */
    function getShopCategory($id) {
        if (!$this->_shopCategoriesLoaded) {
            $this->mapper->loadShopCategoriesFor($this);
        }
        
        if (!isset($this->_shopCategories[$id])) trigger_error ('No such Shop category: \''.$id.'\'', E_USER_ERROR);
        return $this->_shopCategories[$id];
    }
    
    /**
     * @return Sample_Shop_Category 
     */
    function getShopCategoriesItem($id) {
        return $this->getShopCategory($id);
    }
    
    /**
     * @param Sample_Shop_Category $shopCategory 
     */
    function addShopCategory($shopCategory) {
        if (!is_a($shopCategory, 'Sample_Shop_Category')) trigger_error('$shopCategory must be an instance of Sample_Shop_Category', E_USER_ERROR);
        $this->listShopCategories();
        $this->_shopCategories[] = $shopCategory;
        
        if (is_array($shopCategory->_shopProducts) && !Ac_Util::sameInArray($this, $shopCategory->_shopProducts)) {
                $shopCategory->_shopProducts[] = $this;
        }
        
    }

    /**
     * @return Sample_Shop_Category  
     */
    function createShopCategory($values = array()) {
        $m = $this->getMapper('Sample_Shop_Category_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addShopCategory($res);
        return $res;
    }
    

    function getShopCategoryIds() {
        if ($this->_shopCategoryIds === false) {
            $this->mapper->loadShopCategoryIdsFor($this);
        }
        return $this->_shopCategoryIds;
    }
    
    function setShopCategoryIds($shopCategoryIds) {
        if (!is_array($shopCategoryIds)) trigger_error('$shopCategoryIds must be an array', E_USER_ERROR);
        $this->_shopCategoryIds = $shopCategoryIds;
        $this->_shopCategoriesLoaded = false;
        $this->_shopCategories = false; 
    }
    
    function clearShopCategories() {
        $this->_shopCategories = array();
        $this->_shopCategoriesLoaded = true;
        $this->_shopCategoryIds = false;
    }               
        
    
    /**
     * @return Sample_Person 
     */
    function getNotePerson() {
        if ($this->_notePerson === false) {
            $this->mapper->loadNotePeopleFor($this);
            
        }
        return $this->_notePerson;
    }
    
    /**
     * @param Sample_Person $notePerson 
     */
    function setNotePerson($notePerson) {
        if ($notePerson === false) $this->_notePerson = false;
        elseif ($notePerson === null) $this->_notePerson = null;
        else {
            if (!is_a($notePerson, 'Sample_Person')) trigger_error('$notePerson must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_notePerson) && !Ac_Util::sameObject($this->_notePerson, $notePerson)) { 
                $this->_notePerson = $notePerson;
            }
        }
    }
    
    function clearNotePerson() {
        $this->notePerson = null;
    }

    /**
     * @return Sample_Person  
     */
    function createNotePerson($values = array()) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setNotePerson($res);
        return $res;
    }

    
  
    
}

