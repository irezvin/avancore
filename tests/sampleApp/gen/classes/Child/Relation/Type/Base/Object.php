<?php
/**
 * @property Child $app Access to App instance (via Mapper)
 */
class Child_Relation_Type_Base_Object extends Sample_Relation_Type {

    
    var $_mapperClass = 'Child_Relation_Type_Mapper';
    
    /**
     * @var Child_Relation_Type_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Child_Relation_Type_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'relations' => [
                'className' => 'Child_Relation',
                'mapperClass' => 'Child_Relation_Mapper',
                'caption' => 'Relations',
            ],
            'relationTypeId' => [
                'caption' => 'Relation Type Id',
            ],
            'title' => [
                'caption' => 'Title',
            ],
            'isSymmetrical' => [
                'caption' => 'Is Symmetrical',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Relation 
     */
    function getRelation($id) {
        return parent::getRelation($id);
    }
    
    /**
     * @return Child_Relation 
     */
    function getRelationsItem($id) {
        return parent::getRelationsItem($id);
    }
    
    /**
     * @param Child_Relation $relation 
     */
    function addRelation($relation) {
        if (!is_a($relation, 'Child_Relation'))
            trigger_error('$relation must be an instance of Child_Relation', E_USER_ERROR);
        return parent::addRelation($relation);
    }
    
    /**
     * @return Child_Relation  
     */
    function createRelation($values = array()) {
        return parent::createRelation($values);
    }

    

  
    
}

