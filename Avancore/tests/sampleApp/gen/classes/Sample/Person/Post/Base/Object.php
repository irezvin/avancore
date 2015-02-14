<?php

class Sample_Person_Post_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $_person = false;
    public $_personPhoto = false;
    public $id = NULL;
    public $personId = NULL;
    public $photoId = NULL;
    public $title = '';
    public $content = NULL;
    public $pubId = NULL;
    
    var $_mapperClass = 'Sample_Person_Post_Mapper';
    
    /**
     * @var Sample_Person_Post_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Person_Post_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
 
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), array ( 0 => 'person', 1 => 'personPhoto', )));
    }
    
 
    protected function listOwnAssociations() {
        return array ( 'person' => 'Sample_Person', 'personPhoto' => 'Sample_Person_Photo', );
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
            'personPhoto' => array (
                'className' => 'Sample_Person_Photo',
                'mapperClass' => 'Sample_Person_Photo_Mapper',
                'caption' => 'Person photo',
                'relationId' => '_personPhoto',
                'referenceVarName' => '_personPhoto',
            ),
            'id' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Id',
            ),
            'personId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'isNullable' => true,
                'caption' => 'Person Id',
            ),
            'photoId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Person_Photo_Mapper',
                ),
                'objectPropertyName' => 'personPhoto',
                'isNullable' => true,
                'caption' => 'Photo Id',
            ),
            'title' => array (
                'maxLength' => '255',
                'isNullable' => true,
                'caption' => 'Title',
            ),
            'content' => array (
                'controlType' => 'textArea',
                'isNullable' => true,
                'caption' => 'Content',
            ),
            'pubId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Publish_ImplMapper',
                ),
                'objectPropertyName' => 'publish',
                'isNullable' => true,
                'caption' => 'Pub Id',
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
    function createPerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setPerson($res);
        return $res;
    }

    
        
    
    /**
     * @return Sample_Person_Photo 
     */
    function getPersonPhoto() {
        if ($this->_personPhoto === false) {
            $this->mapper->loadPersonPhotosFor($this);
            
        }
        return $this->_personPhoto;
    }
    
    /**
     * @param Sample_Person_Photo $personPhoto 
     */
    function setPersonPhoto($personPhoto) {
        if ($personPhoto === false) $this->_personPhoto = false;
        elseif ($personPhoto === null) $this->_personPhoto = null;
        else {
            if (!is_a($personPhoto, 'Sample_Person_Photo')) trigger_error('$personPhoto must be an instance of Sample_Person_Photo', E_USER_ERROR);
            if (!is_object($this->_personPhoto) && !Ac_Util::sameObject($this->_personPhoto, $personPhoto)) { 
                $this->_personPhoto = $personPhoto;
            }
        }
    }
    
    function clearPersonPhoto() {
        $this->personPhoto = null;
    }

    /**
     * @return Sample_Person_Photo  
     */
    function createPersonPhoto($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Photo_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setPersonPhoto($res);
        return $res;
    }

    
  
    
}

