<?php

class Sample_Publish_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {

    public $_hasDefaults = true;
    public $_authorAuthorPerson = false;
    public $_editorEditorPerson = false;
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
        return array_merge(parent::listOwnProperties(), array ( 0 => 'authorAuthorPerson', 1 => 'editorEditorPerson', ));
    }
    
 
    protected function listOwnAssociations() {
        return array ( 'authorAuthorPerson' => 'Sample_Person', 'editorEditorPerson' => 'Sample_Person', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'authorAuthorPerson' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'author',
                'caption' => 'People',
                'relationId' => '_authorAuthorPerson',
                'referenceVarName' => '_authorAuthorPerson',
            ),
            'editorEditorPerson' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'editor',
                'caption' => 'People',
                'relationId' => '_editorEditorPerson',
                'referenceVarName' => '_editorEditorPerson',
            ),
            'id' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Id',
            ),
            'sharedObjectType' => array (
                'maxLength' => '50',
                'caption' => 'Shared Object Type',
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
                'caption' => 'Published',
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
                'caption' => 'Deleted',
            ),
            'publishUp' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'isNullable' => true,
                'caption' => 'Publish Up',
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
            'publishDown' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'isNullable' => true,
                'caption' => 'Publish Down',
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
            'authorId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'objectPropertyName' => 'authorAuthorPerson',
                'isNullable' => true,
                'caption' => 'Author Id',
            ),
            'editorId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'objectPropertyName' => 'editorEditorPerson',
                'isNullable' => true,
                'caption' => 'Editor Id',
            ),
            'pubChannelId' => array (
                'maxLength' => '255',
                'isNullable' => true,
                'caption' => 'Pub Channel Id',
            ),
            'dateCreated' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'caption' => 'Date Created',
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
            'dateModified' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'caption' => 'Date Modified',
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
            'dateDeleted' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'caption' => 'Date Deleted',
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
        if ($this->_authorAuthorPerson === false) {
            $this->mapper->loadAuthorPeopleFor($this->mixin);
            
        }
        return $this->_authorAuthorPerson;
    }
    
    /**
     * @param Sample_Person $authorPerson 
     */
    function setAuthorPerson($authorPerson) {
        if ($authorPerson === false) $this->_authorAuthorPerson = false;
        elseif ($authorPerson === null) $this->_authorAuthorPerson = null;
        else {
            if (!is_a($authorPerson, 'Sample_Person')) trigger_error('$authorPerson must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_authorAuthorPerson) && !Ac_Util::sameObject($this->_authorAuthorPerson, $authorPerson)) { 
                $this->_authorAuthorPerson = $authorPerson;
            }
        }
    }
    
    function clearAuthorPerson() {
        $this->authorPerson = null;
    }

    /**
     * @return Sample_Person  
     */
    function createAuthorPerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setAuthorPerson($res);
        return $res;
    }

    
        
    
    /**
     * @return Sample_Person 
     */
    function getEditorPerson() {
        if ($this->_editorEditorPerson === false) {
            $this->mapper->loadEditorPeopleFor($this->mixin);
            
        }
        return $this->_editorEditorPerson;
    }
    
    /**
     * @param Sample_Person $editorPerson 
     */
    function setEditorPerson($editorPerson) {
        if ($editorPerson === false) $this->_editorEditorPerson = false;
        elseif ($editorPerson === null) $this->_editorEditorPerson = null;
        else {
            if (!is_a($editorPerson, 'Sample_Person')) trigger_error('$editorPerson must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_editorEditorPerson) && !Ac_Util::sameObject($this->_editorEditorPerson, $editorPerson)) { 
                $this->_editorEditorPerson = $editorPerson;
            }
        }
    }
    
    function clearEditorPerson() {
        $this->editorPerson = null;
    }

    /**
     * @return Sample_Person  
     */
    function createEditorPerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setEditorPerson($res);
        return $res;
    }

    
  
    
}

