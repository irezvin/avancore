<?php

class Sample_Relation_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'relationId'; 

    var $recordClass = 'Sample_Relation'; 

    var $tableName = '#__relations'; 

    var $id = 'Sample_Relation_Mapper'; 

    var $columnNames = array ( 0 => 'relationId', 1 => 'personId', 2 => 'otherPersonId', 3 => 'relationTypeId', 4 => 'relationBegin', 5 => 'relationEnd', 6 => 'notes', ); 

    var $nullableSqlColumns = array ( 0 => 'relationBegin', 1 => 'relationEnd', ); 

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
    
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    /**
     * @return Sample_Relation 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Relation_Mapper')->createRecord($className);
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
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Relation 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

                
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
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
            '_otherPerson' => array (
                'srcMapperClass' => 'Sample_Relation_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
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
            '_person' => array (
                'srcMapperClass' => 'Sample_Relation_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
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
        ));
        
    }
        
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Relation',
                'pluralCaption' => 'Relations',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'relationId',
            ),
        );
    }
        
    /**
     * @return Sample_Relation 
     */
    function loadByRelationId ($relationId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('relationId').' = '.$this->getDb()->q($relationId).'');
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
     * @param Sample_Relation|array $otherPeople     
     * @return Sample_Relation|array of Sample_Relation objects  
     */
    function getOfOtherPeople($otherPeople) {
        $rel = $this->getRelation('_otherPerson');
        $res = $rel->getSrc($otherPeople); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more people 
     * @param Sample_Person|array $otherPeople of Sample_Relation objects
     
     */
    function loadForOtherPeople($otherPeople) {
        $rel = $this->getRelation('_otherPerson');
        return $rel->loadSrc($otherPeople); 
    }

    /**
     * Loads several people of given one or more relations 
     * @param Sample_Relation|array $relations     
     */
    function loadOtherPeopleFor($relations) {
        $rel = $this->getRelation('_otherPerson');
        return $rel->loadDest($relations); 
    }


    /**
     * Returns (but not loads!) several relations of given one or more people 
     * @param Sample_Relation|array $people     
     * @return Sample_Relation|array of Sample_Relation objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_person');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads several relations of given one or more people 
     * @param Sample_Person|array $people of Sample_Relation objects
     
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_person');
        return $rel->loadSrc($people); 
    }

    /**
     * Loads several people of given one or more relations 
     * @param Sample_Relation|array $relations     
     */
    function loadPeopleFor($relations) {
        $rel = $this->getRelation('_person');
        return $rel->loadDest($relations); 
    }

    
}

