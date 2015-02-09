<?php

class Sample_Shop_Category_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $_shopProducts = false;
    public $_shopProductsCount = false;
    public $_shopProductsLoaded = false;
    public $_shopProductIds = false;
    public $id = NULL;
    public $title = NULL;
    public $leftCol = 0;
    public $rightCol = 0;
    public $ignore = 0;
    public $parentId = NULL;
    public $ordering = 0;
    public $depth = 0;
    public $metaId = NULL;
    public $pubId = NULL;
    
    var $_mapperClass = 'Sample_Shop_Category_Mapper';
    
    /**
     * @var Sample_Shop_Category_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Shop_Category_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
 
    
    protected function listOwnProperties() {
        return array ( 0 => 'shopProducts', 1 => 'shopProductIds', 2 => 'id', 3 => 'title', 4 => 'leftCol', 5 => 'rightCol', 6 => 'ignore', 7 => 'parentId', 8 => 'ordering', 9 => 'depth', 10 => 'metaId', 11 => 'pubId', );
    }
 
    protected function listOwnLists() {
        
        return array ( 'shopProducts' => 'shopProducts', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'shopProducts' => 'Sample_Shop_Product', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
            'shopProducts' => array (
                'className' => 'Sample_Shop_Product',
                'mapperClass' => 'Sample_Shop_Product_Mapper',
                'caption' => 'Shop products',
                'relationId' => '_shopProducts',
                'countVarName' => '_shopProductsCount',
                'nnIdsVarName' => '_shopProductIds',
                'referenceVarName' => '_shopProducts',
            ),
            'shopProductIds' => array (
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
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
            'title' => array (
                'maxLength' => '255',
                'isNullable' => true,
                'caption' => 'Title',
            ),
            'leftCol' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Left Col',
            ),
            'rightCol' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Right Col',
            ),
            'ignore' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Ignore',
            ),
            'parentId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'isNullable' => true,
                'caption' => 'Parent Id',
            ),
            'ordering' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Ordering',
            ),
            'depth' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Depth',
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

    function countShopProducts() {
        if (is_array($this->_shopProducts)) return count($this->_shopProducts);
        if ($this->_shopProductsCount === false) {
            $this->mapper->loadAssocCountFor($this, '_shopProducts');
        }
        return $this->_shopProductsCount;
        
    }

    function listShopProducts() {
        if (!$this->_shopProductsLoaded) {
            $this->mapper->loadShopProductsFor($this);
        }
        return array_keys($this->_shopProducts);
    }
    
    /**
     * @return bool
     */
    function isShopProductsLoaded() {
        return $this->_shopProductsLoaded;
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function getShopProduct($id) {
        if (!$this->_shopProductsLoaded) {
            $this->mapper->loadShopProductsFor($this);
        }
        
        if (!isset($this->_shopProducts[$id])) trigger_error ('No such Shop product: \''.$id.'\'', E_USER_ERROR);
        return $this->_shopProducts[$id];
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function getShopProductsItem($id) {
        return $this->getShopProduct($id);
    }
    
    /**
     * @param Sample_Shop_Product $shopProduct 
     */
    function addShopProduct($shopProduct) {
        if (!is_a($shopProduct, 'Sample_Shop_Product')) trigger_error('$shopProduct must be an instance of Sample_Shop_Product', E_USER_ERROR);
        $this->listShopProducts();
        $this->_shopProducts[] = $shopProduct;
        
        if (is_array($shopProduct->_shopCategories) && !Ac_Util::sameInArray($this, $shopProduct->_shopCategories)) {
                $shopProduct->_shopCategories[] = $this;
        }
        
    }

    /**
     * @return Sample_Shop_Product  
     */
    function createShopProduct($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Shop_Product_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addShopProduct($res);
        return $res;
    }
    

    function getShopProductIds() {
        if ($this->_shopProductIds === false) {
            $this->mapper->loadShopProductIdsFor($this);
        }
        return $this->_shopProductIds;
    }
    
    function setShopProductIds($shopProductIds) {
        if (!is_array($shopProductIds)) trigger_error('$shopProductIds must be an array', E_USER_ERROR);
        $this->_shopProductIds = $shopProductIds;
        $this->_shopProductsLoaded = false;
        $this->_shopProducts = false; 
    }
    
    function clearShopProducts() {
        $this->_shopProducts = array();
        $this->_shopProductsLoaded = true;
        $this->_shopProductIds = false;
    }               
  
    
}

