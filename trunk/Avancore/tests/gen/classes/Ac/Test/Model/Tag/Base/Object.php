<?php

Ac_Dispatcher::loadClass('Ac_Model_Object');

class Ac_Test_Model_Tag_Base_Object extends Ac_Model_Object {
    
    var $_people = false;
    var $_peopleCount = false;
    var $_peopleIds = false;
    var $tagId = NULL;
    var $title = '';
    var $titleM = NULL;
    var $titleF = NULL;
    
    var $_mapperClass = 'Ac_Test_Model_Tag_Mapper';
    
    function Ac_Test_Model_Tag_Base_Object() {
        parent::Ac_Model_Object ('#__tags', 'tagId');
    }
    
    function listOwnProperties() {
        return array ( 'people', 'peopleIds', 'tagId', 'title', 'titleM', 'titleF', );
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
              'peopleIds' => array (  
                  'dataType' => 'int',
                  'arrayValue' => true,
              ),
              'tagId' => array (  
                  'dataType' => 'int',
                  'maxLength' => '10',
                  'attribs' => array (    
                      'size' => '6',
                  ),
                  'caption' => 'Tag Id',
                  'isEnabled' => true,
              ),
              'title' => array (  
                  'maxLength' => '45',
                  'caption' => 'Title',
                  'isEnabled' => true,
              ),
              'titleM' => array (  
                  'maxLength' => '45',
                  'isNullable' => true,
                  'caption' => 'Title M',
                  'isEnabled' => true,
              ),
              'titleF' => array (  
                  'maxLength' => '45',
                  'isNullable' => true,
                  'caption' => 'Title F',
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
    

    function getPeopleIds() {
        if ($this->_peopleIds === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocNNIdsFor($this, '_people');
        }
        return $this->_peopleIds;
    }
    
    function setPeopleIds($peopleIds) {
        if (!is_array($peopleIds)) trigger_error('$peopleIds must be an array', E_USER_ERROR);
        $this->_peopleIds = $peopleIds;
        $this->_people = false; 
    }
    
    function clearPeople() {
        $this->_people = array();
        $this->_peopleIds = false;
    }               
  

    function _storeNNRecords() {
        $res = parent::_storeNNRecords() !== false;
        $mapper = & $this->getMapper();
        
        if (is_array($this->_people) || is_array($this->_peopleIds)) {
            $rel = & $mapper->getRelation('_people');
            if (!$this->_autoStoreNNRecords($this->_people, $this->_peopleIds, $rel->fieldLinks, $rel->fieldLinks2, $rel->midTableName, 'people')) 
                $res = false;
        }
            
        return $res; 
    }
    
}

?>