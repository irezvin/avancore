<?php

class Sample_People_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'personId'; 

    var $recordClass = 'Sample_People'; 

    var $tableName = '#__people'; 

    var $id = 'Sample_People_Mapper'; 

    var $columnNames = array ( 'personId', 'name', 'gender', 'isSingle', 'birthDate', 'lastUpdatedDatetime', 'createdTs', 'sexualOrientationId', ); 

    var $defaults = array (
              'personId' => NULL,
              'name' => NULL,
              'gender' => 'F',
              'isSingle' => '1',
              'birthDate' => NULL,
              'lastUpdatedDatetime' => NULL,
              'createdTs' => 'CURRENT_TIMESTAMP',
              'sexualOrientationId' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'personId';
    
    /**
     * @return Sample_People 
     */ 
    function factory ($className = false) {
        $res = parent::factory($className);
        return $res;
    }
    
    /**
     * @return Sample_People 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_People 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_People 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_People 
     */ 
    function loadSingleRecord($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount);
    }

                
    function _getRelationPrototypes() {
        return Ac_Util::m(parent::_getRelationPrototypes(), array (
              '_orientation' => array (
                  'srcMapperClass' => 'Sample_People_Mapper',
                  'destMapperClass' => 'Sample_Orientation_Mapper',
                  'srcVarName' => '_orientation',
                  'destVarName' => '_people',
                  'destCountVarName' => '_peopleCount',
                  'fieldLinks' => array (
                      'sexualOrientationId' => 'sexualOrientationId',
                  ),
                  'srcIsUnique' => false,
                  'destIsUnique' => true,
                  'srcOutgoing' => true,
              ),
              '_tags' => array (
                  'srcMapperClass' => 'Sample_People_Mapper',
                  'destMapperClass' => 'Sample_Tag_Mapper',
                  'srcVarName' => '_tags',
                  'srcNNIdsVarName' => '_tagIds',
                  'srcCountVarName' => '_tagsCount',
                  'destVarName' => '_people',
                  'destCountVarName' => '_peopleCount',
                  'destNNIdsVarName' => '_peopleIds',
                  'fieldLinks' => array (
                      'personId' => 'personId',
                  ),
                  'srcIsUnique' => false,
                  'destIsUnique' => false,
                  'midTableName' => '#__people_tags',
                  'fieldLinks2' => array (
                      'tagId' => 'tagId',
                  ),
              ),
              '_incomingRelations' => array (
                  'srcMapperClass' => 'Sample_People_Mapper',
                  'destMapperClass' => 'Sample_Relation_Mapper',
                  'srcVarName' => '_incomingRelations',
                  'srcCountVarName' => '_incomingRelationsCount',
                  'destVarName' => '_incomingPeople',
                  'destCountVarName' => '_incomingCount',
                  'fieldLinks' => array (
                      'personId' => 'otherPersonId',
                  ),
                  'srcIsUnique' => true,
                  'destIsUnique' => false,
              ),
              '_outgoingRelations' => array (
                  'srcMapperClass' => 'Sample_People_Mapper',
                  'destMapperClass' => 'Sample_Relation_Mapper',
                  'srcVarName' => '_outgoingRelations',
                  'srcCountVarName' => '_outgoingRelationsCount',
                  'destVarName' => '_outgoingPeople',
                  'destCountVarName' => '_outgoingCount',
                  'fieldLinks' => array (
                      'personId' => 'personId',
                  ),
                  'srcIsUnique' => true,
                  'destIsUnique' => false,
              ),
        ));
        
    }
        
    function _doGetInfoParams() {
        return array (
              'singleCaption' => 'People',
              'pluralCaption' => 'People',
              'hasUi' => false,
        );
    }
    
        
    function _doGetUniqueIndexData() {
        return array (
              'PRIMARY' => array (
                  'personId',
              ),
        );
    }
        
    /**
     * @return Sample_People 
     */
    function loadByPersonId ($personId) {
        $recs = $this->loadRecordsByCriteria(''.$this->database->NameQuote('personId').' = '.$this->database->Quote($personId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) several people of given one or more orientation 
     * @param Sample_People|array $orientation     
     * @return Sample_People|array of Sample_People objects  
     */
    function getOfOrientation($orientation) {
        $rel = $this->getRelation('_orientation');
        $res = $rel->getSrc($orientation); 
        return $res;
    }
    
    /**
     * Loads several people of given one or more orientation 
     * @param Sample_Orientation|array $orientation of Sample_People objects
     
     */
    function loadForOrientation($orientation) {
        $rel = $this->getRelation('_orientation');
        return $rel->loadSrc($orientation); 
    }

    /**
     * Loads several orientation of given one or more people 
     * @param Sample_People|array $people     
     */
    function loadOrientationFor($people) {
        $rel = $this->getRelation('_orientation');
        return $rel->loadDest($people); 
    }


    /**
     * Returns (but not loads!) one or more people of given one or more tags 
     * @param Sample_People|array $tags     
     * @return Sample_People|array of Sample_People objects  
     */
    function getOfTags($tags) {
        $rel = $this->getRelation('_tags');
        $res = $rel->getSrc($tags); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more tags 
     * @param Sample_Tag|array $tags of Sample_People objects
     
     */
    function loadForTags($tags) {
        $rel = $this->getRelation('_tags');
        return $rel->loadSrc($tags); 
    }

    /**
     * Loads one or more tags of given one or more people 
     * @param Sample_People|array $people     
     */
    function loadTagsFor($people) {
        $rel = $this->getRelation('_tags');
        return $rel->loadDest($people); 
    }


    /**
     * Returns (but not loads!) one or more people of given one or more relations 
     * @param Sample_People|array $incomingRelations     
     * @return array of Sample_People objects  
     */
    function getOfIncomingRelations($incomingRelations) {
        $rel = $this->getRelation('_incomingRelations');
        $res = $rel->getSrc($incomingRelations); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more relations 
     * @param Sample_Relation|array $incomingRelations of Sample_People objects
     
     */
    function loadForIncomingRelations($incomingRelations) {
        $rel = $this->getRelation('_incomingRelations');
        return $rel->loadSrc($incomingRelations); 
    }

    /**
     * Loads one or more relations of given one or more people 
     * @param Sample_People|array $people     
     */
    function loadIncomingRelationsFor($people) {
        $rel = $this->getRelation('_incomingRelations');
        return $rel->loadDest($people); 
    }


    /**
     * Returns (but not loads!) one or more people of given one or more relations 
     * @param Sample_People|array $outgoingRelations     
     * @return array of Sample_People objects  
     */
    function getOfOutgoingRelations($outgoingRelations) {
        $rel = $this->getRelation('_outgoingRelations');
        $res = $rel->getSrc($outgoingRelations); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more relations 
     * @param Sample_Relation|array $outgoingRelations of Sample_People objects
     
     */
    function loadForOutgoingRelations($outgoingRelations) {
        $rel = $this->getRelation('_outgoingRelations');
        return $rel->loadSrc($outgoingRelations); 
    }

    /**
     * Loads one or more relations of given one or more people 
     * @param Sample_People|array $people     
     */
    function loadOutgoingRelationsFor($people) {
        $rel = $this->getRelation('_outgoingRelations');
        return $rel->loadDest($people); 
    }

    
}
