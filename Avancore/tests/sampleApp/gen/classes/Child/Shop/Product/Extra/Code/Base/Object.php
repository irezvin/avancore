<?php

class Child_Shop_Product_Extra_Code_Base_Object extends Sample_Shop_Product_Extra_Code {

    
    var $_mapperClass = 'Child_Shop_Product_Extra_Code_Mapper';
    
    /**
     * @var Child_Shop_Product_Extra_Code_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Shop_Product_Extra_Code_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), array ( 0 => 'people', )));
    }
    
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'people' => array (
                'className' => 'Child_People',
                'mapperClass' => 'Child_People_Mapper',
                'caption' => 'People',
                'relationId' => '_people',
                'referenceVarName' => '_people',
            ),
            'productId' => array (
                'values' => array (
                    'mapperClass' => 'Child_Shop_Product_Mapper',
                ),
            ),
            'responsiblePersonId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_People_Mapper',
                ),
                'objectPropertyName' => 'people',
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_People 
     */
    function getPeople() {
        return parent::getPeople();
    }
    
    /**
     * @param Child_People $people 
     */
    function setPeople($people) {
        if ($people && !is_a($people, 'Child_People')) 
            trigger_error('$people must be an instance of Child_People', E_USER_ERROR);
        return parent::setPeople($people);
    }
    
    /**
     * @return Child_People  
     */
    function createPeople($values = array(), $isReference = false) {
        return parent::createPeople($values, $isReference);
    }

    
  
    
}

