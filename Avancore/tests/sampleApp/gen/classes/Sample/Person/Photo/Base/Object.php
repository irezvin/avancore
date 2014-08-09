<?php

class Sample_Person_Photo_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $_person = false;
    public $_personAlbums = false;
    public $_personAlbumsCount = false;
    public $_personAlbumIds = false;
    public $_protraitPerson = false;
    public $photoId = NULL;
    public $personId = 0;
    public $filename = '';
    
    var $_mapperClass = 'Sample_Person_Photo_Mapper';
    
    /**
     * @var Sample_Person_Photo_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Person_Photo_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array ( 0 => 'person', 1 => 'personAlbums', 2 => 'personAlbumIds', 3 => 'protraitPerson', 4 => 'photoId', 5 => 'personId', 6 => 'filename', );
    }
 
    protected function listOwnLists() {
        
        return array ( 'personAlbums' => 'personAlbums', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'person' => 'Sample_Person', 'personAlbums' => 'Sample_Person_Album', 'protraitPerson' => 'Sample_Person', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
            'person' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'caption' => 'People',
                'relationId' => '_person',
                'referenceVarName' => '_person',
            ),
            'personAlbums' => array (
                'className' => 'Sample_Person_Album',
                'mapperClass' => 'Sample_Person_Album_Mapper',
                'caption' => 'Person albums',
                'relationId' => '_personAlbums',
                'countVarName' => '_personAlbumsCount',
                'nnIdsVarName' => '_personAlbumIds',
                'referenceVarName' => '_personAlbums',
            ),
            'personAlbumIds' => array (
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Person_Album_Mapper',
                ),
                'showInTable' => false,
            ),
            'protraitPerson' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'protrait',
                'caption' => 'People',
                'relationId' => '_protraitPerson',
                'referenceVarName' => '_protraitPerson',
            ),
            'photoId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Photo Id',
            ),
            'personId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'objectPropertyName' => 'person',
                'caption' => 'Person Id',
            ),
            'filename' => array (
                'maxLength' => '45',
                'caption' => 'Filename',
            ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }
        
    
    /**
     * @return Sample_Person 
     */
    function getPerson() {
        if ($this->_person === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_person');
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
    function createPerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setPerson($res);
        return $res;
    }
    

    function countPersonAlbums() {
        if (is_array($this->_personAlbums)) return count($this->_personAlbums);
        if ($this->_personAlbumsCount === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocCountFor($this, '_personAlbums');
        }
        return $this->_personAlbumsCount;
    }

    function listPersonAlbums() {
        if ($this->_personAlbums === false) {
            $mapper = $this->getMapper();
            $mapper->listAssocFor($this, '_personAlbums');
        }
        return array_keys($this->_personAlbums);
    }
    
    /**
     * @return Sample_Person_Album 
     */
    function getPersonAlbum($id) {
        if ($this->_personAlbums === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_personAlbums');
        }
        if (!isset($this->_personAlbums[$id])) trigger_error ('No such Person album: \''.$id.'\'', E_USER_ERROR);
        if ($this->_personAlbums[$id] === false) {
        }
        return $this->_personAlbums[$id];
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
    function createPersonAlbum($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Album_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addPersonAlbum($res);
        return $res;
    }
    

    function getPersonAlbumIds() {
        if ($this->_personAlbumIds === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocNNIdsFor($this, '_personAlbums');
        }
        return $this->_personAlbumIds;
    }
    
    function setPersonAlbumIds($personAlbumIds) {
        if (!is_array($personAlbumIds)) trigger_error('$personAlbumIds must be an array', E_USER_ERROR);
        $this->_personAlbumIds = $personAlbumIds;
        $this->_personAlbums = false; 
    }
    
    function clearPersonAlbums() {
        $this->_personAlbums = array();
        $this->_personAlbumIds = false;
    }               
        
    
    /**
     * @return Sample_Person 
     */
    function getProtraitPerson() {
        if ($this->_protraitPerson === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_protraitPerson');
        }
        return $this->_protraitPerson;
    }
    
    /**
     * @param Sample_Person $protraitPerson 
     */
    function setProtraitPerson($protraitPerson) {
        if ($protraitPerson === false) $this->_protraitPerson = false;
        elseif ($protraitPerson === null) $this->_protraitPerson = null;
        else {
            if (!is_a($protraitPerson, 'Sample_Person')) trigger_error('$protraitPerson must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_protraitPerson) && !Ac_Util::sameObject($this->_protraitPerson, $protraitPerson)) { 
                $this->_protraitPerson = $protraitPerson;
            }
        }
    }
    
    function clearProtraitPerson() {
        $this->protraitPerson = null;
    }
    
    /**
     * @return Sample_Person  
     */
    function createProtraitPerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setProtraitPerson($res);
        return $res;
    }
    
  

    function _storeReferencedRecords() {
        $res = parent::_storeReferencedRecords() !== false;
        $mapper = $this->getMapper();

        if (is_object($this->_person)) {
            $rel = $mapper->getRelation('_person');
            if (!$this->_autoStoreReferenced($this->_person, $rel->fieldLinks, 'person')) $res = false;
        }
 
        return $res;
    }

    function _storeNNRecords() {
        $res = parent::_storeNNRecords() !== false;
        $mapper = $this->getMapper();
        
        if (is_array($this->_personAlbums) || is_array($this->_personAlbumIds)) {
            $rel = $mapper->getRelation('_personAlbums');
            if (!$this->_autoStoreNNRecords($this->_personAlbums, $this->_personAlbumIds, $rel->fieldLinks, $rel->fieldLinks2, $rel->midTableName, 'personAlbums', $rel->midWhere)) 
                $res = false;
        }
            
        return $res; 
    }
 
    protected function intListReferenceFields() {
        $res = array (
            'personId' => '_person',
        );
        return $res;
    }
    
}

