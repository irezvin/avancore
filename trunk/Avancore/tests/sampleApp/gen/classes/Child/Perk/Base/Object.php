<?php

class Child_Perk_Base_Object extends Sample_Perk {

    
    var $_mapperClass = 'Child_Perk_Mapper';
    
    /**
     * @var Child_Perk_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Perk_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'tags' => array (
                'className' => 'Child_Tag',
                'mapperClass' => 'Child_Tag_Mapper',
            ),
            'tagIds' => array (
                'values' => array (
                    'mapperClass' => 'Child_Tag_Mapper',
                ),
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Tag 
     */
    function getTag($id) {
        return parent::getTag($id);
    }
    
    /**
     * @return Child_Tag 
     */
    function getTagsItem($id) {
        return parent::getTagsItem($id);
    }
    
    /**
     * @param Child_Tag $tag 
     */
    function addTag($tag) {
        if (!is_a($tag, 'Child_Tag'))
            trigger_error('$tag must be an instance of Child_Tag', E_USER_ERROR);
        return parent::addTag($tag);
    }
    
    /**
     * @return Child_Tag  
     */
    function createTag($values = array(), $isReference = false) {
        return parent::createTag($values, $isReference);
    }

    

  
    
}

