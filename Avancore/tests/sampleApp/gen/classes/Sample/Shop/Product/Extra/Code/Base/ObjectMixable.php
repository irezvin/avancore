<?php

class Sample_Shop_Product_Extra_Code_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {

    public $_hasDefaults = true;
    public $_extraCodeExtraCodePerson = false;
    public $_extraCodeShopProductsCount = false;
    public $_extraCodeShopProductsLoaded = false;
    public $productId = NULL;
    public $ean = '';
    public $asin = '';
    public $gtin = '';
    public $responsiblePersonId = NULL;
    
    /**
     * @var Sample_Shop_Product_Extra_Code_MapperMixable 
     */
    protected $mapperExtraTable = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array ( 0 => 'extraCodeExtraCodePerson', ));
    }
 
    protected function listOwnLists() {
        
        return array ( 'extraCodeExtraCodePerson' => 'extraCodeShopProducts', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'extraCodeExtraCodePerson' => 'Sample_Person', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'extraCodeExtraCodePerson' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'extraCode',
                'caption' => 'People',
                'relationId' => '_extraCodeExtraCodePerson',
                'countVarName' => '_extraCodeShopProductsCount',
                'referenceVarName' => '_extraCodeExtraCodePerson',
            ),
            'productId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ),
                'caption' => 'Product Id',
            ),
            'ean' => array (
                'maxLength' => '255',
                'caption' => 'Ean',
            ),
            'asin' => array (
                'maxLength' => '255',
                'caption' => 'Asin',
            ),
            'gtin' => array (
                'maxLength' => '255',
                'caption' => 'Gtin',
            ),
            'responsiblePersonId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'objectPropertyName' => 'extraCodeExtraCodePerson',
                'isNullable' => true,
                'caption' => 'Responsible Person Id',
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Sample_Person 
     */
    function getExtraCodePerson() {
        if ($this->_extraCodeExtraCodePerson === false) {
            $this->mapper->loadExtraCodePeopleFor($this->mixin);
            
        }
        return $this->_extraCodeExtraCodePerson;
    }
    
    /**
     * @param Sample_Person $extraCodePerson 
     */
    function setExtraCodePerson($extraCodePerson) {
        if ($extraCodePerson === false) $this->_extraCodeExtraCodePerson = false;
        elseif ($extraCodePerson === null) $this->_extraCodeExtraCodePerson = null;
        else {
            if (!is_a($extraCodePerson, 'Sample_Person')) trigger_error('$extraCodePerson must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_extraCodeExtraCodePerson) && !Ac_Util::sameObject($this->_extraCodeExtraCodePerson, $extraCodePerson)) { 
                $this->_extraCodeExtraCodePerson = $extraCodePerson;
            }
        }
    }
    
    function clearExtraCodePerson() {
        $this->extraCodePerson = null;
    }

    /**
     * @return Sample_Person  
     */
    function createExtraCodePerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setExtraCodePerson($res);
        return $res;
    }

    
  
    
}

