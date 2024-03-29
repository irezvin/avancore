<?php
/**
 * @property Sample $app Access to App instance (via Mapper)
 */
class Sample_Shop_Product_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_shopCategories = false;

    var $_shopCategoriesCount = false;

    var $_shopCategoriesLoaded = false;

    var $_shopCategoryIds = false;

    var $_referencedShopProducts = false;

    var $_referencedShopProductsCount = false;

    var $_referencedShopProductsLoaded = false;

    var $_referencedShopProductIds = false;

    var $_referencingShopProducts = false;

    var $_referencingShopProductsCount = false;

    var $_referencingShopProductsLoaded = false;

    var $_referencingShopProductIds = false;

    var $_shopSpec = false;

    var $id = NULL;

    var $sku = '';

    var $title = '';

    var $metaId = NULL;

    var $pubId = NULL;

    var $_notePerson = false;

    var $productId = NULL;

    var $note = '';

    var $noteAuthorId = NULL;
    
    var $_mapperClass = 'Sample_Shop_Product_Mapper';
    
    /**
     * @var Sample_Shop_Product_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Shop_Product_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'shopCategories', 1 => 'shopCategoryIds', 2 => 'referencedShopProducts', 3 => 'referencedShopProductIds', 4 => 'referencingShopProducts', 5 => 'referencingShopProductIds', 6 => 'shopSpec', 12 => 'notePerson', ]));
    }
    
 
    protected function listOwnLists() {
        
        return [ 'shopCategories' => 'shopCategories', 'referencedShopProducts' => 'referencedShopProducts', 'referencingShopProducts' => 'referencingShopProducts', ];
    }

    
 
    protected function listOwnAssociations() {
        return [ 'shopCategories' => 'Sample_Shop_Category', 'referencedShopProducts' => 'Sample_Shop_Product', 'referencingShopProducts' => 'Sample_Shop_Product', 'shopSpec' => 'Sample_Shop_Spec', 'notePerson' => 'Sample_Person', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'shopCategories' => [
                'className' => 'Sample_Shop_Category',
                'mapperClass' => 'Sample_Shop_Category_Mapper',

                'caption' => new Ac_Lang_String('sample_shop_product_shop_categories'),
                'idsPropertyName' => 'shopCategoryIds',
                'relationId' => '_shopCategories',
                'countVarName' => '_shopCategoriesCount',
                'nnIdsVarName' => '_shopCategoryIds',
                'referenceVarName' => '_shopCategories',
            ],
            'shopCategoryIds' => [
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Category_Mapper',
                ],
                'showInTable' => false,
                'assocPropertyName' => 'shopCategories',
            ],
            'referencedShopProducts' => [
                'className' => 'Sample_Shop_Product',
                'mapperClass' => 'Sample_Shop_Product_Mapper',
                'otherModelIdInMethodsPrefix' => 'referenced',

                'caption' => new Ac_Lang_String('sample_shop_product_referenced_shop_products'),
                'idsPropertyName' => 'referencedShopProductIds',
                'relationId' => '_referencedShopProducts',
                'countVarName' => '_referencedShopProductsCount',
                'nnIdsVarName' => '_referencedShopProductIds',
                'referenceVarName' => '_referencedShopProducts',
            ],
            'referencedShopProductIds' => [
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ],
                'showInTable' => false,
                'assocPropertyName' => 'referencedShopProducts',
            ],
            'referencingShopProducts' => [
                'className' => 'Sample_Shop_Product',
                'mapperClass' => 'Sample_Shop_Product_Mapper',
                'otherModelIdInMethodsPrefix' => 'referencing',

                'caption' => new Ac_Lang_String('sample_shop_product_referencing_shop_products'),
                'idsPropertyName' => 'referencingShopProductIds',
                'relationId' => '_referencingShopProducts',
                'countVarName' => '_referencingShopProductsCount',
                'nnIdsVarName' => '_referencingShopProductIds',
                'referenceVarName' => '_referencingShopProducts',
            ],
            'referencingShopProductIds' => [
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ],
                'showInTable' => false,
                'assocPropertyName' => 'referencingShopProducts',
            ],
            'shopSpec' => [
                'className' => 'Sample_Shop_Spec',
                'mapperClass' => 'Sample_Shop_Spec_Mapper',

                'caption' => new Ac_Lang_String('sample_shop_product_shop_spec'),
                'idPropertyName' => 'id',
                'relationId' => '_shopSpec',
                'referenceVarName' => '_shopSpec',
            ],
            'id' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_shop_product_id'),
            ],
            'sku' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_shop_product_sku'),
            ],
            'title' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_shop_product_title'),
            ],
            'metaId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_shop_product_meta_id'),
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

                'caption' => new Ac_Lang_String('sample_shop_product_pub_id'),
            ],
            'notePerson' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'note',

                'caption' => new Ac_Lang_String('sample_shop_product_note_person'),
                'idPropertyName' => 'noteAuthorId',
                'relationId' => '_notePerson',
                'referenceVarName' => '_notePerson',
            ],
            'productId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '11',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ],
                'assocPropertyName' => 'referencedShopProducts',

                'caption' => new Ac_Lang_String('sample_shop_product_product_id'),
            ],
            'note' => [
                'controlType' => 'textArea',

                'caption' => new Ac_Lang_String('sample_shop_product_note'),
            ],
            'noteAuthorId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',

                'dummyCaption' => '',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ],
                'assocPropertyName' => 'notePerson',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_shop_product_note_author_id'),
            ],
        ];
    
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
     * @return Sample_Shop_Category[] 
     */
    function getAllShopCategories() {
        $res = [];
        foreach ($this->listShopCategories() as $id)
            $res[] = $this->getShopCategory($id);
        return $res;
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

    function countReferencedShopProducts() {
        if (is_array($this->_referencedShopProducts)) return count($this->_referencedShopProducts);
        if ($this->_referencedShopProductsCount === false) {
            $this->mapper->loadAssocCountFor($this, '_referencedShopProducts');
        }
        return $this->_referencedShopProductsCount;
        
    }

    function listReferencedShopProducts() {
        if (!$this->_referencedShopProductsLoaded) {
            $this->mapper->loadReferencedShopProductsFor($this);
        }
        return array_keys($this->_referencedShopProducts);
    }
    
    /**
     * @return bool
     */
    function isReferencedShopProductsLoaded() {
        return $this->_referencedShopProductsLoaded;
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function getReferencedShopProduct($id) {
        if (!$this->_referencedShopProductsLoaded) {
            $this->mapper->loadReferencedShopProductsFor($this);
        }
        
        if (!isset($this->_referencedShopProducts[$id])) trigger_error ('No such Shop product: \''.$id.'\'', E_USER_ERROR);
        return $this->_referencedShopProducts[$id];
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function getReferencedShopProductsItem($id) {
        return $this->getReferencedShopProduct($id);
    }
    
    /**
     * @return Sample_Shop_Product[] 
     */
    function getAllReferencedShopProducts() {
        $res = [];
        foreach ($this->listReferencedShopProducts() as $id)
            $res[] = $this->getReferencedShopProduct($id);
        return $res;
    }
    
    /**
     * @param Sample_Shop_Product $referencedShopProduct 
     */
    function addReferencedShopProduct($referencedShopProduct) {
        if (!is_a($referencedShopProduct, 'Sample_Shop_Product')) trigger_error('$referencedShopProduct must be an instance of Sample_Shop_Product', E_USER_ERROR);
        $this->listReferencedShopProducts();
        $this->_referencedShopProducts[] = $referencedShopProduct;
        
        if (is_array($referencedShopProduct->_referencingShopProducts) && !Ac_Util::sameInArray($this, $referencedShopProduct->_referencingShopProducts)) {
                $referencedShopProduct->_referencingShopProducts[] = $this;
        }
        
    }

    /**
     * @return Sample_Shop_Product  
     */
    function createReferencedShopProduct($values = array()) {
        $m = $this->getMapper('Sample_Shop_Product_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addReferencedShopProduct($res);
        return $res;
    }
    

    function getReferencedShopProductIds() {
        if ($this->_referencedShopProductIds === false) {
            $this->mapper->loadReferencedShopProductIdsFor($this);
        }
        return $this->_referencedShopProductIds;
    }
    
    function setReferencedShopProductIds($referencedShopProductIds) {
        if (!is_array($referencedShopProductIds)) trigger_error('$referencedShopProductIds must be an array', E_USER_ERROR);
        $this->_referencedShopProductIds = $referencedShopProductIds;
        $this->_referencedShopProductsLoaded = false;
        $this->_referencedShopProducts = false; 
    }
    
    function clearReferencedShopProducts() {
        $this->_referencedShopProducts = array();
        $this->_referencedShopProductsLoaded = true;
        $this->_referencedShopProductIds = false;
    }               

    function countReferencingShopProducts() {
        if (is_array($this->_referencingShopProducts)) return count($this->_referencingShopProducts);
        if ($this->_referencingShopProductsCount === false) {
            $this->mapper->loadAssocCountFor($this, '_referencingShopProducts');
        }
        return $this->_referencingShopProductsCount;
        
    }

    function listReferencingShopProducts() {
        if (!$this->_referencingShopProductsLoaded) {
            $this->mapper->loadReferencingShopProductsFor($this);
        }
        return array_keys($this->_referencingShopProducts);
    }
    
    /**
     * @return bool
     */
    function isReferencingShopProductsLoaded() {
        return $this->_referencingShopProductsLoaded;
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function getReferencingShopProduct($id) {
        if (!$this->_referencingShopProductsLoaded) {
            $this->mapper->loadReferencingShopProductsFor($this);
        }
        
        if (!isset($this->_referencingShopProducts[$id])) trigger_error ('No such Shop product: \''.$id.'\'', E_USER_ERROR);
        return $this->_referencingShopProducts[$id];
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function getReferencingShopProductsItem($id) {
        return $this->getReferencingShopProduct($id);
    }
    
    /**
     * @return Sample_Shop_Product[] 
     */
    function getAllReferencingShopProducts() {
        $res = [];
        foreach ($this->listReferencingShopProducts() as $id)
            $res[] = $this->getReferencingShopProduct($id);
        return $res;
    }
    
    /**
     * @param Sample_Shop_Product $referencingShopProduct 
     */
    function addReferencingShopProduct($referencingShopProduct) {
        if (!is_a($referencingShopProduct, 'Sample_Shop_Product')) trigger_error('$referencingShopProduct must be an instance of Sample_Shop_Product', E_USER_ERROR);
        $this->listReferencingShopProducts();
        $this->_referencingShopProducts[] = $referencingShopProduct;
        
        if (is_array($referencingShopProduct->_referencedShopProducts) && !Ac_Util::sameInArray($this, $referencingShopProduct->_referencedShopProducts)) {
                $referencingShopProduct->_referencedShopProducts[] = $this;
        }
        
    }

    /**
     * @return Sample_Shop_Product  
     */
    function createReferencingShopProduct($values = array()) {
        $m = $this->getMapper('Sample_Shop_Product_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addReferencingShopProduct($res);
        return $res;
    }
    

    function getReferencingShopProductIds() {
        if ($this->_referencingShopProductIds === false) {
            $this->mapper->loadReferencingShopProductIdsFor($this);
        }
        return $this->_referencingShopProductIds;
    }
    
    function setReferencingShopProductIds($referencingShopProductIds) {
        if (!is_array($referencingShopProductIds)) trigger_error('$referencingShopProductIds must be an array', E_USER_ERROR);
        $this->_referencingShopProductIds = $referencingShopProductIds;
        $this->_referencingShopProductsLoaded = false;
        $this->_referencingShopProducts = false; 
    }
    
    function clearReferencingShopProducts() {
        $this->_referencingShopProducts = array();
        $this->_referencingShopProductsLoaded = true;
        $this->_referencingShopProductIds = false;
    }               
        
    
    /**
     * @return Sample_Shop_Spec 
     */
    function getShopSpec() {
        if ($this->_shopSpec === false) {
            $this->mapper->loadShopSpecsFor($this);
            
        }
        return $this->_shopSpec;
    }
    
    /**
     * @param Sample_Shop_Spec $shopSpec 
     */
    function setShopSpec($shopSpec) {
        if ($shopSpec === false) $this->_shopSpec = false;
        elseif ($shopSpec === null) $this->_shopSpec = null;
        else {
            if (!is_a($shopSpec, 'Sample_Shop_Spec')) trigger_error('$shopSpec must be an instance of Sample_Shop_Spec', E_USER_ERROR);
            if (!is_object($this->_shopSpec) && !Ac_Util::sameObject($this->_shopSpec, $shopSpec)) { 
                $this->_shopSpec = $shopSpec;
            }
        }
    }
    
    function clearShopSpec() {
        $this->shopSpec = null;
    }

    /**
     * @return Sample_Shop_Spec  
     */
    function createShopSpec($values = array()) {
        $m = $this->getMapper('Sample_Shop_Spec_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setShopSpec($res);
        return $res;
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

