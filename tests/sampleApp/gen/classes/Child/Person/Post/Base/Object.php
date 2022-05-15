<?php
/**
 * @property Child $app Access to App instance (via Mapper)
 */
class Child_Person_Post_Base_Object extends Sample_Person_Post {

    
    var $_mapperClass = 'Child_Person_Post_Mapper';
    
    /**
     * @var Child_Person_Post_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Child_Person_Post_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'publish' => [
                'className' => 'Child_Publish',
                'mapperClass' => 'Child_Publish_ImplMapper',
                'caption' => 'Publish',
            ],
            'person' => [
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
                'caption' => 'People',
            ],
            'personPhoto' => [
                'className' => 'Child_Person_Photo',
                'mapperClass' => 'Child_Person_Photo_Mapper',
                'caption' => 'Person photo',
            ],
            'id' => [
                'caption' => 'Id',
            ],
            'personId' => [
                'caption' => 'Person Id',
            ],
            'photoId' => [

                'dummyCaption' => '',
                'values' => [
                    'mapperClass' => 'Child_Person_Photo_Mapper',
                ],
                'caption' => 'Photo Id',
            ],
            'title' => [
                'caption' => 'Title',
            ],
            'content' => [
                'caption' => 'Content',
            ],
            'pubId' => [

                'dummyCaption' => '',
                'values' => [
                    'mapperClass' => 'Child_Publish_ImplMapper',
                ],
                'caption' => 'Pub Id',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Publish 
     */
    function getPublish() {
        return parent::getPublish();
    }
    
    /**
     * @param Child_Publish $publish 
     */
    function setPublish($publish) {
        if ($publish && !is_a($publish, 'Child_Publish')) 
            trigger_error('$publish must be an instance of Child_Publish', E_USER_ERROR);
        return parent::setPublish($publish);
    }
    
    /**
     * @return Child_Publish  
     */
    function createPublish($values = array()) {
        return parent::createPublish($values);
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

