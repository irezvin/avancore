<?php

class Child_Shop_Product_Extra_Code_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    protected $preserveMetaCache = true;
    
    /**
     * @var Child_Shop_Product_Extra_Code_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Child_Shop_Product_Extra_Code';
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Child 
     */
    function getApp() {
        return parent::getApp();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), []);
    }
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = [
            'extraCodePerson' => [
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
                'caption' => 'People',
            ],
            'productId' => [
                'values' => [
                    'mapperClass' => 'Child_Shop_Product_Mapper',
                ],
                'caption' => 'Product Id',
            ],
            'ean' => [
                'caption' => 'Ean',
            ],
            'asin' => [
                'caption' => 'Asin',
            ],
            'gtin' => [
                'caption' => 'Gtin',
            ],
            'responsiblePersonId' => [

                'dummyCaption' => '',
                'values' => [
                    'mapperClass' => 'Child_Person_Mapper',
                ],
                'caption' => 'Responsible Person Id',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Person 
     */
    function getExtraCodePerson() {
        return parent::getExtraCodePerson();
    }
    
    /**
     * @param Child_Person $extraCodePerson 
     */
    function setExtraCodePerson($extraCodePerson) {
        if ($extraCodePerson && !is_a($extraCodePerson, 'Child_Person')) 
            trigger_error('$extraCodePerson must be an instance of Child_Person', E_USER_ERROR);
        return parent::setExtraCodePerson($extraCodePerson);
    }
    
    /**
     * @return Child_Person  
     */
    function createExtraCodePerson($values = array()) {
        return parent::createExtraCodePerson($values);
    }

    
  
    
}

