<?php

Ae_Dispatcher::loadClass('Ae_Model_Object');

class Ae_Test_Model_Relation_Base_Object extends Ae_Model_Object {
    
    var $_person = false;
    var $_otherPerson = false;
    var $_relationType = false;
    var $relationId = NULL;
    var $personId = 0;
    var $otherPersonId = 0;
    var $relationTypeId = 0;
    var $relationBegin = NULL;
    var $relationEnd = NULL;
    var $notes = '';
    
    var $_mapperClass = 'Ae_Test_Model_Relation_Mapper';
    
    function Ae_Test_Model_Relation_Base_Object() {
        parent::Ae_Model_Object ('#__relations', 'relationId');
    }
    
    function listOwnProperties() {
        return array ( 'person', 'otherPerson', 'relationType', 'relationId', 'personId', 'otherPersonId', 'relationTypeId', 'relationBegin', 'relationEnd', 'notes', );
    }

    function listOwnLists() {
        return array ( );
    }

    function listOwnAssociations() {
        return array ( 'person' => 'Ae_Test_Model_People', 'otherPerson' => 'Ae_Test_Model_People', 'relationType' => 'Ae_Test_Model_Relation_Type', );
    }

    function getOwnPropertiesInfo() {
    
        return array (
              'person' => array (  
                  'className' => 'Ae_Test_Model_People',
                  'mapperClass' => 'Ae_Test_Model_People_Mapper',
                  'relationId' => '_person',
                  'otherModelIdInMethodsSingle' => 'person',
                  'otherModelIdInMethodsPlural' => 'people',
                  'caption' => 'People',
                  'isEnabled' => true,
              ),
              'otherPerson' => array (  
                  'className' => 'Ae_Test_Model_People',
                  'mapperClass' => 'Ae_Test_Model_People_Mapper',
                  'relationId' => '_otherPerson',
                  'otherModelIdInMethodsSingle' => 'otherPerson',
                  'otherModelIdInMethodsPlural' => 'otherPeople',
                  'caption' => 'People',
                  'isEnabled' => true,
              ),
              'relationType' => array (  
                  'className' => 'Ae_Test_Model_Relation_Type',
                  'mapperClass' => 'Ae_Test_Model_Relation_Type_Mapper',
                  'relationId' => '_relationType',
                  'caption' => 'Relation type',
                  'isEnabled' => true,
              ),
              'relationId' => array (  
                  'dataType' => 'int',
                  'maxLength' => '10',
                  'attribs' => array (    
                      'size' => '6',
                  ),
                  'caption' => 'Relation Id',
                  'isEnabled' => true,
              ),
              'personId' => array (  
                  'dataType' => 'int',
                  'controlType' => 'selectList',
                  'maxLength' => '10',
                  'values' => array (    
                      'class' => 'Ae_Model_Values_Records',
                      'mapperClass' => 'Ae_Test_Model_People_Mapper',
                  ),
                  'objectPropertyName' => 'otherPerson',
                  'caption' => 'Person Id',
                  'isEnabled' => true,
              ),
              'otherPersonId' => array (  
                  'dataType' => 'int',
                  'controlType' => 'selectList',
                  'maxLength' => '10',
                  'values' => array (    
                      'class' => 'Ae_Model_Values_Records',
                      'mapperClass' => 'Ae_Test_Model_People_Mapper',
                  ),
                  'objectPropertyName' => 'otherPerson',
                  'caption' => 'Other Person Id',
                  'isEnabled' => true,
              ),
              'relationTypeId' => array (  
                  'dataType' => 'int',
                  'controlType' => 'selectList',
                  'maxLength' => '10',
                  'values' => array (    
                      'class' => 'Ae_Model_Values_Records',
                      'mapperClass' => 'Ae_Test_Model_Relation_Type_Mapper',
                  ),
                  'objectPropertyName' => 'relationType',
                  'caption' => 'Relation Type Id',
                  'isEnabled' => true,
              ),
              'relationBegin' => array (  
                  'dataType' => 'dateTime',
                  'controlType' => 'dateInput',
                  'isNullable' => true,
                  'caption' => 'Relation Begin',
                  'isEnabled' => true,
                  'internalDateFormat' => 'Y-m-d H:i:s',
                  'outputDateFormat' => 'Y-m-d H:i:s',
              ),
              'relationEnd' => array (  
                  'dataType' => 'dateTime',
                  'controlType' => 'dateInput',
                  'isNullable' => true,
                  'caption' => 'Relation End',
                  'isEnabled' => true,
                  'internalDateFormat' => 'Y-m-d H:i:s',
                  'outputDateFormat' => 'Y-m-d H:i:s',
              ),
              'notes' => array (  
                  'controlType' => 'textArea',
                  'caption' => 'Notes',
                  'isEnabled' => true,
              ),
        );
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }
        
    
    /**
     * @return Ae_Test_Model_People 
     */
    function & getPerson() {
        if ($this->_person === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocFor($this, '_person');
        }
        return $this->_person;
    }
    
