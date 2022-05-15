<?php
/**
 * @method Sample_Person_Post[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Person_Post_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'id';

    var $recordClass = 'Sample_Person_Post';

    var $tableName = '#__person_posts';

    var $id = 'Sample_Person_Post_Mapper';

    var $shortId = 'personPosts';

    var $storage = 'Sample_Person_Post_Storage';

    var $columnNames = [ 0 => 'id', 1 => 'personId', 2 => 'photoId', 3 => 'title', 4 => 'content', 5 => 'pubId', ];

    var $nullableColumns = [ 0 => 'personId', 1 => 'photoId', 2 => 'title', 3 => 'content', 4 => 'pubId', ];

    var $defaults = [
            'id' => NULL,
            'personId' => NULL,
            'photoId' => NULL,
            'title' => '',
            'content' => '',
            'pubId' => NULL,
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
   
    protected $autoincFieldName = 'id';
    protected $askRelationsForDefaults = false;
 
    protected function doGetCoreMixables() { 
        return Ac_Util::m(parent::doGetCoreMixables(), [
            'publish' => [
                'class' => 'Sample_Publish_MapperMixable',
                'colMap' => [
                    'id' => 'pubId',
                ],
            ],
        ]);
    }
    
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_publish' => false,
            '_person' => false,
            '_personPhoto' => false,
        ]);
    }
    
    /**
     * @return Sample_Person_Post 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = [], $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Person_Post_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Person_Post 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Person_Post 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Person_Post 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Person_Post 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Person_Post 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Person_Post[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Person_Post[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Person_Post     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Person_Post     */
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
     * @return Sample_Person_Post[]
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
     * @return Sample_Person_Post[]
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
            '_publish' => [
                'srcMapperClass' => 'Sample_Person_Post_Mapper',
                'destMapperClass' => 'Sample_Publish_ImplMapper',
                'srcVarName' => '_publish',
                'fieldLinks' => [
                    'pubId' => 'id',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
            '_person' => [
                'srcMapperClass' => 'Sample_Person_Post_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_person',
                'destVarName' => '_personPosts',
                'destCountVarName' => '_personPostsCount',
                'destLoadedVarName' => '_personPostsLoaded',
                'fieldLinks' => [
                    'personId' => 'personId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
            '_personPhoto' => [
                'srcMapperClass' => 'Sample_Person_Post_Mapper',
                'destMapperClass' => 'Sample_Person_Photo_Mapper',
                'srcVarName' => '_personPhoto',
                'destVarName' => '_personPosts',
                'destCountVarName' => '_personPostsCount',
                'destLoadedVarName' => '_personPostsLoaded',
                'fieldLinks' => [
                    'personId' => 'personId',
                    'photoId' => 'photoId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
        ]);
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), [
            'publish' => [
                'relationId' => '_publish',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'publish',
                'plural' => 'publish',
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadPublishFor',
                'loadSrcObjectsMapperMethod' => 'loadForPublish',
                'getSrcObjectsMapperMethod' => 'getOfPublish',
                'createDestObjectMethod' => 'createPublish',
                'getDestObjectMethod' => 'getPublish',
                'setDestObjectMethod' => 'setPublish',
                'clearDestObjectMethod' => 'clearPublish',
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
            'personPhoto' => [
                'relationId' => '_personPhoto',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'personPhoto',
                'plural' => 'personPhotos',
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadPersonPhotosFor',
                'loadSrcObjectsMapperMethod' => 'loadForPersonPhotos',
                'getSrcObjectsMapperMethod' => 'getOfPersonPhotos',
                'createDestObjectMethod' => 'createPersonPhoto',
                'getDestObjectMethod' => 'getPersonPhoto',
                'setDestObjectMethod' => 'setPersonPhoto',
                'clearDestObjectMethod' => 'clearPersonPhoto',
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_person_posts_single'),

                'pluralCaption' => new Ac_Lang_String('sample_person_posts_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'id',
            ],
            'idxPubId' => [
                0 => 'pubId',
            ],
        ];
    }

    /**
     * @return Sample_Person_Post 
     */
    function loadById ($id) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('id').' = '.$this->getDb()->q($id).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Person_Post 
     */
    function loadByPubId ($pubId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('pubId').' = '.$this->getDb()->q($pubId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) one or more personPosts of given one or more publish 
     * @param Sample_Person_Post|array $publish     
     * @return Sample_Person_Post|array of Sample_Person_Post objects  
     */
    function getOfPublish($publish) {
        $rel = $this->getRelation('_publish');
        $res = $rel->getSrc($publish); 
        return $res;
    }
    
    /**
     * Loads one or more personPosts of given one or more publish 
     * @param Sample_Publish|array $publish of Sample_Person_Post objects      
     */
    function loadForPublish($publish) {
        $rel = $this->getRelation('_publish');
        return $rel->loadSrc($publish); 
    }
    
    /**
     * Loads one or more publish of given one or more personPosts 
     * @param Sample_Person_Post|array $personPosts     
     */
    function loadPublishFor($personPosts) {
        $rel = $this->getRelation('_publish');
        return $rel->loadDest($personPosts); 
    }

    /**
     * Returns (but not loads!) several personPosts of given one or more people 
     * @param Sample_Person_Post|array $people     
     * @return Sample_Person_Post|array of Sample_Person_Post objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_person');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads several personPosts of given one or more people 
     * @param Sample_Person|array $people of Sample_Person_Post objects      
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_person');
        return $rel->loadSrc($people); 
    }
    
    /**
     * Loads several people of given one or more personPosts 
     * @param Sample_Person_Post|array $personPosts     
     */
    function loadPeopleFor($personPosts) {
        $rel = $this->getRelation('_person');
        return $rel->loadDest($personPosts); 
    }

    /**
     * Returns (but not loads!) several personPosts of given one or more personPhotos 
     * @param Sample_Person_Post|array $personPhotos     
     * @return Sample_Person_Post|array of Sample_Person_Post objects  
     */
    function getOfPersonPhotos($personPhotos) {
        $rel = $this->getRelation('_personPhoto');
        $res = $rel->getSrc($personPhotos); 
        return $res;
    }
    
    /**
     * Loads several personPosts of given one or more personPhotos 
     * @param Sample_Person_Photo|array $personPhotos of Sample_Person_Post objects      
     */
    function loadForPersonPhotos($personPhotos) {
        $rel = $this->getRelation('_personPhoto');
        return $rel->loadSrc($personPhotos); 
    }
    
    /**
     * Loads several personPhotos of given one or more personPosts 
     * @param Sample_Person_Post|array $personPosts     
     */
    function loadPersonPhotosFor($personPosts) {
        $rel = $this->getRelation('_personPhoto');
        return $rel->loadDest($personPosts); 
    }

    
}

