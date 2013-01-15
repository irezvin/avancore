<?php

Ac_Dispatcher::loadClass('Ac_Model_Mapper');

class Ac_Test_Model_Relation_Base_Mapper extends Ac_Model_Mapper {
    
    /**
     * @return Ac_Test_Model_Relation 
     */ 
    function & factory ($className = false) {
        $res = & parent::factory($className);
        return $res;
    }
    
    /**
     * @return Ac_Test_Model_Relation 
     */ 
    function & reference ($values = array()) {
        $res = & parent::reference($values);
        return $res;
    }
    
    /**
     * @return Ac_Test_Model_Relation 
     */ 
    function & loadRecord ($id) {
        $res = & parent::loadRecord($id);
        return $res;
    }
    
    function Ac_Test_Model_Relation_Base_Mapper () {
        parent::Ac_Model_Mapper('#__relations', 'Ac_Test_Model_Relation', 'relationId');
    }
                
    function _getRelationPrototypes() {
        return array (
              '_person' => array (  
                  'srcMapperClass' => 'Ac_Test_Model_Relation_Mapper',
                  'destMapperClass' => 'Ac_Test_Model_People_Mapper',
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
                  'srcMapperClass' => 'Ac_Test_Model_Relation_Mapper',
                  'destMapperClass' => 'Ac_Test_Model_People_Mapper',
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
                  'srcMapperClass' => 'Ac_Test_Model_Relation_Mapper',
                  'destMapperClass' => 'Ac_Test_Model_Relation_Type_Mapper',
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
     * @return Ac_Test_Model_Relation     
     */
    function & loadByRelationId ($relationId) {
        $recs = $this->loadRecordsByCriteria('`relationId` = '.$this->database->Quote($relationId).'');
        if (count($recs)) $res = & $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) several relations of given one or more people 
     * @param Ac_Test_Model_Relation|array $people     
     * @return Ac_Test_Model_Relation|array of Ac_Test_Model_Relation objects  
     */
    function & getOfPeople($people) {
        $rel = & $this->getRelation('_person');
        $res = & $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more people 
     * @param Ac_Test_Model_People|array $people of Ac_Test_Model_Relation objects
     
     */
    function loadForPeople($people) {
        $rel = & $this->getRelation('_person');
        return $rel->loadSrc($people); 
    }

    /**
     * Loads several people of given one or more relations 
     * @param Ac_Test_Model_Relation|array $relations     
     */
    function loadPeopleFor($relations) {
        $rel = & $this->getRelation('_person');
        return $rel->loadDest($relations); 
    }


    /**
     * Returns (but not loads!) several relations of given one or more people 
     * @param Ac_Test_Model_Relation|array $otherPeople     
     * @return Ac_Test_Model_Relation|array of Ac_Test_Model_Relation objects  
     */
    function & getOfOtherPeople($otherPeople) {
        $rel = & $this->getRelation('_otherPerson');
        $res = & $rel->getSrc($otherPeople); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more people 
     * @param Ac_Test_Model_People|array $otherPeople of Ac_Test_Model_Relation objects
     
     */
    function loadForOtherPeople($otherPeople) {
        $rel = & $this->getRelation('_otherPerson');
        return $rel->loadSrc($otherPeople); 
    }

    /**
     * Loads several people of given one or more relations 
     * @param Ac_Test_Model_Relation|array $relations     
     */
    function loadOtherPeopleFor($relations) {
        $rel = & $this->getRelation('_otherPerson');
        return $rel->loadDest($relations); 
    }


    /**
     * Returns (but not loads!) several relations of given one or more relationTypes 
     * @param Ac_Test_Model_Relation|array $relationTypes     
     * @return Ac_Test_Model_Relation|array of Ac_Test_Model_Relation objects  
     */
    function & getOfRelationTypes($relationTypes) {
        $rel = & $this->getRelation('_relationType');
        $res = & $rel->getSrc($relationTypes); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more relationTypes 
     * @param Ac_Test_Model_Relation_Type|array $relationTypes of Ac_Test_Model_Relation objects
     
     */
    function loadForRelationTypes($relationTypes) {
        $rel = & $this->getRelation('_relationType');
        return $rel->loadSrc($relationTypes); 
    }

    /**
     * Loads several relationTypes of given one or more relations 
     * @param Ac_Test_Model_Relation|array $relations     
     */
    function loadRelationTypesFor($relations) {
        $rel = & $this->getRelation('_relationType');
        return $rel->loadDest($relations); 
    }

    
}

?>