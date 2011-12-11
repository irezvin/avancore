<?php

Ae_Dispatcher::loadClass('Ae_Model_Object');

class Ae_Test_Model_People_Base_Object extends Ae_Model_Object {
    
    var $_orientation = false;
    var $_tags = false;
    var $_tagsCount = false;
    var $_tagIds = false;
    var $_outgoingRelations = false;
    var $_outgoingRelationsCount = false;
    var $_incomingRelations = false;
    var $_incomingRelationsCount = false;
    var $personId = NULL;
    var $name = '';
    var $gender = 'F';
    var $isSingle = 1;
    var $birthDate = NULL;
    var $lastUpdatedDatetime = NULL;
    var $createdTs = false;
    var $sexualOrientationId = NULL;
    
    var $_mapperClass = 'Ae_Test_Model_People_Mapper';
    
    function Ae_Test_Model_People_Base_Object() {
        parent::Ae_Model_Object ('#__people', 'personId');
    }
    
    function listOwnProperties() {
        return array ( 'orientation', 'tags', 'tagIds', 'outgoingRelations', 'incomingRelations', 'personId', 'name', 'gender', 'isSingle', 'birthDate', 'lastUpdatedDatetime', 'createdTs', 'sexualOrientationId', );
    }

    function listOwnLists() {
        return array ( 'tags' => 'tags', 'outgoingRelations' => 'outgoingRelations', 'incomingRelations' => 'incomingRelations', );
    }

    function listOwnAssociations() {
        return array ( 'orientation' => 'Ae_Test_Model_Orientation', 'tags' => 'Ae_Test_Model_Tag', 'outgoingRelations' => 'Ae_Test_Model_Relation', 'incomingRelations' => 'Ae_Test_Model_Relation', );
    }

    function getOwnPropertiesInfo() {
    
        return array (
              'orientation' => array (  
                  'className' => 'Ae_Test_Model_Orientation',
                  'mapperClass' => 'Ae_Test_Model_Orientation_Mapper',
                  'relationId' => '_orientation',
                  'caption' => 'Orientation',
                  'isEnabled' => true,
              ),
              'tags' => array (  
                  'className' => 'Ae_Test_Model_Tag',
                  'mapperClass' => 'Ae_Test_Model_Tag_Mapper',
                  'relationId' => '_tags',
                  'caption' => 'Tags',
                  'isEnabled' => true,
              ),
              'tagIds' => array (  
                  'dataType' => 'int',
                  'arrayValue' => true,
              ),
              'outgoingRelations' => array (  
                  'className' => 'Ae_Test_Model_Relation',
                  'mapperClass' => 'Ae_Test_Model_Relation_Mapper',
                  'relationId' => '_outgoingRelations',
                  'otherModelIdInMethodsSingle' => 'outgoingRelation',
                  'otherModelIdInMethodsPlural' => 'outgoingRelations',
                  'caption' => 'Relations',
                  'isEnabled' => true,
              ),
              'incomingRelations' => array (  
                  'className' => 'Ae_Test_Model_Relation',
                  'mapperClass' => 'Ae_Test_Model_Relation_Mapper',
                  'relationId' => '_incomingRelations',
                  'otherModelIdInMethodsSingle' => 'incomingRelation',
                  'otherModelIdInMethodsPlural' => 'incomingRelations',
                  'caption' => 'Relations',
                  'isEnabled' => true,
              ),
              'personId' => array (  
                  'dataType' => 'int',
                  'maxLength' => '10',
                  'attribs' => array (    
                      'size' => '6',
                  ),
                  'caption' => 'Person Id',
                  'isEnabled' => true,
              ),
              'name' => array (  
                  'maxLength' => '255',
                  'caption' => 'Name',
                  'isEnabled' => true,
              ),
              'gender' => array (  
                  'controlType' => 'selectList',
                  'valueList' => array (    
                      'F' => 'F',
                      'M' => 'M',
                  ),
                  'caption' => 'Gender',
                  'isEnabled' => true,
              ),
              'isSingle' => array (  
                  'dataType' => 'bool',
                  'controlType' => 'selectList',
                  'maxLength' => '1',
                  'valueList' => array (    
                      0 => 'No',
                      1 => 'Yes',
                  ),
                  'caption' => 'Is Single',
                  'isEnabled' => true,
              ),
              'birthDate' => array (  
                  'dataType' => 'date',
                  'controlType' => 'dateInput',
                  'caption' => 'Birth Date',
                  'isEnabled' => true,
                  'internalDateFormat' => 'Y-m-d',
                  'outputDateFormat' => 'Y-m-d',
              ),
              'lastUpdatedDatetime' => array (  
                  'dataType' => 'dateTime',
                  'controlType' => 'dateInput',
                  'isNullable' => true,
                  'caption' => 'Last Updated Datetime',
                  'isEnabled' => true,
                  'internalDateFormat' => 'Y-m-d H:i:s',
                  'outputDateFormat' => 'Y-m-d H:i:s',
              ),
              'createdTs' => array (  
                  'dataType' => 'timestamp',
                  'controlType' => 'dateInput',
                  'caption' => 'Created Ts',
                  'isEnabled' => true,
                  'internalDateFormat' => 'YmdHis',
                  'outputDateFormat' => 'Y-m-d H:i:s',
              ),
              'sexualOrientationId' => array (  
                  'dataType' => 'int',
                  'controlType' => 'selectList',
                  'maxLength' => '10',
                  'values' => array (    
                      'class' => 'Ae_Model_Values_Records',
                      'mapperClass' => 'Ae_Test_Model_Orientation_Mapper',
                  ),
                  'objectPropertyName' => 'orientation',
                  'isNullable' => true,
                  'caption' => 'Sexual Orientation Id',
                  'isEnabled' => true,
              ),
        );
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }
        
    
    /**
     * @return Ae_Test_Model_Orientation 
     */
    function & getOrientation() {
        if ($this->_orientation === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocFor($this, '_orientation');
        }
        return $this->_orientation;
    }
    
