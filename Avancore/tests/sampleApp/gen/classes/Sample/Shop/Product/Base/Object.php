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
        return array ( 0 => 'shopCategories', 1 => 'shopCategoryIds', 2 => 'id', 3 => 'sku', 4 => 'title', 5 => 'metaId', 6 => 'pubId', );
    }
 
    protected function listOwnLists() {
        
        return array ( 'shopCategories' => 'shopCategories', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'shopCategories' => 'Sample_Shop_Category', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
            'shopCategories' => array (
                'className' => 'Sample_Shop_Category',
                'mapperClass' => 'Sample_Shop_Category_Mapper',
                'caption' => 'Shop categories',
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
                    'class' => 'Ac_Model_Values_Records',
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
                'caption' => 'Id',
            ),
            'sku' => array (
                'maxLength' => '255',
                'caption' => 'Sku',
            ),
            'title' => array (
                'maxLength' => '255',
                'caption' => 'Title',
            ),
            'metaId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'isNullable' => true,
                'caption' => 'Meta Id',
            ),
            'pubId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Publish_ImplMapper',
                ),
                'isNullable' => true,
                'caption' => 'Pub Id',
            ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }

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
    function createShopCategory($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Shop_Category_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
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
  
    
}

