<?php

Ae_Dispatcher::loadClass('Ae_Model_Mapper');

class Ae_Test_Model_Relation_Base_Mapper extends Ae_Model_Mapper {
    
    /**
     * @return Ae_Test_Model_Relation 
     */ 
    function & factory ($className = false) {
        $res = & parent::factory($className);
        return $res;
    }
    
    /**
     * @return Ae_Test_Model_Relation 
     */ 
    function & reference ($values = array()) {
        $res = & parent::reference($values);
        return $res;
    }
    
    /**
     * @return Ae_Test_Model_Relation 
     */ 
    function & loadRecord ($id) {
        $res = & parent::loadRecord($id);
        return $res;
    }
    
    function Ae_Test_Model_Relation_Base_Mapper () {
        parent::Ae_Model_Mapper('#__relations', 'Ae_Test_Model_Relation', 'relationId');
    }
                
    function _getRelationPrototypes() {
        return array (
              '_person' => array (  
                  'srcMapperClass' => 'Ae_Test_Model_Relation_Mapper',
                  'destMapperClass' => 'Ae_Test_Model_People_Mapper',
                  'srcVarName' => '_person',
                  'destVarName' => '_outgoingRelations',
                  'destCountVarName' => '_outgoingRelationsCount',
                  'fieldLinks' => array (    
                      'personId' => 'personId',
                  ),
                  'srcIsUnique' => false,
                  'destIsUnique' => true,
                  'srcOutgoing' => true,
              ),
              '_otherPerson' => array (  
                  'srcMapperClass' => 'Ae_Test_Model_Relation_Mapper',
                  'destMapperClass' => 'Ae_Test_Model_People_Mapper',
                  'srcVarName' => '_otherPerson',
                  'destVarName' => '_incomingRelations',
                  'destCountVarName' => '_incomingRelationsCount',
                  'fieldLinks' => array (    
                      'otherPersonId' => 'personId',
                  ),
                  'srcIsUnique' => false,
                  'destIsUnique' => true,
                  'srcOutgoing' => true,
              ),
              '_relationType' => array (  
                  'srcMapperClass' => 'Ae_Test_Model_Relation_Mapper',
                  'destMapperClass' => 'Ae_Test_Model_Relation_Type_Mapper',
                  'srcVarName' => '_relationType',
                  'destVarName' => '_relations',
                  'destCountVarName' => '_relationsCount',
                  'fieldLinks' => array (    
                      'relationTypeId' => 'relationTypeId',
                  ),
                  'srcIsUnique' => false,
                  'destIsUnique' => true,
                  'srcOutgoing' => true,
              ),
        );
    }
        
    function _doGetInfoParams() {
        return array (
              'singleCaption' => 'Relation',
              'pluralCaption' => 'Relations',
              'hasUi' => false,
        );
    }
    
        
    function _doGetUniqueIndexData() {
        return array (
              'PRIMARY' => array (  
                  'relationId',
              ),
        );
    }
            
    function getAutoincFieldName() {
        return 'relationId';
    }
        
    /**
     * @return Ae_Test_Model_Relation     
     */
    function & loadByRelationId ($relationId) {
        $recs = $this->loadRecordsByCriteria('`relationId` = '.$this->database->Quote($relationId).'');
        if (count($recs)) $res = & $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) several relations of given one or more people 
     * @param Ae_Test_Model_Relation|array $people     
     * @return Ae_Test_Model_Relation|array of Ae_Test_Model_Relation objects  
     */
    function & getOfPeople($people) {
        $rel = & $this->getRelation('_person');
        $res = & $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more people 
     * @param Ae_Test_Model_People|array $people of Ae_Test_Model_Relation objects
     
     */
    function loadForPeople($people) {
        $rel = & $this->getRelation('_person');
        return $rel->loadSrc($people); 
    }

    /**
     * Loads several people of given one or more relations 
     * @param Ae_Test_Model_Relation|array $relations     
     */
    function loadPeopleFor($relations) {
        $rel = & $this->getRelation('_person');
        return $rel->loadDest($relations); 
    }


    /**
     * Returns (but not loads!) several relations of given one or more people 
     * @param Ae_Test_Model_Relation|array $otherPeople     
     * @return Ae_Test_Model_Relation|array of Ae_Test_Model_Relation objects  
     */
    function & getOfOtherPeople($otherPeople) {
        $rel = & $this->getRelation('_otherPerson');
        $res = & $rel->getSrc($otherPeople); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more people 
     * @param Ae_Test_Model_People|array $otherPeople of Ae_Test_Model_Relation objects
     
     */
    function loadForOtherPeople($otherPeople) {
        $rel = & $this->getRelation('_otherPerson');
        return $rel->loadSrc($otherPeople); 
    }

    /**
     * Loads several people of given one or more relations 
     * @param Ae_Test_Model_Relation|array $relations     
     */
    function loadOtherPeopleFor($relations) {
        $rel = & $this->getRelation('_otherPerson');
        return $rel->loadDest($relations); 
    }


    /**
     * Returns (but not loads!) several relations of given one or more relationTypes 
     * @param Ae_Test_Model_Relation|array $relationTypes     
     * @return Ae_Test_Model_Relation|array of Ae_Test_Model_Relation objects  
     */
    function & getOfRelationTypes($relationTypes) {
        $rel = & $this->getRelation('_relationType');
        $res = & $rel->getSrc($relationTypes); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more relationTypes 
     * @param Ae_Test_Model_Relation_Type|array $relationTypes of Ae_Test_Model_Relation objects
     
     */
    function loadForRelationTypes($relationTypes) {
        $rel = & $this->getRelation('_relationType');
        return $rel->loadSrc($relationTypes); 
    }

    /**
     * Loads several relationTypes of given one or more relations 
     * @param Ae_Test_Model_Relation|array $relations     
     */
    function loadRelationTypesFor($relations) {
        $rel = & $this->getRelation('_relationType');
        return $rel->loadDest($relations); 
    }

    
}

?>