    /**
     * @param Ae_Test_Model_People $person 
     */
    function setPerson(& $person) {
        if ($person === false) $this->_person = false;
        elseif ($person === null) $this->_person = null;
        else {
            if (!is_a($person, 'Ae_Test_Model_People')) trigger_error('$person must be an instance of Ae_Test_Model_People', E_USER_ERROR);
            if (!is_object($this->_person) && !Ae_Util::sameObject($this->_person, $person)) { 
                $this->_person = & $person;
            }
        }
    }
    
    function clearPerson() {
        $this->person = null;
    }
    
    /**
     * @return Ae_Test_Model_People  
     */
    function & createPerson($values = array(), $isReference = false) {
        $m = & $this->getMapper('Ae_Test_Model_People_Mapper');
        $res = & $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->setIsReference(true);
        $this->setPerson($res);
        return $res;
    }
    
        
    
    /**
     * @return Ae_Test_Model_People 
     */
    function & getOtherPerson() {
        if ($this->_otherPerson === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocFor($this, '_otherPerson');
        }
        return $this->_otherPerson;
    }
    
    /**
     * @param Ae_Test_Model_People $otherPerson 
     */
    function setOtherPerson(& $otherPerson) {
        if ($otherPerson === false) $this->_otherPerson = false;
        elseif ($otherPerson === null) $this->_otherPerson = null;
        else {
            if (!is_a($otherPerson, 'Ae_Test_Model_People')) trigger_error('$otherPerson must be an instance of Ae_Test_Model_People', E_USER_ERROR);
            if (!is_object($this->_otherPerson) && !Ae_Util::sameObject($this->_otherPerson, $otherPerson)) { 
                $this->_otherPerson = & $otherPerson;
            }
        }
    }
    
    function clearOtherPerson() {
        $this->otherPerson = null;
    }
    
    /**
     * @return Ae_Test_Model_People  
     */
    function & createOtherPerson($values = array(), $isReference = false) {
        $m = & $this->getMapper('Ae_Test_Model_People_Mapper');
        $res = & $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->setIsReference(true);
        $this->setOtherPerson($res);
        return $res;
    }
    
        
    
    /**
     * @return Ae_Test_Model_Relation_Type 
     */
    function & getRelationType() {
        if ($this->_relationType === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocFor($this, '_relationType');
        }
        return $this->_relationType;
    }
    
    /**
     * @param Ae_Test_Model_Relation_Type $relationType 
     */
    function setRelationType(& $relationType) {
        if ($relationType === false) $this->_relationType = false;
        elseif ($relationType === null) $this->_relationType = null;
        else {
            if (!is_a($relationType, 'Ae_Test_Model_Relation_Type')) trigger_error('$relationType must be an instance of Ae_Test_Model_Relation_Type', E_USER_ERROR);
            if (!is_object($this->_relationType) && !Ae_Util::sameObject($this->_relationType, $relationType)) { 
                $this->_relationType = & $relationType;
            }
        }
    }
    
    function clearRelationType() {
        $this->relationType = null;
    }
    
    /**
     * @return Ae_Test_Model_Relation_Type  
     */
    function & createRelationType($values = array(), $isReference = false) {
        $m = & $this->getMapper('Ae_Test_Model_Relation_Type_Mapper');
        $res = & $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->setIsReference(true);
        $this->setRelationType($res);
        return $res;
    }
    
  

    function _storeUpstandingRecords() {
        $res = parent::_storeUpstandingRecords() !== false;
        $mapper = & $this->getMapper();

        if (is_object($this->_person)) {
            $rel = & $mapper->getRelation('_person');
            if (!$this->_autoStoreUpstanding($this->_person, $rel->fieldLinks, 'person')) $res = false;
        }

        if (is_object($this->_otherPerson)) {
            $rel = & $mapper->getRelation('_otherPerson');
            if (!$this->_autoStoreUpstanding($this->_otherPerson, $rel->fieldLinks, 'otherPerson')) $res = false;
        }

        if (is_object($this->_relationType)) {
            $rel = & $mapper->getRelation('_relationType');
            if (!$this->_autoStoreUpstanding($this->_relationType, $rel->fieldLinks, 'relationType')) $res = false;
        }
 
        return $res;
    }
    
}

?>