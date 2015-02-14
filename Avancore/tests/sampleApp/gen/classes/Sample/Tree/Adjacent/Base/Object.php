<?php

class Sample_Tree_Adjacent_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $id = NULL;
    public $parentId = NULL;
    public $ordering = 0;
    public $title = '';
    public $tag = NULL;
    
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
 
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), array ( )));
    }
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
            'id' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Id',
            ),
            'parentId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'isNullable' => true,
                'caption' => 'Parent Id',
            ),
            'ordering' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Ordering',
            ),
            'title' => array (
                'maxLength' => '255',
                'caption' => 'Title',
            ),
            'tag' => array (
                'dataType' => 'int',
                'maxLength' => '11',
                'attribs' => array (
                    'size' => '6',
                ),
                'isNullable' => true,
                'caption' => 'Tag',
            ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }
  
    
}

