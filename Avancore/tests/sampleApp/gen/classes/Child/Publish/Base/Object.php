<?php

class Child_Publish_Base_Object extends Sample_Publish {

    
    var $_mapperClass = 'Child_Publish_Mapper';
    
    /**
     * @var Child_Publish_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Publish_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), array ( 0 => 'authorPeople', 1 => 'editorPeople', )));
    }
    
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'authorPeople' => array (
                'className' => 'Child_People',
                'mapperClass' => 'Child_People_Mapper',
                'otherModelIdInMethodsPrefix' => 'author',
                'caption' => 'People',
                'relationId' => '_authorPeople',
                'referenceVarName' => '_authorPeople',
            ),
            'editorPeople' => array (
                'className' => 'Child_People',
                'mapperClass' => 'Child_People_Mapper',
                'otherModelIdInMethodsPrefix' => 'editor',
                'caption' => 'People',
                'relationId' => '_editorPeople',
                'referenceVarName' => '_editorPeople',
            ),
            'authorId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_People_Mapper',
                ),
                'objectPropertyName' => 'authorPeople',
            ),
            'editorId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_People_Mapper',
                ),
                'objectPropertyName' => 'editorPeople',
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_People 
     */
    function getAuthorPeople() {
        return parent::getAuthorPeople();
    }
    
    /**
     * @param Child_People $authorPeople 
     */
    function setAuthorPeople($authorPeople) {
        if ($authorPeople && !is_a($authorPeople, 'Child_People')) 
            trigger_error('$authorPeople must be an instance of Child_People', E_USER_ERROR);
        return parent::setAuthorPeople($authorPeople);
    }
    
    /**
     * @return Child_People  
     */
    function createAuthorPeople($values = array(), $isReference = false) {
        return parent::createAuthorPeople($values, $isReference);
    }

    
        
    
    /**
     * @return Child_People 
     */
    function getEditorPeople() {
        return parent::getEditorPeople();
    }
    
    /**
     * @param Child_People $editorPeople 
     */
    function setEditorPeople($editorPeople) {
        if ($editorPeople && !is_a($editorPeople, 'Child_People')) 
            trigger_error('$editorPeople must be an instance of Child_People', E_USER_ERROR);
        return parent::setEditorPeople($editorPeople);
    }
    
    /**
     * @return Child_People  
     */
    function createEditorPeople($values = array(), $isReference = false) {
        return parent::createEditorPeople($values, $isReference);
    }

    
  
    
}

