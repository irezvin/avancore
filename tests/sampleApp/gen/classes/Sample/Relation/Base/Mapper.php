<?php
/**
 * @method Sample_Relation[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Relation_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'relationId';

    var $recordClass = 'Sample_Relation';

    var $tableName = '#__relations';

    var $id = 'Sample_Relation_Mapper';

    var $shortId = 'relations';

    var $storage = 'Sample_Relation_Storage';

    var $columnNames = [ 0 => 'relationId', 1 => 'personId', 2 => 'otherPersonId', 3 => 'relationTypeId', 4 => 'relationBegin', 5 => 'relationEnd', 6 => 'notes', ];

    var $nullableColumns = [ 0 => 'relationBegin', 1 => 'relationEnd', ];

    var $defaults = [
            'relationId' => NULL,
            'personId' => NULL,
            'otherPersonId' => NULL,
            'relationTypeId' => NULL,
            'relationBegin' => NULL,
            'relationEnd' => NULL,
            'notes' => '',
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
   
    protected $autoincFieldName = 'relationId';
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_relationType' => false,
            '_otherPerson' => false,
            '_person' => false,
        ]);
    }
    
    /**
     * @return Sample_Relation 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = [], $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Relation_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Relation 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Relation 
     */ 
    function reference ($values = []) {
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
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Relation[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Relation[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Relation     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Relation     */
    function findOne (array $query = []) {
        return parent::findOne($query);
    }
    
    /**
     * @param array $query
     * @param mixed $keysToList
     * @param mixed $sort
     * @param int $limit
     * @param int $offset
     * @param bool $forceStorage
     * @return Sample_Relation[]
     */
    function find (array $query = [], $keysToList = true, $sort = false, $limit = false, $offset = false, & $remainingQuery = [], & $sorted = false) {
        if (func_num_args() > 5) $remainingQuery = true;
        return parent::find($query, $keysToList, $sort, $limit, $offset, $remainingQuery, $sorted);
    }
    
    /**
     * Does partial search.
     * 
     * Objects are always returned by-identifiers.
     * 
     * @return Sample_Relation[]
     *
     * @param array $inMemoryRecords - set of in-memory records to search in
     * @param type $areByIdentifiers - whether $inMemoryRecords are already indexed by identifiers
     * @param array $query - the query (set of criteria)
     * @param mixed $sort - how to sort
     * @param int $limit
     * @param int $offset
     * @param bool $canUseStorage - whether to ask storage to find missing items or apply storage-specific criteria first
     * @param array $remainingQuery - return value - critria that Mapper wasn't able to understand (thus they weren't applied)
     * @param bool $sorted - return value - whether the result was sorted according to $sort paramter
     */
    function filter (array $records, array $query = [], $sort = false, $limit = false, $offset = false, & $remainingQuery = true, & $sorted = false, $areByIds = false) {
        if (func_num_args() > 5) $remainingQuery = true;
        return parent::filter($records, $query, $sort, $limit, $offset, $remainingQuery, $sorted, $areByIds);
    }
    

    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), [
            '_relationType' => [
                'srcMapperClass' => 'Sample_Relation_Mapper',
                'destMapperClass' => 'Sample_Relation_Type_Mapper',
                'srcVarName' => '_relationType',
                'destVarName' => '_relations',
                'destCountVarName' => '_relationsCount',
                'destLoadedVarName' => '_relationsLoaded',
                'fieldLinks' => [
                    'relationTypeId' => 'relationTypeId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
            '_otherPerson' => [
                'srcMapperClass' => 'Sample_Relation_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_otherPerson',
                'destVarName' => '_incomingRelations',
                'destCountVarName' => '_incomingRelationsCount',
                'destLoadedVarName' => '_incomingRelationsLoaded',
                'fieldLinks' => [
                    'otherPersonId' => 'personId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
            '_person' => [
                'srcMapperClass' => 'Sample_Relation_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_person',
                'destVarName' => '_outgoingRelations',
                'destCountVarName' => '_outgoingRelationsCount',
                'destLoadedVarName' => '_outgoingRelationsLoaded',
                'fieldLinks' => [
                    'personId' => 'personId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
        ]);
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), [
            'relationType' => [
                'relationId' => '_relationType',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'relationType',
                'plural' => 'relationTypes',
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadRelationTypesFor',
                'loadSrcObjectsMapperMethod' => 'loadForRelationTypes',
                'getSrcObjectsMapperMethod' => 'getOfRelationTypes',
                'createDestObjectMethod' => 'createRelationType',
                'getDestObjectMethod' => 'getRelationType',
                'setDestObjectMethod' => 'setRelationType',
                'clearDestObjectMethod' => 'clearRelationType',
            ],
            'otherPerson' => [
                'relationId' => '_otherPerson',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'otherPerson',
                'plural' => 'otherPeople',
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadOtherPeopleFor',
                'loadSrcObjectsMapperMethod' => 'loadForOtherPeople',
                'getSrcObjectsMapperMethod' => 'getOfOtherPeople',
                'createDestObjectMethod' => 'createOtherPerson',
                'getDestObjectMethod' => 'getOtherPerson',
                'setDestObjectMethod' => 'setOtherPerson',
                'clearDestObjectMethod' => 'clearOtherPerson',
            ],
            'person' => [
                'relationId' => '_person',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'person',
                'plural' => 'people',
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadPeopleFor',
                'loadSrcObjectsMapperMethod' => 'loadForPeople',
                'getSrcObjectsMapperMethod' => 'getOfPeople',
                'createDestObjectMethod' => 'createPerson',
                'getDestObjectMethod' => 'getPerson',
                'setDestObjectMethod' => 'setPerson',
                'clearDestObjectMethod' => 'clearPerson',
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_relations_single'),

                'pluralCaption' => new Ac_Lang_String('sample_relations_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'relationId',
            ],
        ];
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

