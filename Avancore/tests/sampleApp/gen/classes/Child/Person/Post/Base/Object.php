<?php

class Child_Person_Post_Base_Object extends Sample_Person_Post {

    
    var $_mapperClass = 'Child_Person_Post_Mapper';
    
    /**
     * @var Child_Person_Post_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Person_Post_Mapper 
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
                'caption' => 'People',
            ),
            'personPhoto' => array (
                'className' => 'Child_Person_Photo',
                'mapperClass' => 'Child_Person_Photo_Mapper',
                'caption' => 'Person photo',
            ),
            'id' => array (
                'caption' => 'Id',
            ),
            'personId' => array (
                'caption' => 'Person Id',
            ),
            'photoId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_Person_Photo_Mapper',
                ),
                'caption' => 'Photo Id',
            ),
            'title' => array (
                'caption' => 'Title',
            ),
            'content' => array (
                'caption' => 'Content',
            ),
            'pubId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_Publish_ImplMapper',
                ),
                'caption' => 'Pub Id',
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
    function createPerson($values = array()) {
        return parent::createPerson($values);
    }

    
        
    
    /**
     * @return Child_Person_Photo 
     */
    function getPersonPhoto() {
        return parent::getPersonPhoto();
    }
    
    /**
     * @param Child_Person_Photo $personPhoto 
     */
    function setPersonPhoto($personPhoto) {
        if ($personPhoto && !is_a($personPhoto, 'Child_Person_Photo')) 
            trigger_error('$personPhoto must be an instance of Child_Person_Photo', E_USER_ERROR);
        return parent::setPersonPhoto($personPhoto);
    }
    
    /**
     * @return Child_Person_Photo  
     */
    function createPersonPhoto($values = array()) {
        return parent::createPersonPhoto($values);
    }

    
  
    
}

