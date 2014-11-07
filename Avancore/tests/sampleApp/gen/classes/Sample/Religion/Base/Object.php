<?php

class Sample_Religion_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $_people = false;
    public $_peopleCount = false;
    public $_peopleLoaded = false;
    public $religionId = NULL;
    public $title = '';
    
    var $_mapperClass = 'Sample_Religion_Mapper';
    
    /**
     * @var Sample_Religion_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Religion_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array ( 0 => 'people', 1 => 'religionId', 2 => 'title', );
    }
 
    protected function listOwnLists() {
        
        return array ( 'people' => 'people', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'people' => 'Sample_Person', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
            'people' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'caption' => 'People',
                'relationId' => '_people',
                'countVarName' => '_peopleCount',
                'referenceVarName' => '_people',
            ),
            'religionId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Religion Id',
            ),
            'title' => array (
                'maxLength' => '45',
                'caption' => 'Title',
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
        if (!$this->_peopleLoaded) {
            $mapper = $this->getMapper();
            $mapper->listAssocFor($this, '_people');
        }
        return array_keys($this->_people);
    }
    
    /**
     * @return bool
     */
    function isPeopleLoaded() {
        return $this->_peopleLoaded;
    }
    
    /**
     * @return Sample_Person 
     */
    function getPerson($id) {
        if (!$this->_peopleLoaded) {
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
        
        $person->_religion = $this;
        
    }
    
    /**
     * @return Sample_Person  
     */
    function createPerson($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addPerson($res);
        return $res;
    }
    
  
    
}

