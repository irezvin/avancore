<?php

class Sample_Tree_Combo_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $id = NULL;
    public $leftCol = 0;
    public $rightCol = 1;
    public $parentId = NULL;
    public $ordering = 0;
    public $title = '';
    public $tag = NULL;
    public $ignore = 0;
    public $depth = 0;
    
    var $_mapperClass = 'Sample_Tree_Combo_Mapper';
    
    /**
     * @var Sample_Tree_Combo_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Tree_Combo_Mapper 
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
            'leftCol' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Left Col',
            ),
            'rightCol' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Right Col',
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
            'ignore' => array (
                'dataType' => 'bool',
                'controlType' => 'selectList',
                'maxLength' => '1',
                'valueList' => array (
                    0 => 'No',
                    1 => 'Yes',
                ),
                'caption' => 'Ignore',
            ),
            'depth' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Depth',
            ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }
  
    
}

