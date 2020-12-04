<?php

class Child_Publish_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {


    protected $preserveMetaCache = true;
    
    /**
     * @var Child_Publish_MapperMixable 
     */
    protected $mapperExtraTable = false;

    protected $mixableId = 'Child_Publish';
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), []);
    }
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = [
            'authorPerson' => [
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
                'caption' => 'People',
            ],
            'editorPerson' => [
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
                'caption' => 'People',
            ],
            'id' => [
                'caption' => 'Id',
            ],
            'sharedObjectType' => [
                'caption' => 'Shared Object Type',
            ],
            'published' => [
                'caption' => 'Published',
            ],
            'deleted' => [
                'caption' => 'Deleted',
            ],
            'publishUp' => [
                'caption' => 'Publish Up',
            ],
            'publishDown' => [
                'caption' => 'Publish Down',
            ],
            'authorId' => [

                'dummyCaption' => '',
                'values' => [
                    'mapperClass' => 'Child_Person_Mapper',
                ],
                'caption' => 'Author Id',
            ],
            'editorId' => [

                'dummyCaption' => '',
                'values' => [
                    'mapperClass' => 'Child_Person_Mapper',
                ],
                'caption' => 'Editor Id',
            ],
            'pubChannelId' => [
                'caption' => 'Pub Channel Id',
            ],
            'dateCreated' => [
                'caption' => 'Date Created',
            ],
            'dateModified' => [
                'caption' => 'Date Modified',
            ],
            'dateDeleted' => [
                'caption' => 'Date Deleted',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Person 
     */
    function getAuthorPerson() {
        return parent::getAuthorPerson();
    }
    
    /**
     * @param Child_Person $authorPerson 
     */
    function setAuthorPerson($authorPerson) {
        if ($authorPerson && !is_a($authorPerson, 'Child_Person')) 
            trigger_error('$authorPerson must be an instance of Child_Person', E_USER_ERROR);
        return parent::setAuthorPerson($authorPerson);
    }
    
    /**
     * @return Child_Person  
     */
    function createAuthorPerson($values = array()) {
        return parent::createAuthorPerson($values);
    }

    
        
    
    /**
     * @return Child_Person 
     */
    function getEditorPerson() {
        return parent::getEditorPerson();
    }
    
    /**
     * @param Child_Person $editorPerson 
     */
    function setEditorPerson($editorPerson) {
        if ($editorPerson && !is_a($editorPerson, 'Child_Person')) 
            trigger_error('$editorPerson must be an instance of Child_Person', E_USER_ERROR);
        return parent::setEditorPerson($editorPerson);
    }
    
    /**
     * @return Child_Person  
     */
    function createEditorPerson($values = array()) {
        return parent::createEditorPerson($values);
    }

    
  
    
}

