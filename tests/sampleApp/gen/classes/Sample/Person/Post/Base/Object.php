<?php

class Sample_Person_Post_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_publish = false;

    var $_person = false;

    var $_personPhoto = false;

    var $id = NULL;

    var $personId = NULL;

    var $photoId = NULL;

    var $title = '';

    var $content = NULL;

    var $pubId = NULL;
    
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
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'publish', 1 => 'person', 2 => 'personPhoto', ]));
    }
    
    
 
    protected function listOwnAssociations() {
        return [ 'publish' => 'Sample_Publish', 'person' => 'Sample_Person', 'personPhoto' => 'Sample_Person_Photo', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'publish' => [
                'className' => 'Sample_Publish',
                'mapperClass' => 'Sample_Publish_ImplMapper',

                'caption' => new Ac_Lang_String('sample_person_post_publish'),
                'relationId' => '_publish',
                'referenceVarName' => '_publish',
            ],
            'person' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',

                'caption' => new Ac_Lang_String('sample_person_post_person'),
                'relationId' => '_person',
                'referenceVarName' => '_person',
            ],
            'personPhoto' => [
                'className' => 'Sample_Person_Photo',
                'mapperClass' => 'Sample_Person_Photo_Mapper',

                'caption' => new Ac_Lang_String('sample_person_post_person_photo'),
                'relationId' => '_personPhoto',
                'referenceVarName' => '_personPhoto',
            ],
            'id' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_person_post_id'),
            ],
            'personId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_person_post_person_id'),
            ],
            'photoId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',

                'dummyCaption' => '',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Photo_Mapper',
                ],
                'objectPropertyName' => 'personPhoto',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_person_post_photo_id'),
            ],
            'title' => [
                'maxLength' => '255',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_person_post_title'),
            ],
            'content' => [
                'controlType' => 'textArea',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_person_post_content'),
            ],
            'pubId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',

                'dummyCaption' => '',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Publish_ImplMapper',
                ],
                'objectPropertyName' => 'publish',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_person_post_pub_id'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }
        
    
    /**
     * @return Sample_Publish 
     */
    function getPublish() {
        if ($this->_publish === false) {
            $this->mapper->loadPublishFor($this);
            
        }
        return $this->_publish;
    }
    
    /**
     * @param Sample_Publish $publish 
     */
    function setPublish($publish) {
        if ($publish === false) $this->_publish = false;
        elseif ($publish === null) $this->_publish = null;
        else {
            if (!is_a($publish, 'Sample_Publish')) trigger_error('$publish must be an instance of Sample_Publish', E_USER_ERROR);
            if (!is_object($this->_publish) && !Ac_Util::sameObject($this->_publish, $publish)) { 
                $this->_publish = $publish;
            }
        }
    }
    
    function clearPublish() {
        $this->publish = null;
    }

    /**
     * @return Sample_Publish  
     */
    function createPublish($values = array()) {
        $m = $this->getMapper('Sample_Publish_ImplMapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setPublish($res);
        return $res;
    }

    
        
    
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
    function createPersonPhoto($values = array()) {
        $m = $this->getMapper('Sample_Person_Photo_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setPersonPhoto($res);
        return $res;
    }

    
  
    
}

