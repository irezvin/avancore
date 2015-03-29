<?php

class Sample_Shop_Product_Extra_Code_Base_ObjectMixable extends Ac_Model_Mixable_ExtraTable {

    public $_hasDefaults = true;
    public $_person = false;
    public $productId = NULL;
    public $ean = '';
    public $asin = '';
    public $gtin = '';
    public $responsiblePersonId = NULL;
    
    /**
     * @var Sample_Shop_Product_Extra_Code_MapperMixable 
     */
    protected $mapperExtraTable = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    protected function listOwnProperties() {
        return array ( 0 => 'person', 1 => 'productId', 2 => 'ean', 3 => 'asin', 4 => 'gtin', 5 => 'responsiblePersonId', );
    }
    
 
    protected function listOwnAssociations() {
        return array ( 'person' => 'Sample_Person', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
            'person' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'caption' => 'People',
                'relationId' => '_person',
                'referenceVarName' => '_person',
            ),
            'productId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Shop_Product_Mapper',
                ),
                'caption' => 'Product Id',
            ),
            'ean' => array (
                'maxLength' => '255',
                'caption' => 'Ean',
            ),
            'asin' => array (
                'maxLength' => '255',
                'caption' => 'Asin',
            ),
            'gtin' => array (
                'maxLength' => '255',
                'caption' => 'Gtin',
            ),
            'responsiblePersonId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'objectPropertyName' => 'person',
                'isNullable' => true,
                'caption' => 'Responsible Person Id',
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Sample_Person 
     */
    function getPerson() {
        if ($this->_person === false) {
            $this->mapper->loadPeopleFor($this->mixin);
            
        }
        return $this->_person;
    }
    
    /**
     * @param Sample_Person $person 
     */
    function setPerson($person) {
        if ($person === false) $this->_person = false;
        elseif ($person === null) $this->_person = null;
        else {
            if (!is_a($person, 'Sample_Person')) trigger_error('$person must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_person) && !Ac_Util::sameObject($this->_person, $person)) { 
                $this->_person = $person;
            }
        }
    }
    
    function clearPerson() {
        $this->person = null;
    }

    /**
     * @return Sample_Person  
     */
    function createPerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setPerson($res);
        return $res;
    }

    
  
    
}

