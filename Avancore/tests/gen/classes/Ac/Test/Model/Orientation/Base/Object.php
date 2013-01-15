<?php

Ac_Dispatcher::loadClass('Ac_Model_Object');

class Ac_Test_Model_Orientation_Base_Object extends Ac_Model_Object {
    
    var $_people = false;
    var $_peopleCount = false;
    var $sexualOrientationId = NULL;
    var $title = '';
    
    var $_mapperClass = 'Ac_Test_Model_Orientation_Mapper';
    
    function Ac_Test_Model_Orientation_Base_Object() {
        parent::Ac_Model_Object ('#__orientation', 'sexualOrientationId');
    }
    
    function listOwnProperties() {
        return array ( 'people', 'sexualOrientationId', 'title', );
    }

    function listOwnLists() {
        return array ( 'people' => 'people', );
    }

    function listOwnAssociations() {
        return array ( 'people' => 'Ac_Test_Model_People', );
    }

    function getOwnPropertiesInfo() {
    
        return array (
              'people' => array (  
                  'className' => 'Ac_Test_Model_People',
                  'mapperClass' => 'Ac_Test_Model_People_Mapper',
                  'relationId' => '_people',
                  'caption' => 'People',
                  'isEnabled' => true,
              ),
              'sexualOrientationId' => array (  
                  'dataType' => 'int',
                  'maxLength' => '10',
                  'attribs' => array (    
                      'size' => '6',
                  ),
                  'caption' => 'Sexual Orientation Id',
                  'isEnabled' => true,
              ),
              'title' => array (  
                  'maxLength' => '45',
                  'caption' => 'Title',
                  'isEnabled' => true,
              ),
        );
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }

    function countPeople() {
        if (is_array($this->_people)) return count($this->_people);
        if ($this->_peopleCount === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocCountFor($this, '_people');
        }
        return $this->_peopleCount;
    }

    function listPeople() {
        if ($this->_people === false) {
            $mapper = & $this->getMapper();
            $mapper->listAssocFor($this, '_people');
        }
        return array_keys($this->_people);
    }
    
    /**
     * @return Ac_Test_Model_People 
     */
    function & getPeople($id) {
        if ($this->_people === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocFor($this, '_people');
        }
        if (!isset($this->_people[$id])) trigger_error ('No such People: \''.$id.'\'', E_USER_ERROR);
        if ($this->_people[$id] === false) {
        }
        return $this->_people[$id];
    }
    
    /**
     * @param Ac_Test_Model_People $people 
     */
    function addPeople(& $people) {
        if (!is_a($people, 'Ac_Test_Model_People')) trigger_error('$people must be an instance of Ac_Test_Model_People', E_USER_ERROR);
        $this->listPeople();
        $this->_people[] = & $people;
    }
    
    /**
     * @return Ac_Test_Model_People  
     */
    function & createPeople($values = array(), $isReference = false) {
        $m = & $this->getMapper('Ac_Test_Model_People_Mapper');
        $res = & $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->setIsReference(true);
        $this->addPeople($res);
        return $res;
    }
    
  

    function _storeDownstandingRecords() {
        $res = parent::_storeDownstandingRecords() !== false;
        $mapper = & $this->getMapper();

        if (is_array($this->_people)) {
            $rel = & $mapper->getRelation('_people');
            if (!$this->_autoStoreDownstanding($this->_people, $rel->fieldLinks, 'people')) $res = false;
        }
        return $res; 
    }
    
}

?>