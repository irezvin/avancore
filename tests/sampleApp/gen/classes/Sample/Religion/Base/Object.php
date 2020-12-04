<?php

class Sample_Religion_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_people = false;

    var $_peopleCount = false;

    var $_peopleLoaded = false;

    var $religionId = NULL;

    var $title = '';
    
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
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'people', ]));
    }
    
 
    protected function listOwnLists() {
        
        return [ 'people' => 'people', ];
    }

    
 
    protected function listOwnAssociations() {
        return [ 'people' => 'Sample_Person', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'people' => [
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',

                'caption' => new Ac_Lang_String('sample_religion_people'),
                'relationId' => '_people',
                'countVarName' => '_peopleCount',
                'referenceVarName' => '_people',
            ],
            'religionId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_religion_religion_id'),
            ],
            'title' => [
                'maxLength' => '45',

                'caption' => new Ac_Lang_String('sample_religion_title'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }

    function countPeople() {
        if (is_array($this->_people)) return count($this->_people);
        if ($this->_peopleCount === false) {
            $this->mapper->loadAssocCountFor($this, '_people');
        }
        return $this->_peopleCount;
        
    }

    function listPeople() {
        if (!$this->_peopleLoaded) {
            $this->mapper->loadPeopleFor($this);
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
            $this->mapper->loadPeopleFor($this);
        }
        
        if (!isset($this->_people[$id])) trigger_error ('No such People: \''.$id.'\'', E_USER_ERROR);
        return $this->_people[$id];
    }
    
    /**
     * @return Sample_Person 
     */
    function getPeopleItem($id) {
        return $this->getPerson($id);
    }
    
    /**
     * @return Sample_Person[] 
     */
    function getAllPeople() {
        $res = [];
        foreach ($this->listPeople() as $id)
            $res[] = $this->getPerson($id);
        return $res;
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
    function createPerson($values = array()) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addPerson($res);
        return $res;
    }
    
  
    
}

