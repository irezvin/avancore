<?php
/**
 * @property Sample $app Access to App instance (via Mapper)
 */
class Sample_Person_Album_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_person = false;

    var $_personPhotos = false;

    var $_personPhotosCount = false;

    var $_personPhotosLoaded = false;

    var $_personPhotoIds = false;

    var $albumId = NULL;

    var $personId = 0;

    var $albumName = '\'\'';
    
    var $_mapperClass = 'Sample_Person_Album_Mapper';
    
    /**
     * @var Sample_Person_Album_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Person_Album_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'person', 1 => 'personPhotos', 2 => 'personPhotoIds', ]));
    }
    
 
    protected function listOwnLists() {
        
        return [ 'personPhotos' => 'personPhotos', ];
    }

    
 
    protected function listOwnAssociations() {
        return [ 'person' => 'Sample_Person', 'personPhotos' => 'Sample_Person_Photo', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'person' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',

                'caption' => new Ac_Lang_String('sample_person_album_person'),
                'idPropertyName' => 'personId',
                'relationId' => '_person',
                'referenceVarName' => '_person',
            ],
            'personPhotos' => [
                'className' => 'Sample_Person_Photo',
                'mapperClass' => 'Sample_Person_Photo_Mapper',

                'caption' => new Ac_Lang_String('sample_person_album_person_photos'),
                'idsPropertyName' => 'personPhotoIds',
                'relationId' => '_personPhotos',
                'countVarName' => '_personPhotosCount',
                'nnIdsVarName' => '_personPhotoIds',
                'referenceVarName' => '_personPhotos',
            ],
            'personPhotoIds' => [
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Photo_Mapper',
                ],
                'showInTable' => false,
                'assocPropertyName' => 'personPhotos',
            ],
            'albumId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_person_album_album_id'),
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

                'caption' => new Ac_Lang_String('sample_person_album_person_id'),
            ],
            'albumName' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_person_album_album_name'),
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

    

    function countPersonPhotos() {
        if (is_array($this->_personPhotos)) return count($this->_personPhotos);
        if ($this->_personPhotosCount === false) {
            $this->mapper->loadAssocCountFor($this, '_personPhotos');
        }
        return $this->_personPhotosCount;
        
    }

    function listPersonPhotos() {
        if (!$this->_personPhotosLoaded) {
            $this->mapper->loadPersonPhotosFor($this);
        }
        return array_keys($this->_personPhotos);
    }
    
    /**
     * @return bool
     */
    function isPersonPhotosLoaded() {
        return $this->_personPhotosLoaded;
    }
    
    /**
     * @return Sample_Person_Photo 
     */
    function getPersonPhoto($id) {
        if (!$this->_personPhotosLoaded) {
            $this->mapper->loadPersonPhotosFor($this);
        }
        
        if (!isset($this->_personPhotos[$id])) trigger_error ('No such Person photo: \''.$id.'\'', E_USER_ERROR);
        return $this->_personPhotos[$id];
    }
    
    /**
     * @return Sample_Person_Photo 
     */
    function getPersonPhotosItem($id) {
        return $this->getPersonPhoto($id);
    }
    
    /**
     * @return Sample_Person_Photo[] 
     */
    function getAllPersonPhotos() {
        $res = [];
        foreach ($this->listPersonPhotos() as $id)
            $res[] = $this->getPersonPhoto($id);
        return $res;
    }
    
    /**
     * @param Sample_Person_Photo $personPhoto 
     */
    function addPersonPhoto($personPhoto) {
        if (!is_a($personPhoto, 'Sample_Person_Photo')) trigger_error('$personPhoto must be an instance of Sample_Person_Photo', E_USER_ERROR);
        $this->listPersonPhotos();
        $this->_personPhotos[] = $personPhoto;
        
        if (is_array($personPhoto->_personAlbums) && !Ac_Util::sameInArray($this, $personPhoto->_personAlbums)) {
                $personPhoto->_personAlbums[] = $this;
        }
        
    }

    /**
     * @return Sample_Person_Photo  
     */
    function createPersonPhoto($values = array()) {
        $m = $this->getMapper('Sample_Person_Photo_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addPersonPhoto($res);
        return $res;
    }
    

    function getPersonPhotoIds() {
        if ($this->_personPhotoIds === false) {
            $this->mapper->loadPersonPhotoIdsFor($this);
        }
        return $this->_personPhotoIds;
    }
    
    function setPersonPhotoIds($personPhotoIds) {
        if (!is_array($personPhotoIds)) trigger_error('$personPhotoIds must be an array', E_USER_ERROR);
        $this->_personPhotoIds = $personPhotoIds;
        $this->_personPhotosLoaded = false;
        $this->_personPhotos = false; 
    }
    
    function clearPersonPhotos() {
        $this->_personPhotos = array();
        $this->_personPhotosLoaded = true;
        $this->_personPhotoIds = false;
    }               
  
    
}

