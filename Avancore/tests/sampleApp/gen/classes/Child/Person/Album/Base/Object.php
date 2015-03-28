<?php

class Child_Person_Album_Base_Object extends Sample_Person_Album {

    
    var $_mapperClass = 'Child_Person_Album_Mapper';
    
    /**
     * @var Child_Person_Album_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Person_Album_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'person' => array (
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
            ),
            'personPhotos' => array (
                'className' => 'Child_Person_Photo',
                'mapperClass' => 'Child_Person_Photo_Mapper',
            ),
            'personPhotoIds' => array (
                'values' => array (
                    'mapperClass' => 'Child_Person_Photo_Mapper',
                ),
            ),
            'personId' => array (
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
    function getPerson() {
        return parent::getPerson();
    }
    
    /**
     * @param Child_Person $person 
     */
    function setPerson($person) {
        if ($person && !is_a($person, 'Child_Person')) 
            trigger_error('$person must be an instance of Child_Person', E_USER_ERROR);
        return parent::setPerson($person);
    }
    
    /**
     * @return Child_Person  
     */
    function createPerson($values = array(), $isReference = false) {
        return parent::createPerson($values, $isReference);
    }

    
        
    
    /**
     * @return Child_Person_Photo 
     */
    function getPersonPhoto($id) {
        return parent::getPersonPhoto($id);
    }
    
    /**
     * @return Child_Person_Photo 
     */
    function getPersonPhotosItem($id) {
        return parent::getPersonPhotosItem($id);
    }
    
    /**
     * @param Child_Person_Photo $personPhoto 
     */
    function addPersonPhoto($personPhoto) {
        if (!is_a($personPhoto, 'Child_Person_Photo'))
            trigger_error('$personPhoto must be an instance of Child_Person_Photo', E_USER_ERROR);
        return parent::addPersonPhoto($personPhoto);
    }
    
    /**
     * @return Child_Person_Photo  
     */
    function createPersonPhoto($values = array(), $isReference = false) {
        return parent::createPersonPhoto($values, $isReference);
    }

    

  
    
}

