<?php
/**
 * @property Child $app Access to App instance (via Mapper)
 */
class Child_Tree_Adjacent_Base_Object extends Sample_Tree_Adjacent {

    
    var $_mapperClass = 'Child_Tree_Adjacent_Mapper';
    
    /**
     * @var Child_Tree_Adjacent_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Child_Tree_Adjacent_Mapper 
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
        ];
    
        return $pi;
                
    }
    
  
    
}

