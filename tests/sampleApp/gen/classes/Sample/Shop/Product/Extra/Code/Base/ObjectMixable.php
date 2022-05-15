<?php

class Sample_Shop_Product_Extra_Code_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    var $_hasDefaults = true;

    var $_extraCodePerson = false;

    var $productId = NULL;

    var $ean = '';

    var $asin = '';

    var $gtin = '';

    var $responsiblePersonId = NULL;

    protected $preserveMetaCache = true;
    
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
    function getApp() {
        return parent::getApp();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), [ 0 => 'extraCodePerson', ]);
    }
    
 
    protected function listOwnAssociations() {
        return [ 'extraCodePerson' => 'Sample_Person', ];
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = [
            'extraCodePerson' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'extraCode',

                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_extra_code_person'),
                'idPropertyName' => 'responsiblePersonId',
                'relationId' => '_extraCodePerson',
                'referenceVarName' => '_extraCodePerson',
            ],
            'productId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ],

                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_product_id'),
            ],
            'ean' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_ean'),
            ],
            'asin' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_asin'),
            ],
            'gtin' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_gtin'),
            ],
            'responsiblePersonId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',

                'dummyCaption' => '',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ],
                'assocPropertyName' => 'extraCodePerson',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_shop_product_extra_code_responsible_person_id'),
            ],
        ];
    
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

