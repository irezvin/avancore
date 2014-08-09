<?php

class Sample_Relation_Base_Object extends Ac_Model_Object {

    public $_relationType = false;
    public $_otherPerson = false;
    public $_person = false;
    public $relationId = NULL;
    public $personId = 0;
    public $otherPersonId = 0;
    public $relationTypeId = 0;
    public $relationBegin = NULL;
    public $relationEnd = NULL;
    public $notes = '';
    
    var $_mapperClass = 'Sample_Relation_Mapper';
    
    /**
     * @var Sample_Relation_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Relation_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array ( 'relationType', 'otherPerson', 'person', 'relationId', 'personId', 'otherPersonId', 'relationTypeId', 'relationBegin', 'relationEnd', 'notes', );
    }
    
 
    protected function listOwnAssociations() {
        return array ( 'relationType' => 'Sample_Relation_Type', 'otherPerson' => 'Sample_Person', 'person' => 'Sample_Person', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
              'relationType' => array (
                  'className' => 'Sample_Relation_Type',
                  'mapperClass' => 'Sample_Relation_Type_Mapper',
                  'caption' => 'Relation type',
                  'relationId' => '_relationType',
              ),
              'otherPerson' => array (
                  'className' => 'Sample_Person',
                  'mapperClass' => 'Sample_Person_Mapper',
                  'otherModelIdInMethodsSingle' => 'otherPerson',
                  'otherModelIdInMethodsPlural' => 'otherPeople',
                  'caption' => 'People',
                  'relationId' => '_otherPerson',
              ),
              'person' => array (
                  'className' => 'Sample_Person',
                  'mapperClass' => 'Sample_Person_Mapper',
                  'otherModelIdInMethodsSingle' => 'person',
                  'otherModelIdInMethodsPlural' => 'people',
                  'caption' => 'People',
                  'relationId' => '_person',
              ),
              'relationId' => array (
                  'dataType' => 'int',
                  'maxLength' => '10',
                  'attribs' => array (
                      'size' => '6',
                  ),
                  'caption' => 'Relation Id',
              ),
              'personId' => array (
                  'dataType' => 'int',
                  'controlType' => 'selectList',
                  'maxLength' => '10',
                  'values' => array (
                      'class' => 'Ac_Model_Values_Records',
                      'mapperClass' => 'Sample_Person_Mapper',
                  ),
                  'objectPropertyName' => 'person',
                  'caption' => 'Person Id',
              ),
              'otherPersonId' => array (
                  'dataType' => 'int',
                  'controlType' => 'selectList',
                  'maxLength' => '10',
                  'values' => array (
                      'class' => 'Ac_Model_Values_Records',
                      'mapperClass' => 'Sample_Person_Mapper',
                  ),
                  'objectPropertyName' => 'otherPerson',
                  'caption' => 'Other Person Id',
              ),
              'relationTypeId' => array (
                  'dataType' => 'int',
                  'controlType' => 'selectList',
                  'maxLength' => '10',
                  'values' => array (
                      'class' => 'Ac_Model_Values_Records',
                      'mapperClass' => 'Sample_Relation_Type_Mapper',
                  ),
                  'objectPropertyName' => 'relationType',
                  'caption' => 'Relation Type Id',
              ),
              'relationBegin' => array (
                  'dataType' => 'dateTime',
                  'controlType' => 'dateInput',
                  'isNullable' => true,
                  'caption' => 'Relation Begin',
                  'internalDateFormat' => 'Y-m-d H:i:s',
                  'outputDateFormat' => 'Y-m-d H:i:s',
              ),
              'relationEnd' => array (
                  'dataType' => 'dateTime',
                  'controlType' => 'dateInput',
                  'isNullable' => true,
                  'caption' => 'Relation End',
                  'internalDateFormat' => 'Y-m-d H:i:s',
                  'outputDateFormat' => 'Y-m-d H:i:s',
              ),
              'notes' => array (
                  'controlType' => 'textArea',
                  'caption' => 'Notes',
              ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }
        
    
    /**
     * @return Sample_Relation_Type 
     */
    function getRelationType() {
        if ($this->_relationType === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_relationType');
        }
        return $this->_relationType;
    }
    
    /**
     * @param Sample_Relation_Type $relationType 
     */
    function setRelationType($relationType) {
        if ($relationType === false) $this->_relationType = false;
        elseif ($relationType === null) $this->_relationType = null;
        else {
            if (!is_a($relationType, 'Sample_Relation_Type')) trigger_error('$relationType must be an instance of Sample_Relation_Type', E_USER_ERROR);
            if (!is_object($this->_relationType) && !Ac_Util::sameObject($this->_relationType, $relationType)) { 
                $this->_relationType = $relationType;
            }
        }
    }
    
    function clearRelationType() {
        $this->relationType = null;
    }
    
    /**
     * @return Sample_Relation_Type  
     */
    function createRelationType($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Relation_Type_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setRelationType($res);
        return $res;
    }
    
        
    
    /**
     * @return Sample_Person 
     */
    function getOtherPerson() {
        if ($this->_otherPerson === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_otherPerson');
        }
        return $this->_otherPerson;
    }
    
    /**
     * @param Sample_Person $otherPerson 
     */
    function setOtherPerson($otherPerson) {
        if ($otherPerson === false) $this->_otherPerson = false;
        elseif ($otherPerson === null) $this->_otherPerson = null;
        else {
            if (!is_a($otherPerson, 'Sample_Person')) trigger_error('$otherPerson must be an instance of Sample_Person', E_USER_ERROR);
            if (!is_object($this->_otherPerson) && !Ac_Util::sameObject($this->_otherPerson, $otherPerson)) { 
                $this->_otherPerson = $otherPerson;
            }
        }
    }
    
    function clearOtherPerson() {
        $this->otherPerson = null;
    }
    
    /**
     * @return Sample_Person  
     */
    function createOtherPerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setOtherPerson($res);
        return $res;
    }
    
        
    
    /**
     * @return Sample_Person 
     */
    function getPerson() {
        if ($this->_person === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_person');
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
    
  

    function _storeReferencedRecords() {
        $res = parent::_storeReferencedRecords() !== false;
        $mapper = $this->getMapper();

        if (is_object($this->_relationType)) {
            $rel = $mapper->getRelation('_relationType');
            if (!$this->_autoStoreReferenced($this->_relationType, $rel->fieldLinks, 'relationType')) $res = false;
        }

        if (is_object($this->_otherPerson)) {
            $rel = $mapper->getRelation('_otherPerson');
            if (!$this->_autoStoreReferenced($this->_otherPerson, $rel->fieldLinks, 'otherPerson')) $res = false;
        }

        if (is_object($this->_person)) {
            $rel = $mapper->getRelation('_person');
            if (!$this->_autoStoreReferenced($this->_person, $rel->fieldLinks, 'person')) $res = false;
        }
 
        return $res;
    }
 
    protected function intListReferenceFields() {
        $res = array (
              'relationTypeId' => '_relationType',
              'otherPersonId' => '_otherPerson',
              'personId' => '_person',
        );
        return $res;
    }
    
}

