<?php

class Sample_Publish_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {

    public $_hasDefaults = true;
    public $_authorPerson = false;
    public $_editorPerson = false;
    public $id = NULL;
    public $sharedObjectType = '';
    public $published = 1;
    public $deleted = 0;
    public $publishUp = '0000-00-00 00:00:00';
    public $publishDown = '0000-00-00 00:00:00';
    public $authorId = NULL;
    public $editorId = NULL;
    public $pubChannelId = NULL;
    public $dateCreated = '0000-00-00 00:00:00';
    public $dateModified = '0000-00-00 00:00:00';
    public $dateDeleted = '0000-00-00 00:00:00';
    
    /**
     * @var Sample_Publish_MapperMixable 
     */
    protected $mapperExtraTable = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array ( 0 => 'authorPerson', 1 => 'editorPerson', ));
    }
    
 
    protected function listOwnAssociations() {
        return array ( 'authorPerson' => 'Sample_Person', 'editorPerson' => 'Sample_Person', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'authorPerson' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'author',
                'caption' => new Ac_Lang_String('sample_publish_author_person'),
                'relationId' => '_authorPerson',
                'referenceVarName' => '_authorPerson',
            ),
            'editorPerson' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'editor',
                'caption' => new Ac_Lang_String('sample_publish_editor_person'),
                'relationId' => '_editorPerson',
                'referenceVarName' => '_editorPerson',
            ),
            'id' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => new Ac_Lang_String('sample_publish_id'),
            ),
            'sharedObjectType' => array (
                'maxLength' => '50',
                'caption' => new Ac_Lang_String('sample_publish_shared_object_type'),
            ),
            'published' => array (
                'dataType' => 'bool',
                'controlType' => 'selectList',
                'maxLength' => '1',
                'valueList' => array (
                    0 => 'No',
                    1 => 'Yes',
                ),
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_publish_published'),
            ),
            'deleted' => array (
                'dataType' => 'bool',
                'controlType' => 'selectList',
                'maxLength' => '1',
                'valueList' => array (
                    0 => 'No',
                    1 => 'Yes',
                ),
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_publish_deleted'),
            ),
            'publishUp' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_publish_publish_up'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
            'publishDown' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_publish_publish_down'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
            'authorId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'objectPropertyName' => 'authorPerson',
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_publish_author_id'),
            ),
            'editorId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'objectPropertyName' => 'editorPerson',
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_publish_editor_id'),
            ),
            'pubChannelId' => array (
                'maxLength' => '255',
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_publish_pub_channel_id'),
            ),
            'dateCreated' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'caption' => new Ac_Lang_String('sample_publish_date_created'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
            'dateModified' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'caption' => new Ac_Lang_String('sample_publish_date_modified'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
            'dateDeleted' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'caption' => new Ac_Lang_String('sample_publish_date_deleted'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Sample_Person 
     */
    function getAuthorPerson() {
        if ($this->_authorPerson === false) {
            $this->mapper->loadAuthorPeopleFor($this->mixin);
            
        }
        return $this->_authorPerson;
    }
    
    /**
     * @param Sample_Person $authorPerson 
     */
    function setAuthorPerson($authorPerson) {
        if ($authorPerson === false) $this->_authorPerson = false;
        elseif ($authorPerson === null) $this->_authorPerson = null;
        else {
            if (!is_a($authorPerson, 'Sample_Person')) trigger_error('$authorPerson must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_authorPerson) && !Ac_Util::sameObject($this->_authorPerson, $authorPerson)) { 
                $this->_authorPerson = $authorPerson;
            }
        }
    }
    
    function clearAuthorPerson() {
        $this->authorPerson = null;
    }

    /**
     * @return Sample_Person  
     */
    function createAuthorPerson($values = array()) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setAuthorPerson($res);
        return $res;
    }

    
        
    
    /**
     * @return Sample_Person 
     */
    function getEditorPerson() {
        if ($this->_editorPerson === false) {
            $this->mapper->loadEditorPeopleFor($this->mixin);
            
        }
        return $this->_editorPerson;
    }
    
    /**
     * @param Sample_Person $editorPerson 
     */
    function setEditorPerson($editorPerson) {
        if ($editorPerson === false) $this->_editorPerson = false;
        elseif ($editorPerson === null) $this->_editorPerson = null;
        else {
            if (!is_a($editorPerson, 'Sample_Person')) trigger_error('$editorPerson must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_editorPerson) && !Ac_Util::sameObject($this->_editorPerson, $editorPerson)) { 
                $this->_editorPerson = $editorPerson;
            }
        }
    }
    
    function clearEditorPerson() {
        $this->editorPerson = null;
    }

    /**
     * @return Sample_Person  
     */
    function createEditorPerson($values = array()) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setEditorPerson($res);
        return $res;
    }

    
  
    
}

