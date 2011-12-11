<?php

Ae_Dispatcher::loadClass('Ae_Model_Object');

class Ae_Test_Model_Relation_Type_Base_Object extends Ae_Model_Object {
    
    var $_relations = false;
    var $_relationsCount = false;
    var $relationTypeId = NULL;
    var $title = '';
    var $isSymmetrical = 0;
    
    var $_mapperClass = 'Ae_Test_Model_Relation_Type_Mapper';
    
    function Ae_Test_Model_Relation_Type_Base_Object() {
        parent::Ae_Model_Object ('#__relation_types', 'relationTypeId');
    }
    
    function listOwnProperties() {
        return array ( 'relations', 'relationTypeId', 'title', 'isSymmetrical', );
    }

    function listOwnLists() {
        return array ( 'relations' => 'relations', );
    }

    function listOwnAssociations() {
        return array ( 'relations' => 'Ae_Test_Model_Relation', );
    }

    function getOwnPropertiesInfo() {
    
        return array (
              'relations' => array (  
                  'className' => 'Ae_Test_Model_Relation',
                  'mapperClass' => 'Ae_Test_Model_Relation_Mapper',
                  'relationId' => '_relations',
                  'caption' => 'Relations',
                  'isEnabled' => true,
              ),
              'relationTypeId' => array (  
                  'dataType' => 'int',
                  'maxLength' => '10',
                  'attribs' => array (    
                      'size' => '6',
                  ),
                  'caption' => 'Relation Type Id',
                  'isEnabled' => true,
              ),
              'title' => array (  
                  'maxLength' => '45',
                  'caption' => 'Title',
                  'isEnabled' => true,
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
                  'isEnabled' => true,
              ),
        );
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }

    function countRelations() {
        if (is_array($this->_relations)) return count($this->_relations);
        if ($this->_relationsCount === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocCountFor($this, '_relations');
        }
        return $this->_relationsCount;
    }

    function listRelations() {
        if ($this->_relations === false) {
            $mapper = & $this->getMapper();
            $mapper->listAssocFor($this, '_relations');
        }
        return array_keys($this->_relations);
    }
    
    /**
     * @return Ae_Test_Model_Relation 
     */
    function & getRelation($id) {
        if ($this->_relations === false) {
            $mapper = & $this->getMapper();
            $mapper->loadAssocFor($this, '_relations');
        }
        if (!isset($this->_relations[$id])) trigger_error ('No such Relation: \''.$id.'\'', E_USER_ERROR);
        if ($this->_relations[$id] === false) {
        }
        return $this->_relations[$id];
    }
    
    /**
     * @param Ae_Test_Model_Relation $relation 
     */
    function addRelation(& $relation) {
        if (!is_a($relation, 'Ae_Test_Model_Relation')) trigger_error('$relation must be an instance of Ae_Test_Model_Relation', E_USER_ERROR);
        $this->listRelations();
        $this->_relations[] = & $relation;
    }
    
    /**
     * @return Ae_Test_Model_Relation  
     */
    function & createRelation($values = array(), $isReference = false) {
        $m = & $this->getMapper('Ae_Test_Model_Relation_Mapper');
        $res = & $m->factory();
        if ($values) $res->bind($values);
        if ($isReference) $res->setIsReference(true);
        $this->addRelation($res);
        return $res;
    }
    
  

    function _storeDownstandingRecords() {
        $res = parent::_storeDownstandingRecords() !== false;
        $mapper = & $this->getMapper();

        if (is_array($this->_relations)) {
            $rel = & $mapper->getRelation('_relations');
            if (!$this->_autoStoreDownstanding($this->_relations, $rel->fieldLinks, 'relations')) $res = false;
        }
        return $res; 
    }
    
}

?>