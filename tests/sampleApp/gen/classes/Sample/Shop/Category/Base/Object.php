<?php
/**
 * @property Sample $app Access to App instance (via Mapper)
 */
class Sample_Shop_Category_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_shopProducts = false;

    var $_shopProductsCount = false;

    var $_shopProductsLoaded = false;

    var $_shopProductIds = false;

    var $id = NULL;

    var $title = NULL;

    var $leftCol = 0;

    var $rightCol = 0;

    var $ignore = 0;

    var $parentId = NULL;

    var $ordering = 0;

    var $depth = 0;

    var $metaId = NULL;

    var $pubId = NULL;
    
    var $_mapperClass = 'Sample_Shop_Category_Mapper';
    
    /**
     * @var Sample_Shop_Category_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Shop_Category_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'shopProducts', 1 => 'shopProductIds', ]));
    }
    
 
    protected function listOwnLists() {
        
        return [ 'shopProducts' => 'shopProducts', ];
    }

    
 
    protected function listOwnAssociations() {
        return [ 'shopProducts' => 'Sample_Shop_Product', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'shopProducts' => [
                'className' => 'Sample_Shop_Product',
                'mapperClass' => 'Sample_Shop_Product_Mapper',

                'caption' => new Ac_Lang_String('sample_shop_category_shop_products'),
                'idsPropertyName' => 'shopProductIds',
                'relationId' => '_shopProducts',
                'countVarName' => '_shopProductsCount',
                'nnIdsVarName' => '_shopProductIds',
                'referenceVarName' => '_shopProducts',
            ],
            'shopProductIds' => [
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ],
                'showInTable' => false,
                'assocPropertyName' => 'shopProducts',
            ],
            'id' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_category_id'),
            ],
            'title' => [
                'maxLength' => '255',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_shop_category_title'),
            ],
            'leftCol' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_category_left_col'),
            ],
            'rightCol' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_category_right_col'),
            ],
            'ignore' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_category_ignore'),
            ],
            'parentId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_shop_category_parent_id'),
            ],
            'ordering' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_category_ordering'),
            ],
            'depth' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_category_depth'),
            ],
            'metaId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_shop_category_meta_id'),
            ],
            'pubId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',

                'dummyCaption' => '',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Publish_ImplMapper',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_shop_category_pub_id'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }

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
     * @return Sample_Shop_Product[] 
     */
    function getAllShopProducts() {
        $res = [];
        foreach ($this->listShopProducts() as $id)
            $res[] = $this->getShopProduct($id);
        return $res;
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
    function createShopProduct($values = array()) {
        $m = $this->getMapper('Sample_Shop_Product_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
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

