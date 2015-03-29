<?php

class Sample_Tree_Record_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $id = NULL;
    public $title = '';
    public $tag = NULL;
    
    var $_mapperClass = 'Sample_Tree_Record_Mapper';
    
    /**
     * @var Sample_Tree_Record_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Tree_Record_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array ( 0 => 'id', 1 => 'title', 2 => 'tag', );
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

