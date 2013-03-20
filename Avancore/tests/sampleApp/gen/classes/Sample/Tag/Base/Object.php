<?php

class Sample_Tag_Base_Object extends Ac_Model_Object {
    
    var $_people = false;
    var $_peopleCount = false;
    var $_peopleIds = false;
    var $tagId = NULL;
    var $title = '';
    var $titleM = NULL;
    var $titleF = NULL;
    
    var $_mapperClass = 'Sample_Tag_Mapper';

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    function listOwnProperties() {
        
        return array ( 'people', 'peopleIds', 'tagId', 'title', 'titleM', 'titleF', );
        
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
              'peopleIds' => array (
                  'dataType' => 'int',
                  'arrayValue' => true,
                  'values' => array (
                      'class' => 'Ac_Model_Values_Records',
                      'mapperClass' => 'Sample_People_Mapper',
                  ),
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
        
        if (is_array($people->_tags) && !Ac_Util::sameInArray($this, $people->_tags)) {
                $people->_tags[] = $this;
        }
        
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
    

    function getPeopleIds() {
        if ($this->_peopleIds === false) {
            $mapper = $this->getMapper();
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
        $mapper = $this->getMapper();
        
        if (is_array($this->_people) || is_array($this->_peopleIds)) {
            $rel = $mapper->getRelation('_people');
            if (!$this->_autoStoreNNRecords($this->_people, $this->_peopleIds, $rel->fieldLinks, $rel->fieldLinks2, $rel->midTableName, 'people')) 
                $res = false;
        }
            
        return $res; 
    }
    
}

