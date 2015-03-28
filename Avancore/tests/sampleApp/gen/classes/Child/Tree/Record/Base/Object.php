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
    
    
    
  
    
}

