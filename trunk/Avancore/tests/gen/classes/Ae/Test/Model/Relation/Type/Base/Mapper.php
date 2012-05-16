<?php

Ac_Dispatcher::loadClass('Ac_Model_Mapper');

class Ac_Test_Model_Relation_Type_Base_Mapper extends Ac_Model_Mapper {
    
    /**
     * @return Ac_Test_Model_Relation_Type 
     */ 
    function & factory ($className = false) {
        $res = & parent::factory($className);
        return $res;
    }
    
    /**
     * @return Ac_Test_Model_Relation_Type 
     */ 
    function & reference ($values = array()) {
        $res = & parent::reference($values);
        return $res;
    }
    
    /**
     * @return Ac_Test_Model_Relation_Type 
     */ 
    function & loadRecord ($id) {
        $res = & parent::loadRecord($id);
        return $res;
    }
    
    function Ac_Test_Model_Relation_Type_Base_Mapper () {
        parent::Ac_Model_Mapper('#__relation_types', 'Ac_Test_Model_Relation_Type', 'relationTypeId');
    }
        
    function getTitleFieldName() {
        return 'title';   
    }
                
    function _getRelationPrototypes() {
        return array (
              '_relations' => array (  
                  'srcMapperClass' => 'Ac_Test_Model_Relation_Type_Mapper',
                  'destMapperClass' => 'Ac_Test_Model_Relation_Mapper',
                  'srcVarName' => '_relations',
                  'srcCountVarName' => '_relationsCount',
                  'destVarName' => '_relationType',
                  'fieldLinks' => array (    
                      'relationTypeId' => 'relationTypeId',
                  ),
                  'srcIsUnique' => true,
                  'destIsUnique' => false,
              ),
        );
    }
        
    function _doGetInfoParams() {
        return array (
              'singleCaption' => 'Relation type',
              'pluralCaption' => 'Relation types',
              'hasUi' => false,
        );
    }
    
        
    function _doGetUniqueIndexData() {
        return array (
              'PRIMARY' => array (  
                  'relationTypeId',
              ),
        );
    }
            
    function getAutoincFieldName() {
        return 'relationTypeId';
    }
        
    /**
     * @return Ac_Test_Model_Relation_Type     
     */
    function & loadByRelationTypeId ($relationTypeId) {
        $recs = $this->loadRecordsByCriteria('`relationTypeId` = '.$this->database->Quote($relationTypeId).'');
        if (count($recs)) $res = & $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) one or more relationTypes of given one or more relations 
     * @param Ac_Test_Model_Relation_Type|array $relations     
     * @return array of Ac_Test_Model_Relation_Type objects  
     */
    function & getOfRelations($relations) {
        $rel = & $this->getRelation('_relations');
        $res = & $rel->getSrc($relations); 
        return $res;
    }
    
    /**
     * Loads one or more relationTypes of given one or more relations 
     * @param Ac_Test_Model_Relation|array $relations of Ac_Test_Model_Relation_Type objects
     
     */
    function loadForRelations($relations) {
        $rel = & $this->getRelation('_relations');
        return $rel->loadSrc($relations); 
    }

    /**
     * Loads one or more relations of given one or more relationTypes 
     * @param Ac_Test_Model_Relation_Type|array $relationTypes     
     */
    function loadRelationsFor($relationTypes) {
        $rel = & $this->getRelation('_relations');
        return $rel->loadDest($relationTypes); 
    }

    
}

?>