<?php

class Sample_Person_Base_Object extends Ac_Model_Object {

    public $_orientation = false;
    public $_tags = false;
    public $_tagsCount = false;
    public $_tagIds = false;
    public $_incomingRelations = false;
    public $_incomingRelationsCount = false;
    public $_outgoingRelations = false;
    public $_outgoingRelationsCount = false;
    public $personId = NULL;
    public $name = '';
    public $gender = 'F';
    public $isSingle = 1;
    public $birthDate = NULL;
    public $lastUpdatedDatetime = NULL;
    public $createdTs = false;
    public $sexualOrientationId = NULL;
    
    var $_mapperClass = 'Sample_Person_Mapper';
    
    /**
     * @var Sample_Person_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Person_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array ( 'orientation', 'tags', 'tagIds', 'incomingRelations', 'outgoingRelations', 'personId', 'name', 'gender', 'isSingle', 'birthDate', 'lastUpdatedDatetime', 'createdTs', 'sexualOrientationId', );
    }
 
    protected function listOwnLists() {
        
        return array ( 'tags' => 'tags', 'incomingRelations' => 'incomingRelations', 'outgoingRelations' => 'outgoingRelations', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'orientation' => 'Sample_Orientation', 'tags' => 'Sample_Tag', 'incomingRelations' => 'Sample_Relation', 'outgoingRelations' => 'Sample_Relation', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
              'orientation' => array (
                  'className' => 'Sample_Orientation',
                  'mapperClass' => 'Sample_Orientation_Mapper',
                  'caption' => 'Orientation',
                  'relationId' => '_orientation',
              ),
              'tags' => array (
                  'className' => 'Sample_Tag',
                  'mapperClass' => 'Sample_Tag_Mapper',
                  'caption' => 'Tags',
                  'relationId' => '_tags',
              ),
              'tagIds' => array (
                  'dataType' => 'int',
                  'arrayValue' => true,
                  'controlType' => 'selectList',
                  'values' => array (
                      'class' => 'Ac_Model_Values_Records',
                      'mapperClass' => 'Sample_Tag_Mapper',
                  ),
                  'showInTable' => false,
              ),
              'incomingRelations' => array (
                  'className' => 'Sample_Relation',
                  'mapperClass' => 'Sample_Relation_Mapper',
                  'otherModelIdInMethodsSingle' => 'incomingRelation',
                  'otherModelIdInMethodsPlural' => 'incomingRelations',
                  'caption' => 'Relations',
                  'relationId' => '_incomingRelations',
              ),
              'outgoingRelations' => array (
                  'className' => 'Sample_Relation',
                  'mapperClass' => 'Sample_Relation_Mapper',
                  'otherModelIdInMethodsSingle' => 'outgoingRelation',
                  'otherModelIdInMethodsPlural' => 'outgoingRelations',
                  'caption' => 'Relations',
                  'relationId' => '_outgoingRelations',
              ),
              'personId' => array (
                  'dataType' => 'int',
                  'maxLength' => '10',
                  'attribs' => array (
                      'size' => '6',
                  ),
                  'caption' => 'Person Id',
              ),
              'name' => array (
                  'maxLength' => '255',
                  'caption' => 'Name',
              ),
              'gender' => array (
                  'controlType' => 'selectList',
                  'valueList' => array (
                      'F' => 'F',
                      'M' => 'M',
                  ),
                  'caption' => 'Gender',
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
              ),
              'birthDate' => array (
                  'dataType' => 'date',
                  'controlType' => 'dateInput',
                  'caption' => 'Birth Date',
                  'internalDateFormat' => 'Y-m-d',
                  'outputDateFormat' => 'Y-m-d',
              ),
              'lastUpdatedDatetime' => array (
                  'dataType' => 'dateTime',
                  'controlType' => 'dateInput',
                  'isNullable' => true,
                  'caption' => 'Last Updated Datetime',
                  'internalDateFormat' => 'Y-m-d H:i:s',
                  'outputDateFormat' => 'Y-m-d H:i:s',
              ),
              'createdTs' => array (
                  'dataType' => 'timestamp',
                  'controlType' => 'dateInput',
                  'caption' => 'Created Ts',
                  'internalDateFormat' => 'YmdHis',
                  'outputDateFormat' => 'Y-m-d H:i:s',
              ),
              'sexualOrientationId' => array (
                  'dataType' => 'int',
                  'controlType' => 'selectList',
                  'maxLength' => '10',
                  'values' => array (
                      'class' => 'Ac_Model_Values_Records',
                      'mapperClass' => 'Sample_Orientation_Mapper',
                  ),
                  'objectPropertyName' => 'orientation',
                  'isNullable' => true,
                  'caption' => 'Sexual Orientation Id',
              ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }
        
    
    /**
     * @return Sample_Orientation 
     */
    function getOrientation() {
        if ($this->_orientation === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_orientation');
        }
        return $this->_orientation;
    }
    
