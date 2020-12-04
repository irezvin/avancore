<?php

class Sample_Publish_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    var $_hasDefaults = true;

    var $_authorPerson = false;

    var $_editorPerson = false;

    var $id = NULL;

    var $sharedObjectType = '';

    var $published = 1;

    var $deleted = 0;

    var $publishUp = '0000-00-00 00:00:00';

    var $publishDown = '0000-00-00 00:00:00';

    var $authorId = NULL;

    var $editorId = NULL;

    var $pubChannelId = NULL;

    var $dateCreated = '0000-00-00 00:00:00';

    var $dateModified = '0000-00-00 00:00:00';

    var $dateDeleted = '0000-00-00 00:00:00';

    protected $preserveMetaCache = true;
    
    /**
     * @var Sample_Publish_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Sample_Publish';
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), [ 0 => 'authorPerson', 1 => 'editorPerson', ]);
    }
    
 
    protected function listOwnAssociations() {
        return [ 'authorPerson' => 'Sample_Person', 'editorPerson' => 'Sample_Person', ];
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = [
            'authorPerson' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'author',

                'caption' => new Ac_Lang_String('sample_publish_author_person'),
                'relationId' => '_authorPerson',
                'referenceVarName' => '_authorPerson',
            ],
            'editorPerson' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'editor',

                'caption' => new Ac_Lang_String('sample_publish_editor_person'),
                'relationId' => '_editorPerson',
                'referenceVarName' => '_editorPerson',
            ],
            'id' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_publish_id'),
            ],
            'sharedObjectType' => [
                'maxLength' => '50',

                'caption' => new Ac_Lang_String('sample_publish_shared_object_type'),
            ],
            'published' => [
                'dataType' => 'bool',
                'controlType' => 'selectList',
                'maxLength' => '1',
                'valueList' => [
                    0 => 'No',
                    1 => 'Yes',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_publish_published'),
            ],
            'deleted' => [
                'dataType' => 'bool',
                'controlType' => 'selectList',
                'maxLength' => '1',
                'valueList' => [
                    0 => 'No',
                    1 => 'Yes',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_publish_deleted'),
            ],
            'publishUp' => [
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_publish_publish_up'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ],
            'publishDown' => [
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_publish_publish_down'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ],
            'authorId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',

                'dummyCaption' => '',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ],
                'objectPropertyName' => 'authorPerson',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_publish_author_id'),
            ],
            'editorId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',

                'dummyCaption' => '',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ],
                'objectPropertyName' => 'editorPerson',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_publish_editor_id'),
            ],
            'pubChannelId' => [
                'maxLength' => '255',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_publish_pub_channel_id'),
            ],
            'dateCreated' => [
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',

                'caption' => new Ac_Lang_String('sample_publish_date_created'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ],
            'dateModified' => [
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',

                'caption' => new Ac_Lang_String('sample_publish_date_modified'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ],
            'dateDeleted' => [
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',

                'caption' => new Ac_Lang_String('sample_publish_date_deleted'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ],
        ];
    
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

