<?php
/**
 * @method Sample_Publish[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Publish_Base_ImplMapper extends Ac_Model_Mapper {

    protected $identifierField = NULL;

    var $pk = 'id';

    var $recordClass = 'Ac_Model_Record';

    var $tableName = '#__publish';

    var $id = 'Sample_Publish_ImplMapper';

    var $shortId = 'publish';

    var $storage = 'Sample_Publish_Storage';

    var $columnNames = [ 0 => 'id', 1 => 'sharedObjectType', 2 => 'published', 3 => 'deleted', 4 => 'publishUp', 5 => 'publishDown', 6 => 'authorId', 7 => 'editorId', 8 => 'pubChannelId', 9 => 'dateCreated', 10 => 'dateModified', 11 => 'dateDeleted', ];

    var $nullableColumns = [ 0 => 'published', 1 => 'deleted', 2 => 'publishUp', 3 => 'publishDown', 4 => 'authorId', 5 => 'editorId', 6 => 'pubChannelId', ];

    var $defaults = [
            'id' => NULL,
            'sharedObjectType' => NULL,
            'published' => 1,
            'deleted' => 0,
            'publishUp' => '0000-00-00 00:00:00',
            'publishDown' => '0000-00-00 00:00:00',
            'authorId' => NULL,
            'editorId' => NULL,
            'pubChannelId' => NULL,
            'dateCreated' => '0000-00-00 00:00:00',
            'dateModified' => '0000-00-00 00:00:00',
            'dateDeleted' => '0000-00-00 00:00:00',
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
   
    protected $autoincFieldName = 'id';
    protected $askRelationsForDefaults = false;
 
    protected function doGetCoreMixables() { 
        return Ac_Util::m(parent::doGetCoreMixables(), [
            'Ac_Model_Typer_Abstract' => [
                'class' => 'Ac_Model_Typer_ExtraTable',
                'tableName' => '#__publish',
                'objectTypeField' => 'sharedObjectType',
            ],
        ]);
    }
    
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_authorPerson' => false,
            '_editorPerson' => false,
        ]);
    }
    
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * Creates new Publish instance that is bound to this mapper.
     * 
     * @param string|array $typeIdOrDefaults Id of child mapper (FALSE = create default if possible)
     * @param array $defaults Values of object properties' to assign
     * @return Sample_Publish 
     */ 
    function createRecord($typeIdOrDefaults = false, array $defaults = []) {
        $res = parent::createRecord($typeIdOrDefaults, $defaults);
        return $res;
    }
    
    /**
     * @return Sample_Publish 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Publish 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Publish 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Publish 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Publish[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Publish[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Publish     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Publish     */
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
     * @return Sample_Publish[]
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
     * @return Sample_Publish[]
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
        return 'pubChannelId';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), [
            '_authorPerson' => [
                'srcMapperClass' => 'Sample_Publish_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_authorPerson',
                'destVarName' => '_authorPublish',
                'destCountVarName' => '_authorPublishCount',
                'destLoadedVarName' => '_authorPublishLoaded',
                'fieldLinks' => [
                    'authorId' => 'personId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
            '_editorPerson' => [
                'srcMapperClass' => 'Sample_Publish_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_editorPerson',
                'destVarName' => '_editorPublish',
                'destCountVarName' => '_editorPublishCount',
                'destLoadedVarName' => '_editorPublishLoaded',
                'fieldLinks' => [
                    'editorId' => 'personId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_publish_single'),

                'pluralCaption' => new Ac_Lang_String('sample_publish_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'id',
            ],
            'idxPubChannelId' => [
                0 => 'pubChannelId',
            ],
        ];
    }

    /**
     * @return Sample_Publish 
     */
    function loadById ($id) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('id').' = '.$this->getDb()->q($id).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Publish 
     */
    function loadByPubChannelId ($pubChannelId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('pubChannelId').' = '.$this->getDb()->q($pubChannelId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
}

