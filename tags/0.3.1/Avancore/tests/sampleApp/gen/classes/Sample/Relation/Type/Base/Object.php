<?php

class Sample_Relation_Type_Base_Object extends Ac_Model_Object {

    public $_relations = false;
    public $_relationsCount = false;
    public $relationTypeId = NULL;
    public $title = '';
    public $isSymmetrical = 0;
    
    var $_mapperClass = 'Sample_Relation_Type_Mapper';
    
    /**
     * @var Sample_Relation_Type_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Relation_Type_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    function listOwnProperties() {
        
        return array ( 'relations', 'relationTypeId', 'title', 'isSymmetrical', );
        
    }

    function listOwnLists() {
        
        return array ( 'relations' => 'relations', );
        
    }

    function listOwnAssociations() {
        
        return array ( 'relations' => 'Sample_Relation', );
        
    }

    function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
              'relations' => array (
                  'className' => 'Sample_Relation',
                  'mapperClass' => 'Sample_Relation_Mapper',
                  'caption' => 'Relations',
                  'relationId' => '_relations',
              ),
              'relationTypeId' => array (
                  'dataType' => 'int',
                  'maxLength' => '10',
                  'attribs' => array (
                      'size' => '6',
                  ),
                  'caption' => 'Relation Type Id',
              ),
              'title' => array (
                  'maxLength' => '45',
                  'caption' => 'Title',
              ),
              'isSymmetrical' => array (
                  'dataType' => 'bool',
                  'controlType' => 'selectList',
                  'maxLength' => '1',
                  'valueList' => array (
                      0 => 'No',
                      1 => 'Yes',
                  ),
                  'caption' => 'Is Symmetrical',
              ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }

    function countRelations() {
        if (is_array($this->_relations)) return count($this->_relations);
        if ($this->_relationsCount === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocCountFor($this, '_relations');
        }
        return $this->_relationsCount;
    }

    function listRelations() {
        if ($this->_relations === false) {
            $mapper = $this->getMapper();
            $mapper->listAssocFor($this, '_relations');
        }
        return array_keys($this->_relations);
    }
    
    /**
     * @return Sample_Relation 
     */
    function getRelation($id) {
        if ($this->_relations === false) {
            $mapper = $this->getMapper();
            $mapper->loadAssocFor($this, '_relations');
        }
        if (!isset($this->_relations[$id])) trigger_error ('No such Relation: \''.$id.'\'', E_USER_ERROR);
        if ($this->_relations[$id] === false) {
        }
        return $this->_relations[$id];
    }
    
    /**
     * @param Sample_Relation $relation 
     */
    function addRelation($relation) {
        if (!is_a($relation, 'Sample_Relation')) trigger_error('$relation must be an instance of Sample_Relation', E_USER_ERROR);
        $this->listRelations();
        $this->_relations[] = $relation;
        
        $relation->_relationType = $this;
        
    }
    
    /**
     * @return Sample_Relation  
     */
    function createRelation($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Relation_Mapper');
        $res = $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addRelation($res);
        return $res;
    }
    
  

    function _storeReferencingRecords() {
        $res = parent::_storeReferencingRecords() !== false;
        $mapper = $this->getMapper();

        if (is_array($this->_relations)) {
            $rel = $mapper->getRelation('_relations');
            if (!$this->_autoStoreReferencing($this->_relations, $rel->fieldLinks, 'relations')) $res = false;
        }
        return $res; 
    }
    
}

