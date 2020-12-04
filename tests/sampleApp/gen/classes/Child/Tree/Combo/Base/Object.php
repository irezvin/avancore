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
        if ($pi === false) $pi = [
            'id' => [
                'caption' => 'Id',
            ],
            'leftCol' => [
                'caption' => 'Left Col',
            ],
            'rightCol' => [
                'caption' => 'Right Col',
            ],
            'parentId' => [
                'caption' => 'Parent Id',
            ],
            'ordering' => [
                'caption' => 'Ordering',
            ],
            'title' => [
                'caption' => 'Title',
            ],
            'tag' => [
                'caption' => 'Tag',
            ],
            'ignore' => [
                'caption' => 'Ignore',
            ],
            'depth' => [
                'caption' => 'Depth',
            ],
        ];
    
        return $pi;
                
    }
    
  
    
}

