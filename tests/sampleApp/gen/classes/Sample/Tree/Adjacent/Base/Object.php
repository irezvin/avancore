<?php

class Sample_Tree_Adjacent_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $id = NULL;

    var $parentId = NULL;

    var $ordering = 0;

    var $title = '';

    var $tag = NULL;
    
    var $_mapperClass = 'Sample_Tree_Adjacent_Mapper';
    
    /**
     * @var Sample_Tree_Adjacent_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Tree_Adjacent_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = array (
            'id' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => new Ac_Lang_String('sample_tree_adjacent_id'),
            ),
            'parentId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_tree_adjacent_parent_id'),
            ),
            'ordering' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => new Ac_Lang_String('sample_tree_adjacent_ordering'),
            ),
            'title' => array (
                'maxLength' => '255',
                'caption' => new Ac_Lang_String('sample_tree_adjacent_title'),
            ),
            'tag' => array (
                'dataType' => 'int',
                'maxLength' => '11',
                'attribs' => array (
                    'size' => '6',
                ),
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_tree_adjacent_tag'),
            ),
        );
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }
  
    
}

