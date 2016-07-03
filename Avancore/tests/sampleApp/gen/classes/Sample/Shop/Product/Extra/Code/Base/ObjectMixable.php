<?php

class Sample_Shop_Product_Extra_Code_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {

    public $_hasDefaults = true;
    public $_extraCodePerson = false;
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

    protected $mixableId = 'Sample_Shop_Product_Extra_Code';
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array ( 0 => 'extraCodePerson', ));
    }
 
    protected function listOwnLists() {
        
        return array ( 'extraCodePerson' => 'extraCodeShopProducts', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'extraCodePerson' => 'Sample_Person', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'extraCodePerson' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'extraCode',
                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_extra_code_person'),
                'relationId' => '_extraCodePerson',
                'countVarName' => '_extraCodeShopProductsCount',
                'referenceVarName' => '_extraCodePerson',
            ),
            'productId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ),
                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_product_id'),
            ),
            'ean' => array (
                'maxLength' => '255',
                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_ean'),
            ),
            'asin' => array (
                'maxLength' => '255',
                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_asin'),
            ),
            'gtin' => array (
                'maxLength' => '255',
                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_gtin'),
            ),
            'responsiblePersonId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'objectPropertyName' => 'extraCodePerson',
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_responsible_person_id'),
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Sample_Person 
     */
    function getExtraCodePerson() {
        if ($this->_extraCodePerson === false) {
            $this->mapper->loadExtraCodePeopleFor($this->mixin);
            
        }
        return $this->_extraCodePerson;
    }
    
    /**
     * @param Sample_Person $extraCodePerson 
     */
    function setExtraCodePerson($extraCodePerson) {
        if ($extraCodePerson === false) $this->_extraCodePerson = false;
        elseif ($extraCodePerson === null) $this->_extraCodePerson = null;
        else {
            if (!is_a($extraCodePerson, 'Sample_Person')) trigger_error('$extraCodePerson must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_extraCodePerson) && !Ac_Util::sameObject($this->_extraCodePerson, $extraCodePerson)) { 
                $this->_extraCodePerson = $extraCodePerson;
            }
        }
    }
    
    function clearExtraCodePerson() {
        $this->extraCodePerson = null;
    }

    /**
     * @return Sample_Person  
     */
    function createExtraCodePerson($values = array()) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setExtraCodePerson($res);
        return $res;
    }

    
  
    
}

