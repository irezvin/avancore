<?php

class Sample_Relation_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'relationId'; 

    var $recordClass = 'Sample_Relation'; 

    var $tableName = '#__relations'; 

    var $id = 'Sample_Relation_Mapper'; 

    var $columnNames = array ( 'relationId', 'personId', 'otherPersonId', 'relationTypeId', 'relationBegin', 'relationEnd', 'notes', ); 

    var $defaults = array (
              'relationId' => NULL,
              'personId' => NULL,
              'otherPersonId' => NULL,
              'relationTypeId' => NULL,
              'relationBegin' => NULL,
              'relationEnd' => NULL,
              'notes' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'relationId';
    
    /**
     * @return Sample_Relation 
     */ 
    function factory ($className = false) {
        $res = parent::factory($className);
        return $res;
    }
    
    /**
     * @return Sample_Relation 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Relation 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Relation 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Relation 
     */ 
    function loadSingleRecord($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount);
    }

                
    function _getRelationPrototypes() {
        return Ac_Util::m(parent::_getRelationPrototypes(), array (
              '_relationType' => array (
                  'srcMapperClass' => 'Sample_Relation_Mapper',
                  'destMapperClass' => 'Sample_Relation_Type_Mapper',
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
              '_incomingPeople' => array (
                  'srcMapperClass' => 'Sample_Relation_Mapper',
                  'destMapperClass' => 'Sample_People_Mapper',
                  'srcVarName' => '_incomingPeople',
                  'srcCountVarName' => '_incomingCount',
                  'destVarName' => '_incomingRelations',
                  'destCountVarName' => '_incomingRelationsCount',
                  'fieldLinks' => array (
                      'otherPersonId' => 'personId',
                  ),
                  'srcIsUnique' => false,
                  'destIsUnique' => true,
                  'srcOutgoing' => true,
              ),
              '_outgoingPeople' => array (
                  'srcMapperClass' => 'Sample_Relation_Mapper',
                  'destMapperClass' => 'Sample_People_Mapper',
                  'srcVarName' => '_outgoingPeople',
                  'srcCountVarName' => '_outgoingCount',
                  'destVarName' => '_outgoingRelations',
                  'destCountVarName' => '_outgoingRelationsCount',
                  'fieldLinks' => array (
                      'personId' => 'personId',
                  ),
                  'srcIsUnique' => false,
                  'destIsUnique' => true,
                  'srcOutgoing' => true,
              ),
        ));
        
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
        
    /**
     * @return Sample_Relation 
     */
    function loadByRelationId ($relationId) {
        $recs = $this->loadRecordsByCriteria(''.$this->database->NameQuote('relationId').' = '.$this->database->Quote($relationId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) several relations of given one or more relationTypes 
     * @param Sample_Relation|array $relationTypes     
     * @return Sample_Relation|array of Sample_Relation objects  
     */
    function getOfRelationTypes($relationTypes) {
        $rel = $this->getRelation('_relationType');
        $res = $rel->getSrc($relationTypes); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more relationTypes 
     * @param Sample_Relation_Type|array $relationTypes of Sample_Relation objects
     
     */
    function loadForRelationTypes($relationTypes) {
        $rel = $this->getRelation('_relationType');
        return $rel->loadSrc($relationTypes); 
    }

    /**
     * Loads several relationTypes of given one or more relations 
     * @param Sample_Relation|array $relations     
     */
    function loadRelationTypesFor($relations) {
        $rel = $this->getRelation('_relationType');
        return $rel->loadDest($relations); 
    }


    /**
     * Returns (but not loads!) several relations of given one or more people 
     * @param Sample_Relation|array $incomingPeople     
     * @return Sample_Relation|array of Sample_Relation objects  
     */
    function getOfIncomingPeople($incomingPeople) {
        $rel = $this->getRelation('_incomingPeople');
        $res = $rel->getSrc($incomingPeople); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more people 
     * @param Sample_People|array $incomingPeople of Sample_Relation objects
     
     */
    function loadForIncomingPeople($incomingPeople) {
        $rel = $this->getRelation('_incomingPeople');
        return $rel->loadSrc($incomingPeople); 
    }

    /**
     * Loads several people of given one or more relations 
     * @param Sample_Relation|array $relations     
     */
    function loadIncomingPeopleFor($relations) {
        $rel = $this->getRelation('_incomingPeople');
        return $rel->loadDest($relations); 
    }


    /**
     * Returns (but not loads!) several relations of given one or more people 
     * @param Sample_Relation|array $outgoingPeople     
     * @return Sample_Relation|array of Sample_Relation objects  
     */
    function getOfOutgoingPeople($outgoingPeople) {
        $rel = $this->getRelation('_outgoingPeople');
        $res = $rel->getSrc($outgoingPeople); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more people 
     * @param Sample_People|array $outgoingPeople of Sample_Relation objects
     
     */
    function loadForOutgoingPeople($outgoingPeople) {
        $rel = $this->getRelation('_outgoingPeople');
        return $rel->loadSrc($outgoingPeople); 
    }

    /**
     * Loads several people of given one or more relations 
     * @param Sample_Relation|array $relations     
     */
    function loadOutgoingPeopleFor($relations) {
        $rel = $this->getRelation('_outgoingPeople');
        return $rel->loadDest($relations); 
    }

    
}

