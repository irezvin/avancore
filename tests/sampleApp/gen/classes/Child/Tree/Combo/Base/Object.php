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
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = array (
            'id' => array (
                'caption' => 'Id',
            ),
            'leftCol' => array (
                'caption' => 'Left Col',
            ),
            'rightCol' => array (
                'caption' => 'Right Col',
            ),
            'parentId' => array (
                'caption' => 'Parent Id',
            ),
            'ordering' => array (
                'caption' => 'Ordering',
            ),
            'title' => array (
                'caption' => 'Title',
            ),
            'tag' => array (
                'caption' => 'Tag',
            ),
            'ignore' => array (
                'caption' => 'Ignore',
            ),
            'depth' => array (
                'caption' => 'Depth',
            ),
        );
    
        return $pi;
                
    }
    
  
    
}