    /**
     * @param Ae_Test_Model_Orientation $orientation 
     */
    function setOrientation(& $orientation) {
        if ($orientation === false) $this->_orientation = false;
        elseif ($orientation === null) $this->_orientation = null;
        else {
            if (!is_a($orientation, 'Ae_Test_Model_Orientation')) trigger_error('$orientation must be an instance of Ae_Test_Model_Orientation', E_USER_ERROR);
            if (!is_object($this->_orientation) && !Ae_Util::sameObject($this->_orientation, $orientation)) { 
                $this->_orientation = & $orientation;
            }
        }
    }
    
    function clearOrientation() {
        $this->orientation = null;
    }
    
    /**
     * @return Ae_Test_Model_Orientation  
     */
    function & createOrientation($values = array(), $isReference = false) {
        $m = & $this->getMapper('Ae_Test_Model_Orientation_Mapper');
        $res = & $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->setIsReference(true);
        $this->setOrientation($res);
        return $res;
    }
    

    function countTags() {
        if (is_array($this->_tags)) return count($this->_tags);
        if ($this->_tagsCount === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocCountFor($this, '_tags');
        }
        return $this->_tagsCount;
    }

    function listTags() {
        if ($this->_tags === false) {
            $mapper = & $this->getMapper();
            $mapper->listAssocFor($this, '_tags');
        }
        return array_keys($this->_tags);
    }
    
    /**
     * @return Ae_Test_Model_Tag 
     */
    function & getTag($id) {
        if ($this->_tags === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocFor($this, '_tags');
        }
        if (!isset($this->_tags[$id])) trigger_error ('No such Tag: \''.$id.'\'', E_USER_ERROR);
        if ($this->_tags[$id] === false) {
        }
        return $this->_tags[$id];
    }
    
    /**
     * @param Ae_Test_Model_Tag $tag 
     */
    function addTag(& $tag) {
        if (!is_a($tag, 'Ae_Test_Model_Tag')) trigger_error('$tag must be an instance of Ae_Test_Model_Tag', E_USER_ERROR);
        $this->listTags();
        $this->_tags[] = & $tag;
    }
    
    /**
     * @return Ae_Test_Model_Tag  
     */
    function & createTag($values = array(), $isReference = false) {
        $m = & $this->getMapper('Ae_Test_Model_Tag_Mapper');
        $res = & $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->setIsReference(true);
        $this->addTag($res);
        return $res;
    }
    

    function getTagIds() {
        if ($this->_tagIds === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocNNIdsFor($this, '_tags');
        }
        return $this->_tagIds;
    }
    
    function setTagIds($tagIds) {
        if (!is_array($tagIds)) trigger_error('$tagIds must be an array', E_USER_ERROR);
        $this->_tagIds = $tagIds;
        $this->_tags = false; 
    }
    
    function clearTags() {
        $this->_tags = array();
        $this->_tagIds = false;
    }               

    function countOutgoingRelations() {
        if (is_array($this->_outgoingRelations)) return count($this->_outgoingRelations);
        if ($this->_outgoingRelationsCount === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocCountFor($this, '_outgoingRelations');
        }
        return $this->_outgoingRelationsCount;
    }

    function listOutgoingRelations() {
        if ($this->_outgoingRelations === false) {
            $mapper = & $this->getMapper();
            $mapper->listAssocFor($this, '_outgoingRelations');
        }
        return array_keys($this->_outgoingRelations);
    }
    
