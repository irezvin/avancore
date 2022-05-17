<?php
/**
 * @method Sample_Religion[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Religion_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'religionId';

    var $recordClass = 'Sample_Religion';

    var $tableName = '#__religion';

    var $id = 'Sample_Religion_Mapper';

    var $shortId = 'religion';

    var $storage = 'Sample_Religion_Storage';

    var $columnNames = [ 0 => 'religionId', 1 => 'title', ];

    var $defaults = [
            'religionId' => NULL,
            'title' => NULL,
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
   
    protected $autoincFieldName = 'religionId';
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_people' => false,
            '_peopleCount' => false,
            '_peopleLoaded' => false,
        ]);
    }
    
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * Creates new Religion instance that is bound to this mapper.
     * 
     * @param string|array $typeIdOrDefaults Id of child mapper (FALSE = create default if possible)
     * @param array $defaults Values of object properties' to assign
     * @return Sample_Religion 
     */ 
    function createRecord($typeIdOrDefaults = false, array $defaults = []) {
        $res = parent::createRecord($typeIdOrDefaults, $defaults);
        return $res;
    }
    
    /**
     * @return Sample_Religion 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Religion 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Religion 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Religion 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Religion[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Religion[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Religion     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Religion     */
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
     * @return Sample_Religion[]
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
     * @return Sample_Religion[]
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
                'srcMapperClass' => 'Sample_Religion_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_people',
                'srcCountVarName' => '_peopleCount',
                'srcLoadedVarName' => '_peopleLoaded',
                'destVarName' => '_religion',
                'fieldLinks' => [
                    'religionId' => 'religionId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
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
                'class' => 'Ac_Model_Association_Many',
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
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_religion_single'),

                'pluralCaption' => new Ac_Lang_String('sample_religion_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'religionId',
            ],
            'Index_2' => [
                0 => 'title',
            ],
        ];
    }

    /**
     * @return Sample_Religion 
     */
    function loadByReligionId ($religionId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('religionId').' = '.$this->getDb()->q($religionId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Religion 
     */
    function loadByTitle ($title) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('title').' = '.$this->getDb()->q($title).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) one or more religion of given one or more people 
     * @param Sample_Religion|array $people     
     * @return array of Sample_Religion objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_people');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads one or more religion of given one or more people 
     * @param Sample_Person|array $people of Sample_Religion objects      
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_people');
        return $rel->loadSrc($people); 
    }
    
    /**
     * Loads one or more people of given one or more religion 
     * @param Sample_Religion|array $religion     
     */
    function loadPeopleFor($religion) {
        $rel = $this->getRelation('_people');
        return $rel->loadDest($religion); 
    }

    
}

