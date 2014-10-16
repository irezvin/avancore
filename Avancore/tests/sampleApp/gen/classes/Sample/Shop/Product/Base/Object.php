<?php

class Sample_Shop_Product_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $id = NULL;
    public $sku = '';
    public $title = '';
    public $metaId = NULL;
    public $published = 0;
    
    var $_mapperClass = 'Sample_Shop_Product_Mapper';
    
    /**
     * @var Sample_Shop_Product_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Shop_Product_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array ( 0 => 'id', 1 => 'sku', 2 => 'title', 3 => 'metaId', 4 => 'published', );
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
            'sku' => array (
                'maxLength' => '255',
                'caption' => 'Sku',
            ),
            'title' => array (
                'maxLength' => '255',
                'caption' => 'Title',
            ),
            'metaId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'isNullable' => true,
                'caption' => 'Meta Id',
            ),
            'published' => array (
                'dataType' => 'bool',
                'controlType' => 'selectList',
                'maxLength' => '1',
                'valueList' => array (
                    0 => 'No',
                    1 => 'Yes',
                ),
                'caption' => 'Published',
            ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }
  
    
}

