<?php

class Sample_Shop_Product_Note_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $_person = false;
    public $_shopProduct = false;
    public $productId = NULL;
    public $note = '';
    public $noteAuthorId = NULL;
    
    var $_mapperClass = 'Sample_Shop_Product_Note_Mapper';
    
    /**
     * @var Sample_Shop_Product_Note_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Shop_Product_Note_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
 
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), array ( 0 => 'person', 1 => 'shopProduct', )));
    }
    
 
    protected function listOwnAssociations() {
        return array ( 'person' => 'Sample_Person', 'shopProduct' => 'Sample_Shop_Product', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
            'person' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'caption' => 'People',
                'relationId' => '_person',
                'referenceVarName' => '_person',
            ),
            'shopProduct' => array (
                'className' => 'Sample_Shop_Product',
                'mapperClass' => 'Sample_Shop_Product_Mapper',
                'caption' => 'Shop product',
                'relationId' => '_shopProduct',
                'referenceVarName' => '_shopProduct',
            ),
            'productId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '11',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ),
                'objectPropertyName' => 'shopProduct',
                'caption' => 'Product Id',
            ),
            'note' => array (
                'controlType' => 'textArea',
                'caption' => 'Note',
            ),
            'noteAuthorId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'objectPropertyName' => 'person',
                'isNullable' => true,
                'caption' => 'Note Author Id',
            ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    /**
     * @return Sample_Person 
     */
    function getPerson() {
        if ($this->_person === false) {
            $this->mapper->loadPeopleFor($this);
            
        }
        return $this->_person;
    }
    
    /**
     * @param Sample_Person $person 
     */
    function setPerson($person) {
        if ($person === false) $this->_person = false;
        elseif ($person === null) $this->_person = null;
        else {
            if (!is_a($person, 'Sample_Person')) trigger_error('$person must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_person) && !Ac_Util::sameObject($this->_person, $person)) { 
                $this->_person = $person;
            }
        }
    }
    
    function clearPerson() {
        $this->person = null;
    }

    /**
     * @return Sample_Person  
     */
    function createPerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setPerson($res);
        return $res;
    }

    
        
    
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
    function createShopProduct($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Shop_Product_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setShopProduct($res);
        return $res;
    }

    
  
    
}

