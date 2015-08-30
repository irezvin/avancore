<?php

class Child_Publish_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {

    
    /**
     * @var Child_Publish_MapperMixable 
     */
    protected $mapperExtraTable = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array ( ));
    }
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'authorPerson' => array (
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
            ),
            'editorPerson' => array (
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
            ),
            'authorId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_Person_Mapper',
                ),
            ),
            'editorId' => array (
                'dummyCaption' => '',
                'values' => array (
                    'mapperClass' => 'Child_Person_Mapper',
                ),
            ),
        );
    
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

