<?php
/**
 * @method Sample_Perk[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Perk_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'perkId';

    var $recordClass = 'Sample_Perk';

    var $tableName = '#__perks';

    var $id = 'Sample_Perk_Mapper';

    var $shortId = 'perks';

    var $storage = 'Sample_Perk_Storage';

    var $columnNames = [ 0 => 'perkId', 1 => 'name', ];

    var $nullableColumns = [ 0 => 'name', ];

    var $defaults = [
            'perkId' => NULL,
            'name' => '',
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
   
    protected $autoincFieldName = 'perkId';
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_tags' => false,
            '_tagsCount' => false,
            '_tagsLoaded' => false,
            '_tagIds' => false,
        ]);
    }
    
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * Creates new Perks instance that is bound to this mapper.
     * 
     * @param string|array $typeIdOrDefaults Id of child mapper (FALSE = create default if possible)
     * @param array $defaults Values of object properties' to assign
     * @return Sample_Perk 
     */ 
    function createRecord($typeIdOrDefaults = false, array $defaults = []) {
        $res = parent::createRecord($typeIdOrDefaults, $defaults);
        return $res;
    }
    
    /**
     * @return Sample_Perk 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Perk 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Perk 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Perk 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Perk[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Perk[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Perk     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Perk     */
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
     * @return Sample_Perk[]
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
     * @return Sample_Perk[]
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
        return 'name';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), [
            '_tags' => [
                'srcMapperClass' => 'Sample_Perk_Mapper',
                'destMapperClass' => 'Sample_Tag_Mapper',
                'srcVarName' => '_tags',
                'srcNNIdsVarName' => '_tagIds',
                'srcCountVarName' => '_tagsCount',
                'srcLoadedVarName' => '_tagsLoaded',
                'destVarName' => '_perks',
                'destCountVarName' => '_perksCount',
                'destLoadedVarName' => '_perksLoaded',
                'destNNIdsVarName' => '_perkIds',
                'fieldLinks' => [
                    'perkId' => 'idOfPerk',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'srcLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadTagIdsFor',
                ],
                'destLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadPerkIdsFor',
                ],
                'midTableName' => '#__tag_perks',
                'fieldLinks2' => [
                    'idOfTag' => 'tagId',
                ],
            ],
        ]);
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), [
            'tags' => [
                'relationId' => '_tags',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'tag',
                'plural' => 'tags',
                'class' => 'Ac_Model_Association_ManyToMany',
                'loadDestObjectsMapperMethod' => 'loadTagsFor',
                'loadSrcObjectsMapperMethod' => 'loadForTags',
                'getSrcObjectsMapperMethod' => 'getOfTags',
                'createDestObjectMethod' => 'createTag',
                'getAllDestObjectsMethod' => 'getAllTags',
                'listDestObjectsMethod' => 'listTags',
                'countDestObjectsMethod' => 'countTags',
                'getDestObjectMethod' => 'getTag',
                'addDestObjectMethod' => 'addTag',
                'isDestLoadedMethod' => 'isTagsLoaded',
                'loadDestIdsMapperMethod' => 'loadTagIdsFor',
                'getDestIdsMethod' => 'getTagIds',
                'setDestIdsMethod' => 'setTagIds',
                'clearDestObjectsMethod' => 'clearTags',
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_perks_single'),

                'pluralCaption' => new Ac_Lang_String('sample_perks_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'perkId',
            ],
        ];
    }

    /**
     * @return Sample_Perk 
     */
    function loadByPerkId ($perkId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('perkId').' = '.$this->getDb()->q($perkId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) one or more perks of given one or more tags 
     * @param Sample_Perk|array $tags     
     * @return Sample_Perk|array of Sample_Perk objects  
     */
    function getOfTags($tags) {
        $rel = $this->getRelation('_tags');
        $res = $rel->getSrc($tags); 
        return $res;
    }
    
    /**
     * Loads one or more perks of given one or more tags 
     * @param Sample_Tag|array $tags of Sample_Perk objects      
     */
    function loadForTags($tags) {
        $rel = $this->getRelation('_tags');
        return $rel->loadSrc($tags); 
    }
    
    /**
     * Loads one or more tags of given one or more perks 
     * @param Sample_Perk|array $perks     
     */
    function loadTagsFor($perks) {
        $rel = $this->getRelation('_tags');
        return $rel->loadDest($perks); 
    }


    /**
     * @param Sample_Perk|array $perks 
     */
     function loadTagIdsFor($perks) {
        $rel = $this->getRelation('_tags');
        return $rel->loadDestNNIds($perks); 
    }
    
    
}

