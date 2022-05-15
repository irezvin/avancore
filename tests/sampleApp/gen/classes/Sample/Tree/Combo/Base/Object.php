<?php
/**
 * @property Sample $app Access to App instance (via Mapper)
 */
class Sample_Tree_Combo_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $id = NULL;

    var $leftCol = 0;

    var $rightCol = 1;

    var $parentId = NULL;

    var $ordering = 0;

    var $title = '';

    var $tag = NULL;

    var $ignore = 0;

    var $depth = 0;
    
    var $_mapperClass = 'Sample_Tree_Combo_Mapper';
    
    /**
     * @var Sample_Tree_Combo_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Tree_Combo_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'id' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_tree_combo_id'),
            ],
            'leftCol' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_tree_combo_left_col'),
            ],
            'rightCol' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_tree_combo_right_col'),
            ],
            'parentId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_tree_combo_parent_id'),
            ],
            'ordering' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_tree_combo_ordering'),
            ],
            'title' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_tree_combo_title'),
            ],
            'tag' => [
                'dataType' => 'int',
                'maxLength' => '11',
                'attribs' => [
                    'size' => '6',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_tree_combo_tag'),
            ],
            'ignore' => [
                'dataType' => 'bool',
                'controlType' => 'selectList',
                'maxLength' => '1',
                'valueList' => [
                    0 => 'No',
                    1 => 'Yes',
                ],

                'caption' => new Ac_Lang_String('sample_tree_combo_ignore'),
            ],
            'depth' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_tree_combo_depth'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }
  
    
}

