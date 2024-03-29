<?php
/**
 * @method Sample_Relation_Type[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Relation_Type_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'relationTypeId';

    var $recordClass = 'Sample_Relation_Type';

    var $tableName = '#__relation_types';

    var $id = 'Sample_Relation_Type_Mapper';

    var $shortId = 'relationTypes';

    var $storage = 'Sample_Relation_Type_Storage';

    var $columnNames = [ 0 => 'relationTypeId', 1 => 'title', 2 => 'isSymmetrical', ];

    var $defaults = [
            'relationTypeId' => NULL,
            'title' => NULL,
            'isSymmetrical' => 0,
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
   
    protected $autoincFieldName = 'relationTypeId';
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_relations' => false,
            '_relationsCount' => false,
            '_relationsLoaded' => false,
        ]);
    }
    
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * Creates new RelationTypes instance that is bound to this mapper.
     * 
     * @param string|array $typeIdOrDefaults Id of child mapper (FALSE = create default if possible)
     * @param array $defaults Values of object properties' to assign
     * @return Sample_Relation_Type 
     */ 
    function createRecord($typeIdOrDefaults = false, array $defaults = []) {
        $res = parent::createRecord($typeIdOrDefaults, $defaults);
        return $res;
    }
    
    /**
     * @return Sample_Relation_Type 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Relation_Type 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Relation_Type 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Relation_Type 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Relation_Type[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Relation_Type[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Relation_Type     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Relation_Type     */
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
     * @return Sample_Relation_Type[]
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
     * @return Sample_Relation_Type[]
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
    

    
    function getTitleFieldName() {
        return 'title';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), [
            '_relations' => [
                'srcMapperClass' => 'Sample_Relation_Type_Mapper',
                'destMapperClass' => 'Sample_Relation_Mapper',
                'srcVarName' => '_relations',
                'srcCountVarName' => '_relationsCount',
                'srcLoadedVarName' => '_relationsLoaded',
                'destVarName' => '_relationType',
                'fieldLinks' => [
                    'relationTypeId' => 'relationTypeId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
        ]);
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), [
            'relations' => [
                'relationId' => '_relations',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'relation',
                'plural' => 'relations',
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => 'loadRelationsFor',
                'loadSrcObjectsMapperMethod' => 'loadForRelations',
                'getSrcObjectsMapperMethod' => 'getOfRelations',
                'createDestObjectMethod' => 'createRelation',
                'getAllDestObjectsMethod' => 'getAllRelations',
                'listDestObjectsMethod' => 'listRelations',
                'countDestObjectsMethod' => 'countRelations',
                'getDestObjectMethod' => 'getRelation',
                'addDestObjectMethod' => 'addRelation',
                'isDestLoadedMethod' => 'isRelationsLoaded',
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_relation_types_single'),

                'pluralCaption' => new Ac_Lang_String('sample_relation_types_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'relationTypeId',
            ],
        ];
    }

    /**
     * @return Sample_Relation_Type 
     */
    function loadByRelationTypeId ($relationTypeId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('relationTypeId').' = '.$this->getDb()->q($relationTypeId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) one or more relationTypes of given one or more relations 
     * @param Sample_Relation_Type|array $relations     
     * @return array of Sample_Relation_Type objects  
     */
    function getOfRelations($relations) {
        $rel = $this->getRelation('_relations');
        $res = $rel->getSrc($relations); 
        return $res;
    }
    
    /**
     * Loads one or more relationTypes of given one or more relations 
     * @param Sample_Relation|array $relations of Sample_Relation_Type objects      
     */
    function loadForRelations($relations) {
        $rel = $this->getRelation('_relations');
        return $rel->loadSrc($relations); 
    }
    
    /**
     * Loads one or more relations of given one or more relationTypes 
     * @param Sample_Relation_Type|array $relationTypes     
     */
    function loadRelationsFor($relationTypes) {
        $rel = $this->getRelation('_relations');
        return $rel->loadDest($relationTypes); 
    }

    
}

