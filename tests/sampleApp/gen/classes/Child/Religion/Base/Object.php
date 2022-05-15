<?php
/**
 * @property Child $app Access to App instance (via Mapper)
 */
class Child_Religion_Base_Object extends Sample_Religion {

    
    var $_mapperClass = 'Child_Religion_Mapper';
    
    /**
     * @var Child_Religion_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Child_Religion_Mapper 
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
            'religionId' => [
                'caption' => 'Religion Id',
            ],
            'title' => [
                'caption' => 'Title',
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

    

  
    
}

