<?php

class Child_Tag_Base_Object extends Sample_Tag {

    
    var $_mapperClass = 'Child_Tag_Mapper';
    
    /**
     * @var Child_Tag_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Tag_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'people' => [
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
                'caption' => 'People',
            ],
            'personIds' => [
                'values' => [
                    'mapperClass' => 'Child_Person_Mapper',
                ],
            ],
            'perks' => [
                'className' => 'Child_Perk',
                'mapperClass' => 'Child_Perk_Mapper',
                'caption' => 'Perks',
            ],
            'perkIds' => [
                'values' => [
                    'mapperClass' => 'Child_Perk_Mapper',
                ],
            ],
            'tagId' => [
                'caption' => 'Tag Id',
            ],
            'title' => [
                'caption' => 'Title',
            ],
            'titleM' => [
                'caption' => 'TitleM',
            ],
            'titleF' => [
                'caption' => 'TitleF',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Person 
     */
    function getPerson($id) {
        return parent::getPerson($id);
    }
    
    /**
     * @return Child_Person 
     */
    function getPeopleItem($id) {
        return parent::getPeopleItem($id);
    }
    
    /**
     * @param Child_Person $person 
     */
    function addPerson($person) {
        if (!is_a($person, 'Child_Person'))
            trigger_error('$person must be an instance of Child_Person', E_USER_ERROR);
        return parent::addPerson($person);
    }
    
    /**
     * @return Child_Person  
     */
    function createPerson($values = array()) {
        return parent::createPerson($values);
    }

    

        
    
    /**
     * @return Child_Perk 
     */
    function getPerk($id) {
        return parent::getPerk($id);
    }
    
    /**
     * @return Child_Perk 
     */
    function getPerksItem($id) {
        return parent::getPerksItem($id);
    }
    
    /**
     * @param Child_Perk $perk 
     */
    function addPerk($perk) {
        if (!is_a($perk, 'Child_Perk'))
            trigger_error('$perk must be an instance of Child_Perk', E_USER_ERROR);
        return parent::addPerk($perk);
    }
    
    /**
     * @return Child_Perk  
     */
    function createPerk($values = array()) {
        return parent::createPerk($values);
    }

    

  
    
}

