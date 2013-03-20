<?php

class Sample_Relation_Base_Object extends Ac_Model_Object {
    
    var $_relationType = false;
    var $_incomingPeople = false;
    var $_incomingCount = false;
    var $_outgoingPeople = false;
    var $_outgoingCount = false;
    var $relationId = NULL;
    var $personId = 0;
    var $otherPersonId = 0;
    var $relationTypeId = 0;
    var $relationBegin = NULL;
    var $relationEnd = NULL;
    var $notes = '';
    
    var $_mapperClass = 'Sample_Relation_Mapper';

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    function listOwnProperties() {
        
        return array ( 'relationType', 'incomingPeople', 'outgoingPeople', 'relationId', 'personId', 'otherPersonId', 'relationTypeId', 'relationBegin', 'relationEnd', 'notes', );
        
    }

    function listOwnLists() {
        
        return array ( 'incomingPeople' => 'incoming', 'outgoingPeople' => 'outgoing', );
        
    }

    function listOwnAssociations() {
        
        return array ( 'relationType' => 'Sample_Relation_Type', 'incomingPeople' => 'Sample_People', 'outgoingPeople' => 'Sample_People', );
        
    }

    function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
              'relationType' => array (
                  'className' => 'Sample_Relation_Type',
                  'mapperClass' => 'Sample_Relation_Type_Mapper',
                  'relationId' => '_relationType',
                  'caption' => 'Relation type',
              ),
              'incomingPeople' => array (
                  'className' => 'Sample_People',
                  'mapperClass' => 'Sample_People_Mapper',
                  'relationId' => '_people',
                  'otherModelIdInMethodsPrefix' => 'incoming',
                  'caption' => 'People',
              ),
              'outgoingPeople' => array (
                  'className' => 'Sample_People',
                  'mapperClass' => 'Sample_People_Mapper',
                  'relationId' => '_people',
                  'otherModelIdInMethodsPrefix' => 'outgoing',
                  'caption' => 'People',
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
                      'mapperClass' => 'Sample_People_Mapper',
                  ),
                  'objectPropertyName' => 'outgoingPeople',
                  'caption' => 'Person Id',
              ),
              'otherPersonId' => array (
                  'dataType' => 'int',
                  'controlType' => 'selectList',
                  'maxLength' => '10',
                  'values' => array (
                      'class' => 'Ac_Model_Values_Records',
                      'mapperClass' => 'Sample_People_Mapper',
                  ),
                  'objectPropertyName' => 'incomingPeople',
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
    function setRelationType(& $relationType) {
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
        $res = $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setRelationType($res);
        return $res;
    }
    
        
    
    /**
     * @return Sample_People 
     */
    function getIncomingPeople() {
        if ($this->_incomingPeople === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_incomingPeople');
        }
        return $this->_incomingPeople;
    }
    
    /**
     * @param Sample_People $incomingPeople 
     */
    function setIncomingPeople(& $incomingPeople) {
        if ($incomingPeople === false) $this->_incomingPeople = false;
        elseif ($incomingPeople === null) $this->_incomingPeople = null;
        else {
            if (!is_a($incomingPeople, 'Sample_People')) trigger_error('$incomingPeople must be an instance of Sample_People', E_USER_ERROR);
            if (!is_object($this->_incomingPeople) && !Ac_Util::sameObject($this->_incomingPeople, $incomingPeople)) { 
                $this->_incomingPeople = $incomingPeople;
            }
        }
    }
    
    function clearIncomingPeople() {
        $this->incomingPeople = null;
    }
    
    /**
     * @return Sample_People  
     */
    function createIncomingPeople($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_People_Mapper');
        $res = $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setIncomingPeople($res);
        return $res;
    }
    
        
    
    /**
     * @return Sample_People 
     */
    function getOutgoingPeople() {
        if ($this->_outgoingPeople === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_outgoingPeople');
        }
        return $this->_outgoingPeople;
    }
    
    /**
     * @param Sample_People $outgoingPeople 
     */
    function setOutgoingPeople(& $outgoingPeople) {
        if ($outgoingPeople === false) $this->_outgoingPeople = false;
        elseif ($outgoingPeople === null) $this->_outgoingPeople = null;
        else {
            if (!is_a($outgoingPeople, 'Sample_People')) trigger_error('$outgoingPeople must be an instance of Sample_People', E_USER_ERROR);
            if (!is_object($this->_outgoingPeople) && !Ac_Util::sameObject($this->_outgoingPeople, $outgoingPeople)) { 
                $this->_outgoingPeople = $outgoingPeople;
            }
        }
    }
    
    function clearOutgoingPeople() {
        $this->outgoingPeople = null;
    }
    
    /**
     * @return Sample_People  
     */
    function createOutgoingPeople($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_People_Mapper');
        $res = $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setOutgoingPeople($res);
        return $res;
    }
    
  

    function _storeUpstandingRecords() {
        $res = parent::_storeUpstandingRecords() !== false;
        $mapper = $this->getMapper();

        if (is_object($this->_relationType)) {
            $rel = $mapper->getRelation('_relationType');
            if (!$this->_autoStoreUpstanding($this->_relationType, $rel->fieldLinks, 'relationType')) $res = false;
        }

        if (is_object($this->_incomingPeople)) {
            $rel = $mapper->getRelation('_incomingPeople');
            if (!$this->_autoStoreUpstanding($this->_incomingPeople, $rel->fieldLinks, 'incomingPeople')) $res = false;
        }

        if (is_object($this->_outgoingPeople)) {
            $rel = $mapper->getRelation('_outgoingPeople');
            if (!$this->_autoStoreUpstanding($this->_outgoingPeople, $rel->fieldLinks, 'outgoingPeople')) $res = false;
        }
 
        return $res;
    }
    
}

