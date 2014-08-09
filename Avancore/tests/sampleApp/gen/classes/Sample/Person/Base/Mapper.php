<?php

class Sample_Person_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'personId'; 

    var $recordClass = 'Sample_Person'; 

    var $tableName = '#__people'; 

    var $id = 'Sample_Person_Mapper'; 

    var $columnNames = array ( 0 => 'personId', 1 => 'name', 2 => 'gender', 3 => 'isSingle', 4 => 'birthDate', 5 => 'lastUpdatedDatetime', 6 => 'createdTs', 7 => 'sexualOrientationId', ); 

    var $nullableSqlColumns = array ( 0 => 'lastUpdatedDatetime', 1 => 'sexualOrientationId', ); 

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
    
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    /**
     * @return Sample_Person 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Person_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Person 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Person 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Person 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Person 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

                
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_orientation' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
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
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Tag_Mapper',
                'srcVarName' => '_tags',
                'srcNNIdsVarName' => '_tagIds',
                'srcCountVarName' => '_tagsCount',
                'destVarName' => '_people',
                'destCountVarName' => '_peopleCount',
                'destNNIdsVarName' => '_personIds',
                'fieldLinks' => array (
                    'personId' => 'idOfPerson',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'midTableName' => '#__people_tags',
                'fieldLinks2' => array (
                    'idOfTag' => 'tagId',
                ),
            ),
            '_incomingRelations' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Relation_Mapper',
                'srcVarName' => '_incomingRelations',
                'srcCountVarName' => '_incomingRelationsCount',
                'destVarName' => '_otherPerson',
                'fieldLinks' => array (
                    'personId' => 'otherPersonId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ),
            '_outgoingRelations' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Relation_Mapper',
                'srcVarName' => '_outgoingRelations',
                'srcCountVarName' => '_outgoingRelationsCount',
                'destVarName' => '_person',
                'fieldLinks' => array (
                    'personId' => 'personId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ),
        ));
        
    }
        
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'People',
                'pluralCaption' => 'People',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'personId',
            ),
        );
    }
        
    /**
     * @return Sample_Person 
     */
    function loadByPersonId ($personId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('personId').' = '.$this->getDb()->q($personId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) several people of given one or more orientation 
     * @param Sample_Person|array $orientation     
     * @return Sample_Person|array of Sample_Person objects  
     */
    function getOfOrientation($orientation) {
        $rel = $this->getRelation('_orientation');
        $res = $rel->getSrc($orientation); 
        return $res;
    }
    
    /**
     * Loads several people of given one or more orientation 
     * @param Sample_Orientation|array $orientation of Sample_Person objects
     
     */
    function loadForOrientation($orientation) {
        $rel = $this->getRelation('_orientation');
        return $rel->loadSrc($orientation); 
    }

    /**
     * Loads several orientation of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadOrientationFor($people) {
        $rel = $this->getRelation('_orientation');
        return $rel->loadDest($people); 
    }


    /**
     * Returns (but not loads!) one or more people of given one or more tags 
     * @param Sample_Person|array $tags     
     * @return Sample_Person|array of Sample_Person objects  
     */
    function getOfTags($tags) {
        $rel = $this->getRelation('_tags');
        $res = $rel->getSrc($tags); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more tags 
     * @param Sample_Tag|array $tags of Sample_Person objects
     
     */
    function loadForTags($tags) {
        $rel = $this->getRelation('_tags');
        return $rel->loadSrc($tags); 
    }

    /**
     * Loads one or more tags of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadTagsFor($people) {
        $rel = $this->getRelation('_tags');
        return $rel->loadDest($people); 
    }


    /**
     * @param Sample_Person|array $people 
     */
     function loadTagIdsFor($people) {
        $rel = $this->getRelation('_tags');
        return $rel->loadDestNNIds($people); 
    }


    /**
     * Returns (but not loads!) one or more people of given one or more relations 
     * @param Sample_Person|array $incomingRelations     
     * @return array of Sample_Person objects  
     */
    function getOfIncomingRelations($incomingRelations) {
        $rel = $this->getRelation('_incomingRelations');
        $res = $rel->getSrc($incomingRelations); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more relations 
     * @param Sample_Relation|array $incomingRelations of Sample_Person objects
     
     */
    function loadForIncomingRelations($incomingRelations) {
        $rel = $this->getRelation('_incomingRelations');
        return $rel->loadSrc($incomingRelations); 
    }

    /**
     * Loads one or more relations of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadIncomingRelationsFor($people) {
        $rel = $this->getRelation('_incomingRelations');
        return $rel->loadDest($people); 
    }


    /**
     * Returns (but not loads!) one or more people of given one or more relations 
     * @param Sample_Person|array $outgoingRelations     
     * @return array of Sample_Person objects  
     */
    function getOfOutgoingRelations($outgoingRelations) {
        $rel = $this->getRelation('_outgoingRelations');
        $res = $rel->getSrc($outgoingRelations); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more relations 
     * @param Sample_Relation|array $outgoingRelations of Sample_Person objects
     
     */
    function loadForOutgoingRelations($outgoingRelations) {
        $rel = $this->getRelation('_outgoingRelations');
        return $rel->loadSrc($outgoingRelations); 
    }

    /**
     * Loads one or more relations of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadOutgoingRelationsFor($people) {
        $rel = $this->getRelation('_outgoingRelations');
        return $rel->loadDest($people); 
    }

    
}

