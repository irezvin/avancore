<?php

class Child_Tree_Combo_Base_Object extends Sample_Tree_Combo {

    
    var $_mapperClass = 'Child_Tree_Combo_Mapper';
    
    /**
     * @var Child_Tree_Combo_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Tree_Combo_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    
  
    
}

