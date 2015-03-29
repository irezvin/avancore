<?php

class Sample_Relation_Type_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $_relations = false;
    public $_relationsCount = false;
    public $_relationsLoaded = false;
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
 
    
    protected function listOwnProperties() {
        return array ( 0 => 'relations', 1 => 'relationTypeId', 2 => 'title', 3 => 'isSymmetrical', );
    }
 
    protected function listOwnLists() {
        
        return array ( 'relations' => 'relations', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'relations' => 'Sample_Relation', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
            'relations' => array (
                'className' => 'Sample_Relation',
                'mapperClass' => 'Sample_Relation_Mapper',
                'caption' => 'Relations',
                'relationId' => '_relations',
                'countVarName' => '_relationsCount',
                'referenceVarName' => '_relations',
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
            $this->mapper->loadAssocCountFor($this, '_relations');
        }
        return $this->_relationsCount;
    }

    function listRelations() {
        if (!$this->_relationsLoaded) {
            $this->mapper->loadRelationsFor($this);
        }
        return array_keys($this->_relations);
    }
    
    /**
     * @return bool
     */
    function isRelationsLoaded() {
        return $this->_relationsLoaded;
    }
    
    /**
     * @return Sample_Relation 
     */
    function getRelation($id) {
        if (!$this->_relationsLoaded) {
            $this->mapper->loadRelationsFor($this);
        }
        if (!isset($this->_relations[$id])) trigger_error ('No such Relation: \''.$id.'\'', E_USER_ERROR);
        return $this->_relations[$id];
    }
    
    /**
     * @return Sample_Relation 
     */
    function getRelationsItem($id) {
        return $this->getRelation($id);
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
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addRelation($res);
        return $res;
    }
    
  
    
}

