<?php

class Sample_Relation_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_relationType = false;

    var $_otherPerson = false;

    var $_person = false;

    var $relationId = NULL;

    var $personId = 0;

    var $otherPersonId = 0;

    var $relationTypeId = 0;

    var $relationBegin = NULL;

    var $relationEnd = NULL;

    var $notes = '';
    
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
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'relationType', 1 => 'otherPerson', 2 => 'person', ]));
    }
    
    
 
    protected function listOwnAssociations() {
        return [ 'relationType' => 'Sample_Relation_Type', 'otherPerson' => 'Sample_Person', 'person' => 'Sample_Person', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'relationType' => [
                'className' => 'Sample_Relation_Type',
                'mapperClass' => 'Sample_Relation_Type_Mapper',

                'caption' => new Ac_Lang_String('sample_relation_relation_type'),
                'relationId' => '_relationType',
                'referenceVarName' => '_relationType',
            ],
            'otherPerson' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsSingle' => 'otherPerson',
                'otherModelIdInMethodsPlural' => 'otherPeople',

                'caption' => new Ac_Lang_String('sample_relation_other_person'),
                'relationId' => '_otherPerson',
                'referenceVarName' => '_otherPerson',
            ],
            'person' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'otherModelIdInMethodsSingle' => 'person',
                'otherModelIdInMethodsPlural' => 'people',

                'caption' => new Ac_Lang_String('sample_relation_person'),
                'relationId' => '_person',
                'referenceVarName' => '_person',
            ],
            'relationId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_relation_relation_id'),
            ],
            'personId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ],
                'objectPropertyName' => 'person',

                'caption' => new Ac_Lang_String('sample_relation_person_id'),
            ],
            'otherPersonId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ],
                'objectPropertyName' => 'otherPerson',

                'caption' => new Ac_Lang_String('sample_relation_other_person_id'),
            ],
            'relationTypeId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Relation_Type_Mapper',
                ],
                'objectPropertyName' => 'relationType',

                'caption' => new Ac_Lang_String('sample_relation_relation_type_id'),
            ],
            'relationBegin' => [
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_relation_relation_begin'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ],
            'relationEnd' => [
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_relation_relation_end'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ],
            'notes' => [
                'controlType' => 'textArea',

                'caption' => new Ac_Lang_String('sample_relation_notes'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }
        
    
    /**
     * @return Sample_Relation_Type 
     */
    function getRelationType() {
        if ($this->_relationType === false) {
            $this->mapper->loadRelationTypesFor($this);
            
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
    function createRelationType($values = array()) {
        $m = $this->getMapper('Sample_Relation_Type_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setRelationType($res);
        return $res;
    }

    
        
    
    /**
     * @return Sample_Person 
     */
    function getOtherPerson() {
        if ($this->_otherPerson === false) {
            $this->mapper->loadOtherPeopleFor($this);
            
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
    function createOtherPerson($values = array()) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setOtherPerson($res);
        return $res;
    }

    
        
    
    /**
     * @return Sample_Person 
     */
    function getPerson() {
        if ($this->_person === false) {
            $this->mapper->loadPeopleFor($this);
            
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
    function createPerson($values = array()) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setPerson($res);
        return $res;
    }

    
  
    
}

