<?php

class Sample_Tree_Record_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $id = NULL;

    var $title = '';

    var $tag = NULL;
    
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
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'id' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_tree_record_id'),
            ],
            'title' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_tree_record_title'),
            ],
            'tag' => [
                'dataType' => 'int',
                'maxLength' => '11',
                'attribs' => [
                    'size' => '6',
                ],
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_tree_record_tag'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }
  
    
}

