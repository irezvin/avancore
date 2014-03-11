<?php

class Sample_Tag_Base_Object extends Ac_Model_Object {

    public $_people = false;
    public $_peopleCount = false;
    public $_personIds = false;
    public $tagId = NULL;
    public $title = '';
    public $titleM = NULL;
    public $titleF = NULL;
    
    var $_mapperClass = 'Sample_Tag_Mapper';
    
    /**
     * @var Sample_Tag_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Tag_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    function listOwnProperties() {
        
        return array ( 'people', 'personIds', 'tagId', 'title', 'titleM', 'titleF', );
        
    }

    function listOwnLists() {
        
        return array ( 'people' => 'people', );
        
    }

    function listOwnAssociations() {
        
        return array ( 'people' => 'Sample_Person', );
        
    }

    function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
              'people' => array (
                  'className' => 'Sample_Person',
                  'mapperClass' => 'Sample_Person_Mapper',
                  'caption' => 'People',
                  'relationId' => '_people',
              ),
              'personIds' => array (
                  'dataType' => 'int',
                  'arrayValue' => true,
                  'controlType' => 'selectList',
                  'values' => array (
                      'class' => 'Ac_Model_Values_Records',
                      'mapperClass' => 'Sample_Person_Mapper',
                  ),
                  'showInTable' => false,
              ),
              'tagId' => array (
                  'dataType' => 'int',
                  'maxLength' => '10',
                  'attribs' => array (
                      'size' => '6',
                  ),
                  'caption' => 'Tag Id',
              ),
              'title' => array (
                  'maxLength' => '45',
                  'caption' => 'Title',
              ),
              'titleM' => array (
                  'maxLength' => '45',
                  'isNullable' => true,
                  'caption' => 'Title M',
              ),
              'titleF' => array (
                  'maxLength' => '45',
                  'isNullable' => true,
                  'caption' => 'Title F',
              ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }

    function countPeople() {
        if (is_array($this->_people)) return count($this->_people);
        if ($this->_peopleCount === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocCountFor($this, '_people');
        }
        return $this->_peopleCount;
    }

    function listPeople() {
        if ($this->_people === false) {
            $mapper = $this->getMapper();
            $mapper->listAssocFor($this, '_people');
        }
        return array_keys($this->_people);
    }
    
    /**
     * @return Sample_Person 
     */
    function getPerson($id) {
        if ($this->_people === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_people');
        }
        if (!isset($this->_people[$id])) trigger_error ('No such People: \''.$id.'\'', E_USER_ERROR);
        if ($this->_people[$id] === false) {
        }
        return $this->_people[$id];
    }
    
    /**
     * @param Sample_Person $person 
     */
    function addPerson($person) {
        if (!is_a($person, 'Sample_Person')) trigger_error('$person must be an instance of Sample_Person', E_USER_ERROR);
        $this->listPeople();
        $this->_people[] = $person;
        
        if (is_array($person->_tags) && !Ac_Util::sameInArray($this, $person->_tags)) {
                $person->_tags[] = $this;
        }
        
    }
    
    /**
     * @return Sample_Person  
     */
    function createPerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addPerson($res);
        return $res;
    }
    

    function getPersonIds() {
        if ($this->_personIds === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocNNIdsFor($this, '_people');
        }
        return $this->_personIds;
    }
    
    function setPersonIds($personIds) {
        if (!is_array($personIds)) trigger_error('$personIds must be an array', E_USER_ERROR);
        $this->_personIds = $personIds;
        $this->_people = false; 
    }
    
    function clearPeople() {
        $this->_people = array();
        $this->_personIds = false;
    }               
  

    function _storeNNRecords() {
        $res = parent::_storeNNRecords() !== false;
        $mapper = $this->getMapper();
        
        if (is_array($this->_people) || is_array($this->_personIds)) {
            $rel = $mapper->getRelation('_people');
            if (!$this->_autoStoreNNRecords($this->_people, $this->_personIds, $rel->fieldLinks, $rel->fieldLinks2, $rel->midTableName, 'people', $rel->midWhere)) 
                $res = false;
        }
            
        return $res; 
    }
    
}

