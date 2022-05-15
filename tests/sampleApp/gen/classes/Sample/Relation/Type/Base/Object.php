<?php
/**
 * @property Sample $app Access to App instance (via Mapper)
 */
class Sample_Relation_Type_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_relations = false;

    var $_relationsCount = false;

    var $_relationsLoaded = false;

    var $relationTypeId = NULL;

    var $title = '';

    var $isSymmetrical = 0;
    
    var $_mapperClass = 'Sample_Relation_Type_Mapper';
    
    /**
     * @var Sample_Relation_Type_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Relation_Type_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'relations', ]));
    }
    
 
    protected function listOwnLists() {
        
        return [ 'relations' => 'relations', ];
    }

    
 
    protected function listOwnAssociations() {
        return [ 'relations' => 'Sample_Relation', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'relations' => [
                'className' => 'Sample_Relation',
                'mapperClass' => 'Sample_Relation_Mapper',

                'caption' => new Ac_Lang_String('sample_relation_type_relations'),
                'idPropertyName' => 'relationTypeId',
                'relationId' => '_relations',
                'countVarName' => '_relationsCount',
                'referenceVarName' => '_relations',
            ],
            'relationTypeId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_relation_type_relation_type_id'),
            ],
            'title' => [
                'maxLength' => '45',

                'caption' => new Ac_Lang_String('sample_relation_type_title'),
            ],
            'isSymmetrical' => [
                'dataType' => 'bool',
                'controlType' => 'selectList',
                'maxLength' => '1',
                'valueList' => [
                    0 => 'No',
                    1 => 'Yes',
                ],

                'caption' => new Ac_Lang_String('sample_relation_type_is_symmetrical'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }

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
     * @return Sample_Relation[] 
     */
    function getAllRelations() {
        $res = [];
        foreach ($this->listRelations() as $id)
            $res[] = $this->getRelation($id);
        return $res;
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
    function createRelation($values = array()) {
        $m = $this->getMapper('Sample_Relation_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addRelation($res);
        return $res;
    }
    
  
    
}

