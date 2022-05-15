<?php
/**
 * @method Sample_Tag[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Tag_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'tagId';

    var $recordClass = 'Sample_Tag';

    var $tableName = '#__tags';

    var $id = 'Sample_Tag_Mapper';

    var $shortId = 'tags';

    var $storage = 'Sample_Tag_Storage';

    var $columnNames = [ 0 => 'tagId', 1 => 'title', 2 => 'titleM', 3 => 'titleF', ];

    var $nullableColumns = [ 0 => 'titleM', 1 => 'titleF', ];

    var $defaults = [
            'tagId' => NULL,
            'title' => NULL,
            'titleM' => NULL,
            'titleF' => NULL,
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
   
    protected $autoincFieldName = 'tagId';
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_people' => false,
            '_peopleCount' => false,
            '_peopleLoaded' => false,
            '_personIds' => false,
            '_perks' => false,
            '_perksCount' => false,
            '_perksLoaded' => false,
            '_perkIds' => false,
        ]);
    }
    
    /**
     * @return Sample_Tag 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = [], $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Tag_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Tag 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Tag 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Tag 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Tag 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Tag 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Tag[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Tag[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Tag     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Tag     */
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
     * @return Sample_Tag[]
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
     * @return Sample_Tag[]
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
            '_people' => [
                'srcMapperClass' => 'Sample_Tag_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_people',
                'srcNNIdsVarName' => '_personIds',
                'srcCountVarName' => '_peopleCount',
                'srcLoadedVarName' => '_peopleLoaded',
                'destVarName' => '_tags',
                'destCountVarName' => '_tagsCount',
                'destLoadedVarName' => '_tagsLoaded',
                'destNNIdsVarName' => '_tagIds',
                'fieldLinks' => [
                    'tagId' => 'idOfTag',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'srcLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadPersonIdsFor',
                ],
                'destLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadTagIdsFor',
                ],
                'midTableName' => '#__people_tags',
                'fieldLinks2' => [
                    'idOfPerson' => 'personId',
                ],
            ],
            '_perks' => [
                'srcMapperClass' => 'Sample_Tag_Mapper',
                'destMapperClass' => 'Sample_Perk_Mapper',
                'srcVarName' => '_perks',
                'srcNNIdsVarName' => '_perkIds',
                'srcCountVarName' => '_perksCount',
                'srcLoadedVarName' => '_perksLoaded',
                'destVarName' => '_tags',
                'destCountVarName' => '_tagsCount',
                'destLoadedVarName' => '_tagsLoaded',
                'destNNIdsVarName' => '_tagIds',
                'fieldLinks' => [
                    'tagId' => 'idOfTag',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'srcLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadPerkIdsFor',
                ],
                'destLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadTagIdsFor',
                ],
                'midTableName' => '#__tag_perks',
                'fieldLinks2' => [
                    'idOfPerk' => 'perkId',
                ],
            ],
        ]);
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), [
            'people' => [
                'relationId' => '_people',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'person',
                'plural' => 'people',
                'class' => 'Ac_Model_Association_ManyToMany',
                'loadDestObjectsMapperMethod' => 'loadPeopleFor',
                'loadSrcObjectsMapperMethod' => 'loadForPeople',
                'getSrcObjectsMapperMethod' => 'getOfPeople',
                'createDestObjectMethod' => 'createPerson',
                'getAllDestObjectsMethod' => 'getAllPeople',
                'listDestObjectsMethod' => 'listPeople',
                'countDestObjectsMethod' => 'countPeople',
                'getDestObjectMethod' => 'getPerson',
                'addDestObjectMethod' => 'addPerson',
                'isDestLoadedMethod' => 'isPeopleLoaded',
                'loadDestIdsMapperMethod' => 'loadPersonIdsFor',
                'getDestIdsMethod' => 'getPersonIds',
                'setDestIdsMethod' => 'setPersonIds',
                'clearDestObjectsMethod' => 'clearPeople',
            ],
            'perks' => [
                'relationId' => '_perks',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'perk',
                'plural' => 'perks',
                'class' => 'Ac_Model_Association_ManyToMany',
                'loadDestObjectsMapperMethod' => 'loadPerksFor',
                'loadSrcObjectsMapperMethod' => 'loadForPerks',
                'getSrcObjectsMapperMethod' => 'getOfPerks',
                'createDestObjectMethod' => 'createPerk',
                'getAllDestObjectsMethod' => 'getAllPerks',
                'listDestObjectsMethod' => 'listPerks',
                'countDestObjectsMethod' => 'countPerks',
                'getDestObjectMethod' => 'getPerk',
                'addDestObjectMethod' => 'addPerk',
                'isDestLoadedMethod' => 'isPerksLoaded',
                'loadDestIdsMapperMethod' => 'loadPerkIdsFor',
                'getDestIdsMethod' => 'getPerkIds',
                'setDestIdsMethod' => 'setPerkIds',
                'clearDestObjectsMethod' => 'clearPerks',
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_tags_single'),

                'pluralCaption' => new Ac_Lang_String('sample_tags_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'tagId',
            ],
            'Index_2' => [
                0 => 'title',
            ],
        ];
    }

    /**
     * @return Sample_Tag 
     */
    function loadByTagId ($tagId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('tagId').' = '.$this->getDb()->q($tagId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Tag 
     */
    function loadByTitle ($title) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('title').' = '.$this->getDb()->q($title).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) one or more tags of given one or more people 
     * @param Sample_Tag|array $people     
     * @return Sample_Tag|array of Sample_Tag objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_people');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads one or more tags of given one or more people 
     * @param Sample_Person|array $people of Sample_Tag objects      
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_people');
        return $rel->loadSrc($people); 
    }
    
    /**
     * Loads one or more people of given one or more tags 
     * @param Sample_Tag|array $tags     
     */
    function loadPeopleFor($tags) {
        $rel = $this->getRelation('_people');
        return $rel->loadDest($tags); 
    }


    /**
     * @param Sample_Tag|array $tags 
     */
     function loadPersonIdsFor($tags) {
        $rel = $this->getRelation('_people');
        return $rel->loadDestNNIds($tags); 
    }
    
    /**
     * Returns (but not loads!) one or more tags of given one or more perks 
     * @param Sample_Tag|array $perks     
     * @return Sample_Tag|array of Sample_Tag objects  
     */
    function getOfPerks($perks) {
        $rel = $this->getRelation('_perks');
        $res = $rel->getSrc($perks); 
        return $res;
    }
    
    /**
     * Loads one or more tags of given one or more perks 
     * @param Sample_Perk|array $perks of Sample_Tag objects      
     */
    function loadForPerks($perks) {
        $rel = $this->getRelation('_perks');
        return $rel->loadSrc($perks); 
    }
    
    /**
     * Loads one or more perks of given one or more tags 
     * @param Sample_Tag|array $tags     
     */
    function loadPerksFor($tags) {
        $rel = $this->getRelation('_perks');
        return $rel->loadDest($tags); 
    }


    /**
     * @param Sample_Tag|array $tags 
     */
     function loadPerkIdsFor($tags) {
        $rel = $this->getRelation('_perks');
        return $rel->loadDestNNIds($tags); 
    }
    
    
}