    /**
     * @param Sample_Orientation $orientation 
     */
    function setOrientation($orientation) {
        if ($orientation === false) $this->_orientation = false;
        elseif ($orientation === null) $this->_orientation = null;
        else {
            if (!is_a($orientation, 'Sample_Orientation')) trigger_error('$orientation must be an instance of Sample_Orientation', E_USER_ERROR);
            if (!is_object($this->_orientation) && !Ac_Util::sameObject($this->_orientation, $orientation)) { 
                $this->_orientation = $orientation;
            }
        }
    }
    
    function clearOrientation() {
        $this->orientation = null;
    }
    
    /**
     * @return Sample_Orientation  
     */
    function createOrientation($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Orientation_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->setOrientation($res);
        return $res;
    }
    

    function countTags() {
        if (is_array($this->_tags)) return count($this->_tags);
        if ($this->_tagsCount === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocCountFor($this, '_tags');
        }
        return $this->_tagsCount;
    }

    function listTags() {
        if ($this->_tags === false) {
            $mapper = $this->getMapper();
            $mapper->listAssocFor($this, '_tags');
        }
        return array_keys($this->_tags);
    }
    
    /**
     * @return Sample_Tag 
     */
    function getTag($id) {
        if ($this->_tags === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_tags');
        }
        if (!isset($this->_tags[$id])) trigger_error ('No such Tag: \''.$id.'\'', E_USER_ERROR);
        if ($this->_tags[$id] === false) {
        }
        return $this->_tags[$id];
    }
    
    /**
     * @param Sample_Tag $tag 
     */
    function addTag($tag) {
        if (!is_a($tag, 'Sample_Tag')) trigger_error('$tag must be an instance of Sample_Tag', E_USER_ERROR);
        $this->listTags();
        $this->_tags[] = $tag;
        
        if (is_array($tag->_people) && !Ac_Util::sameInArray($this, $tag->_people)) {
                $tag->_people[] = $this;
        }
        
    }
    
    /**
     * @return Sample_Tag  
     */
    function createTag($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Tag_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addTag($res);
        return $res;
    }
    

