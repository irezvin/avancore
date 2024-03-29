<?php
/**
 * @property Sample $app Access to App instance (via Mapper)
 */
class Sample_Person_Photo_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_person = false;

    var $_personAlbums = false;

    var $_personAlbumsCount = false;

    var $_personAlbumsLoaded = false;

    var $_personAlbumIds = false;

    var $_portraitPerson = false;

    var $_personPosts = false;

    var $_personPostsCount = false;

    var $_personPostsLoaded = false;

    var $photoId = NULL;

    var $personId = 0;

    var $filename = '';
    
    var $_mapperClass = 'Sample_Person_Photo_Mapper';
    
    /**
     * @var Sample_Person_Photo_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Person_Photo_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'person', 1 => 'personAlbums', 2 => 'personAlbumIds', 3 => 'portraitPerson', 4 => 'personPosts', ]));
    }
    
 
    protected function listOwnLists() {
        
        return [ 'personAlbums' => 'personAlbums', 'personPosts' => 'personPosts', ];
    }

    
 
    protected function listOwnAssociations() {
        return [ 'person' => 'Sample_Person', 'personAlbums' => 'Sample_Person_Album', 'portraitPerson' => 'Sample_Person', 'personPosts' => 'Sample_Person_Post', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'person' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',

                'caption' => new Ac_Lang_String('sample_person_photo_person'),
                'idPropertyName' => 'personId',
                'relationId' => '_person',
                'referenceVarName' => '_person',
            ],
            'personAlbums' => [
                'className' => 'Sample_Person_Album',
                'mapperClass' => 'Sample_Person_Album_Mapper',

                'caption' => new Ac_Lang_String('sample_person_photo_person_albums'),
                'idsPropertyName' => 'personAlbumIds',
                'relationId' => '_personAlbums',
                'countVarName' => '_personAlbumsCount',
                'nnIdsVarName' => '_personAlbumIds',
                'referenceVarName' => '_personAlbums',
            ],
            'personAlbumIds' => [
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Album_Mapper',
                ],
                'showInTable' => false,
                'assocPropertyName' => 'personAlbums',
            ],
            'portraitPerson' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'portrait',

                'caption' => new Ac_Lang_String('sample_person_photo_portrait_person'),
                'relationId' => '_portraitPerson',
                'referenceVarName' => '_portraitPerson',
            ],
            'personPosts' => [
                'className' => 'Sample_Person_Post',
                'mapperClass' => 'Sample_Person_Post_Mapper',

                'caption' => new Ac_Lang_String('sample_person_photo_person_posts'),
                'relationId' => '_personPosts',
                'countVarName' => '_personPostsCount',
                'referenceVarName' => '_personPosts',
            ],
            'photoId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_person_photo_photo_id'),
            ],
            'personId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ],
                'assocPropertyName' => 'person',

                'caption' => new Ac_Lang_String('sample_person_photo_person_id'),
            ],
            'filename' => [
                'maxLength' => '45',

                'caption' => new Ac_Lang_String('sample_person_photo_filename'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }
        
    
    /**
     * @return Sample_Person 
     */
    function getPerson() {
        if ($this->_person === false) {
            $this->mapper->loadPeopleFor($this);
            
        }
        return $this->_person;
    }
    
    /**
     * @param Sample_Person $person 
     */
    function setPerson($person) {
        if ($person === false) $this->_person = false;
        elseif ($person === null) $this->_person = null;
        else {
            if (!is_a($person, 'Sample_Person')) trigger_error('$person must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_person) && !Ac_Util::sameObject($this->_person, $person)) { 
                $this->_person = $person;
            }
        }
    }
    
    function clearPerson() {
        $this->person = null;
    }

    /**
     * @return Sample_Person  
     */
    function createPerson($values = array()) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setPerson($res);
        return $res;
    }

    

    function countPersonAlbums() {
        if (is_array($this->_personAlbums)) return count($this->_personAlbums);
        if ($this->_personAlbumsCount === false) {
            $this->mapper->loadAssocCountFor($this, '_personAlbums');
        }
        return $this->_personAlbumsCount;
        
    }

    function listPersonAlbums() {
        if (!$this->_personAlbumsLoaded) {
            $this->mapper->loadPersonAlbumsFor($this);
        }
        return array_keys($this->_personAlbums);
    }
    
    /**
     * @return bool
     */
    function isPersonAlbumsLoaded() {
        return $this->_personAlbumsLoaded;
    }
    
    /**
     * @return Sample_Person_Album 
     */
    function getPersonAlbum($id) {
        if (!$this->_personAlbumsLoaded) {
            $this->mapper->loadPersonAlbumsFor($this);
        }
        
        if (!isset($this->_personAlbums[$id])) trigger_error ('No such Person album: \''.$id.'\'', E_USER_ERROR);
        return $this->_personAlbums[$id];
    }
    
    /**
     * @return Sample_Person_Album 
     */
    function getPersonAlbumsItem($id) {
        return $this->getPersonAlbum($id);
    }
    
    /**
     * @return Sample_Person_Album[] 
     */
    function getAllPersonAlbums() {
        $res = [];
        foreach ($this->listPersonAlbums() as $id)
            $res[] = $this->getPersonAlbum($id);
        return $res;
    }
    
    /**
     * @param Sample_Person_Album $personAlbum 
     */
    function addPersonAlbum($personAlbum) {
        if (!is_a($personAlbum, 'Sample_Person_Album')) trigger_error('$personAlbum must be an instance of Sample_Person_Album', E_USER_ERROR);
        $this->listPersonAlbums();
        $this->_personAlbums[] = $personAlbum;
        
        if (is_array($personAlbum->_personPhotos) && !Ac_Util::sameInArray($this, $personAlbum->_personPhotos)) {
                $personAlbum->_personPhotos[] = $this;
        }
        
    }

    /**
     * @return Sample_Person_Album  
     */
    function createPersonAlbum($values = array()) {
        $m = $this->getMapper('Sample_Person_Album_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addPersonAlbum($res);
        return $res;
    }
    

    function getPersonAlbumIds() {
        if ($this->_personAlbumIds === false) {
            $this->mapper->loadPersonAlbumIdsFor($this);
        }
        return $this->_personAlbumIds;
    }
    
    function setPersonAlbumIds($personAlbumIds) {
        if (!is_array($personAlbumIds)) trigger_error('$personAlbumIds must be an array', E_USER_ERROR);
        $this->_personAlbumIds = $personAlbumIds;
        $this->_personAlbumsLoaded = false;
        $this->_personAlbums = false; 
    }
    
    function clearPersonAlbums() {
        $this->_personAlbums = array();
        $this->_personAlbumsLoaded = true;
        $this->_personAlbumIds = false;
    }               
        
    
    /**
     * @return Sample_Person 
     */
    function getPortraitPerson() {
        if ($this->_portraitPerson === false) {
            $this->mapper->loadPortraitPeopleFor($this);
            
        }
        return $this->_portraitPerson;
    }
    
    /**
     * @param Sample_Person $portraitPerson 
     */
    function setPortraitPerson($portraitPerson) {
        if ($portraitPerson === false) $this->_portraitPerson = false;
        elseif ($portraitPerson === null) $this->_portraitPerson = null;
        else {
            if (!is_a($portraitPerson, 'Sample_Person')) trigger_error('$portraitPerson must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_portraitPerson) && !Ac_Util::sameObject($this->_portraitPerson, $portraitPerson)) { 
                $this->_portraitPerson = $portraitPerson;
            }
        }
    }
    
    function clearPortraitPerson() {
        $this->portraitPerson = null;
    }

    /**
     * @return Sample_Person  
     */
    function createPortraitPerson($values = array()) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setPortraitPerson($res);
        return $res;
    }

    

    function countPersonPosts() {
        if (is_array($this->_personPosts)) return count($this->_personPosts);
        if ($this->_personPostsCount === false) {
            $this->mapper->loadAssocCountFor($this, '_personPosts');
        }
        return $this->_personPostsCount;
        
    }

    function listPersonPosts() {
        if (!$this->_personPostsLoaded) {
            $this->mapper->loadPersonPostsFor($this);
        }
        return array_keys($this->_personPosts);
    }
    
    /**
     * @return bool
     */
    function isPersonPostsLoaded() {
        return $this->_personPostsLoaded;
    }
    
    /**
     * @return Sample_Person_Post 
     */
    function getPersonPost($id) {
        if (!$this->_personPostsLoaded) {
            $this->mapper->loadPersonPostsFor($this);
        }
        
        if (!isset($this->_personPosts[$id])) trigger_error ('No such Person post: \''.$id.'\'', E_USER_ERROR);
        return $this->_personPosts[$id];
    }
    
    /**
     * @return Sample_Person_Post 
     */
    function getPersonPostsItem($id) {
        return $this->getPersonPost($id);
    }
    
    /**
     * @return Sample_Person_Post[] 
     */
    function getAllPersonPosts() {
        $res = [];
        foreach ($this->listPersonPosts() as $id)
            $res[] = $this->getPersonPost($id);
        return $res;
    }
    
    /**
     * @param Sample_Person_Post $personPost 
     */
    function addPersonPost($personPost) {
        if (!is_a($personPost, 'Sample_Person_Post')) trigger_error('$personPost must be an instance of Sample_Person_Post', E_USER_ERROR);
        $this->listPersonPosts();
        $this->_personPosts[] = $personPost;
        
        $personPost->_personPhoto = $this;
        
    }

    /**
     * @return Sample_Person_Post  
     */
    function createPersonPost($values = array()) {
        $m = $this->getMapper('Sample_Person_Post_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addPersonPost($res);
        return $res;
    }
    
  
    
}