    /**
     * @return Ae_Test_Model_Relation 
     */
    function & getOutgoingRelation($id) {
        if ($this->_outgoingRelations === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocFor($this, '_outgoingRelations');
        }
        if (!isset($this->_outgoingRelations[$id])) trigger_error ('No such Relation: \''.$id.'\'', E_USER_ERROR);
        if ($this->_outgoingRelations[$id] === false) {
        }
        return $this->_outgoingRelations[$id];
    }
    
    /**
     * @param Ae_Test_Model_Relation $outgoingRelation 
     */
    function addOutgoingRelation(& $outgoingRelation) {
        if (!is_a($outgoingRelation, 'Ae_Test_Model_Relation')) trigger_error('$outgoingRelation must be an instance of Ae_Test_Model_Relation', E_USER_ERROR);
        $this->listOutgoingRelations();
        $this->_outgoingRelations[] = & $outgoingRelation;
    }
    
    /**
     * @return Ae_Test_Model_Relation  
     */
    function & createOutgoingRelation($values = array(), $isReference = false) {
        $m = & $this->getMapper('Ae_Test_Model_Relation_Mapper');
        $res = & $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->setIsReference(true);
        $this->addOutgoingRelation($res);
        return $res;
    }
    

    function countIncomingRelations() {
        if (is_array($this->_incomingRelations)) return count($this->_incomingRelations);
        if ($this->_incomingRelationsCount === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocCountFor($this, '_incomingRelations');
        }
        return $this->_incomingRelationsCount;
    }

    function listIncomingRelations() {
        if ($this->_incomingRelations === false) {
            $mapper = & $this->getMapper();
            $mapper->listAssocFor($this, '_incomingRelations');
        }
        return array_keys($this->_incomingRelations);
    }
    
    /**
     * @return Ae_Test_Model_Relation 
     */
    function & getIncomingRelation($id) {
        if ($this->_incomingRelations === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocFor($this, '_incomingRelations');
        }
        if (!isset($this->_incomingRelations[$id])) trigger_error ('No such Relation: \''.$id.'\'', E_USER_ERROR);
        if ($this->_incomingRelations[$id] === false) {
        }
        return $this->_incomingRelations[$id];
    }
    
    /**
     * @param Ae_Test_Model_Relation $incomingRelation 
     */
    function addIncomingRelation(& $incomingRelation) {
        if (!is_a($incomingRelation, 'Ae_Test_Model_Relation')) trigger_error('$incomingRelation must be an instance of Ae_Test_Model_Relation', E_USER_ERROR);
        $this->listIncomingRelations();
        $this->_incomingRelations[] = & $incomingRelation;
    }
    
    /**
     * @return Ae_Test_Model_Relation  
     */
    function & createIncomingRelation($values = array(), $isReference = false) {
        $m = & $this->getMapper('Ae_Test_Model_Relation_Mapper');
        $res = & $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->setIsReference(true);
        $this->addIncomingRelation($res);
        return $res;
    }
    
  

    function _storeUpstandingRecords() {
        $res = parent::_storeUpstandingRecords() !== false;
        $mapper = & $this->getMapper();

        if (is_object($this->_orientation)) {
            $rel = & $mapper->getRelation('_orientation');
            if (!$this->_autoStoreUpstanding($this->_orientation, $rel->fieldLinks, 'orientation')) $res = false;
        }
 
        return $res;
    }

    function _storeDownstandingRecords() {
        $res = parent::_storeDownstandingRecords() !== false;
        $mapper = & $this->getMapper();

        if (is_array($this->_outgoingRelations)) {
            $rel = & $mapper->getRelation('_outgoingRelations');
            if (!$this->_autoStoreDownstanding($this->_outgoingRelations, $rel->fieldLinks, 'outgoingRelations')) $res = false;
        }

        if (is_array($this->_incomingRelations)) {
            $rel = & $mapper->getRelation('_incomingRelations');
            if (!$this->_autoStoreDownstanding($this->_incomingRelations, $rel->fieldLinks, 'incomingRelations')) $res = false;
        }
        return $res; 
    }

    function _storeNNRecords() {
        $res = parent::_storeNNRecords() !== false;
        $mapper = & $this->getMapper();
        
        if (is_array($this->_tags) || is_array($this->_tagIds)) {
            $rel = & $mapper->getRelation('_tags');
            if (!$this->_autoStoreNNRecords($this->_tags, $this->_tagIds, $rel->fieldLinks, $rel->fieldLinks2, $rel->midTableName, 'tags')) 
                $res = false;
        }
            
        return $res; 
    }
    
}

?>