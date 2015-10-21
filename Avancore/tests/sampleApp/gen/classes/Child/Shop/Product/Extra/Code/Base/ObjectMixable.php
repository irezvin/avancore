<?php

class Child_Shop_Product_Extra_Code_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {

    
    /**
     * @var Child_Shop_Product_Extra_Code_MapperMixable 
     */
    protected $mapperExtraTable = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array ( ));
    }
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'extraCodePerson' => array (
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
            ),
            'productId' => array (
                'values' => array (
                    'mapperClass' => 'Child_Shop_Product_Mapper',
                ),
            ),
            'responsiblePersonId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_Person_Mapper',
                ),
            ),
        );
    
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