    function getTagIds() {
        if ($this->_tagIds === false) {
            $mapper = $this->getMapper();
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

    function countIncomingRelations() {
        if (is_array($this->_incomingRelations)) return count($this->_incomingRelations);
        if ($this->_incomingRelationsCount === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocCountFor($this, '_incomingRelations');
        }
        return $this->_incomingRelationsCount;
    }

    function listIncomingRelations() {
        if ($this->_incomingRelations === false) {
            $mapper = $this->getMapper();
            $mapper->listAssocFor($this, '_incomingRelations');
        }
        return array_keys($this->_incomingRelations);
    }
    
    /**
     * @return Sample_Relation 
     */
    function getIncomingRelation($id) {
        if ($this->_incomingRelations === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_incomingRelations');
        }
        if (!isset($this->_incomingRelations[$id])) trigger_error ('No such Relation: \''.$id.'\'', E_USER_ERROR);
        if ($this->_incomingRelations[$id] === false) {
        }
        return $this->_incomingRelations[$id];
    }
    
    /**
     * @param Sample_Relation $incomingRelation 
     */
    function addIncomingRelation($incomingRelation) {
        if (!is_a($incomingRelation, 'Sample_Relation')) trigger_error('$incomingRelation must be an instance of Sample_Relation', E_USER_ERROR);
        $this->listIncomingRelations();
        $this->_incomingRelations[] = $incomingRelation;
        
        $incomingRelation->_otherPerson = $this;
        
    }
    
    /**
     * @return Sample_Relation  
     */
    function createIncomingRelation($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Relation_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addIncomingRelation($res);
        return $res;
    }
    

    function countOutgoingRelations() {
        if (is_array($this->_outgoingRelations)) return count($this->_outgoingRelations);
        if ($this->_outgoingRelationsCount === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocCountFor($this, '_outgoingRelations');
        }
        return $this->_outgoingRelationsCount;
    }

    function listOutgoingRelations() {
        if ($this->_outgoingRelations === false) {
            $mapper = $this->getMapper();
            $mapper->listAssocFor($this, '_outgoingRelations');
        }
        return array_keys($this->_outgoingRelations);
    }
    
    /**
     * @return Sample_Relation 
     */
    function getOutgoingRelation($id) {
        if ($this->_outgoingRelations === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_outgoingRelations');
        }
        if (!isset($this->_outgoingRelations[$id])) trigger_error ('No such Relation: \''.$id.'\'', E_USER_ERROR);
        if ($this->_outgoingRelations[$id] === false) {
        }
        return $this->_outgoingRelations[$id];
    }
    
    /**
     * @param Sample_Relation $outgoingRelation 
     */
    function addOutgoingRelation($outgoingRelation) {
        if (!is_a($outgoingRelation, 'Sample_Relation')) trigger_error('$outgoingRelation must be an instance of Sample_Relation', E_USER_ERROR);
        $this->listOutgoingRelations();
        $this->_outgoingRelations[] = $outgoingRelation;
        
        $outgoingRelation->_person = $this;
        
    }
    
    /**
     * @return Sample_Relation  
     */
    function createOutgoingRelation($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Relation_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addOutgoingRelation($res);
        return $res;
    }
    
  

    function _storeReferencedRecords() {
        $res = parent::_storeReferencedRecords() !== false;
        $mapper = $this->getMapper();

        if (is_object($this->_orientation)) {
            $rel = $mapper->getRelation('_orientation');
            if (!$this->_autoStoreReferenced($this->_orientation, $rel->fieldLinks, 'orientation')) $res = false;
        }
 
        return $res;
    }

    function _storeReferencingRecords() {
        $res = parent::_storeReferencingRecords() !== false;
        $mapper = $this->getMapper();

        if (is_array($this->_incomingRelations)) {
            $rel = $mapper->getRelation('_incomingRelations');
            if (!$this->_autoStoreReferencing($this->_incomingRelations, $rel->fieldLinks, 'incomingRelations')) $res = false;
        }

        if (is_array($this->_outgoingRelations)) {
            $rel = $mapper->getRelation('_outgoingRelations');
            if (!$this->_autoStoreReferencing($this->_outgoingRelations, $rel->fieldLinks, 'outgoingRelations')) $res = false;
        }
        return $res; 
    }

    function _storeNNRecords() {
        $res = parent::_storeNNRecords() !== false;
        $mapper = $this->getMapper();
        
        if (is_array($this->_tags) || is_array($this->_tagIds)) {
            $rel = $mapper->getRelation('_tags');
            if (!$this->_autoStoreNNRecords($this->_tags, $this->_tagIds, $rel->fieldLinks, $rel->fieldLinks2, $rel->midTableName, 'tags', $rel->midWhere)) 
                $res = false;
        }
            
        return $res; 
    }
 
    protected function intListReferenceFields() {
        $res = array (
              'sexualOrientationId' => '_orientation',
        );
        return $res;
    }
    
}

