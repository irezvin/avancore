<?php

class Child_Tree_Record_Base_Object extends Sample_Tree_Record {

    
    var $_mapperClass = 'Child_Tree_Record_Mapper';
    
    /**
     * @var Child_Tree_Record_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Tree_Record_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = array (
            'id' => array (
                'caption' => 'Id',
            ),
            'title' => array (
                'caption' => 'Title',
            ),
            'tag' => array (
                'caption' => 'Tag',
            ),
        );
    
        return $pi;
                
    }
    
  
    
}

