<?php

class Sample_Tag_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $_people = false;
    public $_peopleCount = false;
    public $_peopleLoaded = false;
    public $_personIds = false;
    public $_perks = false;
    public $_perksCount = false;
    public $_perksLoaded = false;
    public $_perkIds = false;
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
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), array ( 0 => 'people', 1 => 'personIds', 2 => 'perks', 3 => 'perkIds', )));
    }
    
 
    protected function listOwnLists() {
        
        return array ( 'people' => 'people', 'perks' => 'perks', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'people' => 'Sample_Person', 'perks' => 'Sample_Perk', );
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = array (
            'people' => array (
                'className' => 'Sample_Person',
                'mapperClass' => 'Sample_Person_Mapper',
                'caption' => new Ac_Lang_String('sample_tag_people'),
                'relationId' => '_people',
                'countVarName' => '_peopleCount',
                'nnIdsVarName' => '_personIds',
                'referenceVarName' => '_people',
            ),
            'personIds' => array (
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Mapper',
                ),
                'showInTable' => false,
            ),
            'perks' => array (
                'className' => 'Sample_Perk',
                'mapperClass' => 'Sample_Perk_Mapper',
                'caption' => new Ac_Lang_String('sample_tag_perks'),
                'relationId' => '_perks',
                'countVarName' => '_perksCount',
                'nnIdsVarName' => '_perkIds',
                'referenceVarName' => '_perks',
            ),
            'perkIds' => array (
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => array (
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Perk_Mapper',
                ),
                'showInTable' => false,
            ),
            'tagId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => new Ac_Lang_String('sample_tag_tag_id'),
            ),
            'title' => array (
                'maxLength' => '45',
                'caption' => new Ac_Lang_String('sample_tag_title'),
            ),
            'titleM' => array (
                'maxLength' => '45',
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_tag_title_m'),
            ),
            'titleF' => array (
                'maxLength' => '45',
                'isNullable' => true,
                'caption' => new Ac_Lang_String('sample_tag_title_f'),
            ),
        );
    
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
    function createPerson($values = array()) {
        $m = $this->getMapper('Sample_Person_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addPerson($res);
        return $res;
    }
    

    function getPersonIds() {
        if ($this->_personIds === false) {
            $this->mapper->loadPersonIdsFor($this);
        }
        return $this->_personIds;
    }
    
    function setPersonIds($personIds) {
        if (!is_array($personIds)) trigger_error('$personIds must be an array', E_USER_ERROR);
        $this->_personIds = $personIds;
        $this->_peopleLoaded = false;
        $this->_people = false; 
    }
    
    function clearPeople() {
        $this->_people = array();
        $this->_peopleLoaded = true;
        $this->_personIds = false;
    }               

    function countPerks() {
        if (is_array($this->_perks)) return count($this->_perks);
        if ($this->_perksCount === false) {
            $this->mapper->loadAssocCountFor($this, '_perks');
        }
        return $this->_perksCount;
        
    }

    function listPerks() {
        if (!$this->_perksLoaded) {
            $this->mapper->loadPerksFor($this);
        }
        return array_keys($this->_perks);
    }
    
    /**
     * @return bool
     */
    function isPerksLoaded() {
        return $this->_perksLoaded;
    }
    
    /**
     * @return Sample_Perk 
     */
    function getPerk($id) {
        if (!$this->_perksLoaded) {
            $this->mapper->loadPerksFor($this);
        }
        
        if (!isset($this->_perks[$id])) trigger_error ('No such Perk: \''.$id.'\'', E_USER_ERROR);
        return $this->_perks[$id];
    }
    
    /**
     * @return Sample_Perk 
     */
    function getPerksItem($id) {
        return $this->getPerk($id);
    }
    
    /**
     * @param Sample_Perk $perk 
     */
    function addPerk($perk) {
        if (!is_a($perk, 'Sample_Perk')) trigger_error('$perk must be an instance of Sample_Perk', E_USER_ERROR);
        $this->listPerks();
        $this->_perks[] = $perk;
        
        if (is_array($perk->_tags) && !Ac_Util::sameInArray($this, $perk->_tags)) {
                $perk->_tags[] = $this;
        }
        
    }

    /**
     * @return Sample_Perk  
     */
    function createPerk($values = array()) {
        $m = $this->getMapper('Sample_Perk_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addPerk($res);
        return $res;
    }
    

    function getPerkIds() {
        if ($this->_perkIds === false) {
            $this->mapper->loadPerkIdsFor($this);
        }
        return $this->_perkIds;
    }
    
    function setPerkIds($perkIds) {
        if (!is_array($perkIds)) trigger_error('$perkIds must be an array', E_USER_ERROR);
        $this->_perkIds = $perkIds;
        $this->_perksLoaded = false;
        $this->_perks = false; 
    }
    
    function clearPerks() {
        $this->_perks = array();
        $this->_perksLoaded = true;
        $this->_perkIds = false;
    }               
  
    
}

