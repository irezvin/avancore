<?php

class Sample_Orientation_Base_Object extends Ac_Model_Object {
    
    var $_people = false;
    var $_peopleCount = false;
    var $sexualOrientationId = NULL;
    var $title = '';
    
    var $_mapperClass = 'Sample_Orientation_Mapper';

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    function listOwnProperties() {
        
        return array ( 'people', 'sexualOrientationId', 'title', );
        
    }

    function listOwnLists() {
        
        return array ( 'people' => 'people', );
        
    }

    function listOwnAssociations() {
        
        return array ( 'people' => 'Sample_People', );
        
    }

    function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
              'people' => array (
                  'className' => 'Sample_People',
                  'mapperClass' => 'Sample_People_Mapper',
                  'relationId' => '_people',
                  'caption' => 'People',
              ),
              'sexualOrientationId' => array (
                  'dataType' => 'int',
                  'maxLength' => '10',
                  'attribs' => array (
                      'size' => '6',
                  ),
                  'caption' => 'Sexual Orientation Id',
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
        if ($this->_people === false) {
            $mapper = $this->getMapper();
            $mapper->listAssocFor($this, '_people');
        }
        return array_keys($this->_people);
    }
    
    /**
     * @return Sample_People 
     */
    function getPeople($id) {
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
     * @param Sample_People $people 
     */
    function addPeople(& $people) {
        if (!is_a($people, 'Sample_People')) trigger_error('$people must be an instance of Sample_People', E_USER_ERROR);
        $this->listPeople();
        $this->_people[] = $people;
        
        $people->_orientation = $this;
        
    }
    
    /**
     * @return Sample_People  
     */
    function createPeople($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_People_Mapper');
        $res = $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addPeople($res);
        return $res;
    }
    
  

    function _storeDownstandingRecords() {
        $res = parent::_storeDownstandingRecords() !== false;
        $mapper = $this->getMapper();

        if (is_array($this->_people)) {
            $rel = $mapper->getRelation('_people');
            if (!$this->_autoStoreDownstanding($this->_people, $rel->fieldLinks, 'people')) $res = false;
        }
        return $res; 
    }
    
}

