<?php
/**
 * @property Sample $app Access to App instance (via Mapper)
 */
class Sample_Shop_Spec_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_shopProduct = false;

    var $productId = NULL;

    var $detailsUrl = '';

    var $specsType = '';
    
    var $_mapperClass = 'Sample_Shop_Spec_Mapper';
    
    /**
     * @var Sample_Shop_Spec_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Shop_Spec_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'shopProduct', ]));
    }
    
    
 
    protected function listOwnAssociations() {
        return [ 'shopProduct' => 'Sample_Shop_Product', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'shopProduct' => [
                'className' => 'Sample_Shop_Product',
                'mapperClass' => 'Sample_Shop_Product_Mapper',

                'caption' => new Ac_Lang_String('sample_shop_spec_shop_product'),
                'idPropertyName' => 'productId',
                'relationId' => '_shopProduct',
                'referenceVarName' => '_shopProduct',
            ],
            'productId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ],
                'assocPropertyName' => 'shopProduct',

                'caption' => new Ac_Lang_String('sample_shop_spec_product_id'),
            ],
            'detailsUrl' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_shop_spec_details_url'),
            ],
            'specsType' => [
                'maxLength' => '40',

                'caption' => new Ac_Lang_String('sample_shop_spec_specs_type'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }
        
    
    /**
     * @return Sample_Shop_Product 
     */
    function getShopProduct() {
        if ($this->_shopProduct === false) {
            $this->mapper->loadShopProductsFor($this);
            
        }
        return $this->_shopProduct;
    }
    
    /**
     * @param Sample_Shop_Product $shopProduct 
     */
    function setShopProduct($shopProduct) {
        if ($shopProduct === false) $this->_shopProduct = false;
        elseif ($shopProduct === null) $this->_shopProduct = null;
        else {
            if (!is_a($shopProduct, 'Sample_Shop_Product')) trigger_error('$shopProduct must be an instance of Sample_Shop_Product', E_USER_ERROR);
            if (!is_object($this->_shopProduct) && !Ac_Util::sameObject($this->_shopProduct, $shopProduct)) { 
                $this->_shopProduct = $shopProduct;
            }
        }
    }
    
    function clearShopProduct() {
        $this->shopProduct = null;
    }

    /**
     * @return Sample_Shop_Product  
     */
    function createShopProduct($values = array()) {
        $m = $this->getMapper('Sample_Shop_Product_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setShopProduct($res);
        return $res;
    }

    
  
    
}

