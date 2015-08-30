<?php

class Child_Person_Photo_Base_Object extends Sample_Person_Photo {

    
    var $_mapperClass = 'Child_Person_Photo_Mapper';
    
    /**
     * @var Child_Person_Photo_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Person_Photo_Mapper 
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
            'personAlbums' => array (
                'className' => 'Child_Person_Album',
                'mapperClass' => 'Child_Person_Album_Mapper',
            ),
            'personAlbumIds' => array (
                'values' => array (
                    'mapperClass' => 'Child_Person_Album_Mapper',
                ),
            ),
            'portraitPerson' => array (
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
            ),
            'personPosts' => array (
                'className' => 'Child_Person_Post',
                'mapperClass' => 'Child_Person_Post_Mapper',
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
    function createPerson($values = array()) {
        return parent::createPerson($values);
    }

    
        
    
    /**
     * @return Child_Person_Album 
     */
    function getPersonAlbum($id) {
        return parent::getPersonAlbum($id);
    }
    
    /**
     * @return Child_Person_Album 
     */
    function getPersonAlbumsItem($id) {
        return parent::getPersonAlbumsItem($id);
    }
    
    /**
     * @param Child_Person_Album $personAlbum 
     */
    function addPersonAlbum($personAlbum) {
        if (!is_a($personAlbum, 'Child_Person_Album'))
            trigger_error('$personAlbum must be an instance of Child_Person_Album', E_USER_ERROR);
        return parent::addPersonAlbum($personAlbum);
    }
    
    /**
     * @return Child_Person_Album  
     */
    function createPersonAlbum($values = array()) {
        return parent::createPersonAlbum($values);
    }

    

        
    
    /**
     * @return Child_Person 
     */
    function getPortraitPerson() {
        return parent::getPortraitPerson();
    }
    
    /**
     * @param Child_Person $portraitPerson 
     */
    function setPortraitPerson($portraitPerson) {
        if ($portraitPerson && !is_a($portraitPerson, 'Child_Person')) 
            trigger_error('$portraitPerson must be an instance of Child_Person', E_USER_ERROR);
        return parent::setPortraitPerson($portraitPerson);
    }
    
    /**
     * @return Child_Person  
     */
    function createPortraitPerson($values = array()) {
        return parent::createPortraitPerson($values);
    }

    
        
    
    /**
     * @return Child_Person_Post 
     */
    function getPersonPost($id) {
        return parent::getPersonPost($id);
    }
    
    /**
     * @return Child_Person_Post 
     */
    function getPersonPostsItem($id) {
        return parent::getPersonPostsItem($id);
    }
    
    /**
     * @param Child_Person_Post $personPost 
     */
    function addPersonPost($personPost) {
        if (!is_a($personPost, 'Child_Person_Post'))
            trigger_error('$personPost must be an instance of Child_Person_Post', E_USER_ERROR);
        return parent::addPersonPost($personPost);
    }
    
    /**
     * @return Child_Person_Post  
     */
    function createPersonPost($values = array()) {
        return parent::createPersonPost($values);
    }

    

  
    
}

