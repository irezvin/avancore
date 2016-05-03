<?php
/**
 * @method Sample_Person_Album[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Person_Album_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'albumId'; 

    var $recordClass = 'Sample_Person_Album'; 

    var $tableName = '#__person_albums'; 

    var $id = 'Sample_Person_Album_Mapper'; 

    var $storage = 'Sample_Person_Album_Storage'; 

    var $columnNames = array ( 0 => 'albumId', 1 => 'personId', 2 => 'albumName', ); 

    var $defaults = array (
            'albumId' => NULL,
            'personId' => '0',
            'albumName' => '\'\'',
        ); 
 
   
    protected $autoincFieldName = 'albumId';
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), array (
            '_person' => false,
            '_personPhotos' => false,
            '_personPhotosCount' => false,
            '_personPhotosLoaded' => false,
            '_personPhotoIds' => false,
        ));
    }
    
    /**
     * @return Sample_Person_Album 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Person_Album_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Person_Album 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Person_Album 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Person_Album 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Person_Album 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Person_Album 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Person_Album[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Person_Album[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Person_Album     */
    function findFirst (array $query = array(), $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Person_Album     */
    function findOne (array $query = array()) {
        return parent::findOne($query);
    }
    
    /**
     * @param array $query
     * @param mixed $keysToList
     * @param mixed $sort
     * @param int $limit
     * @param int $offset
     * @param bool $forceStorage
     * @return Sample_Person_Album[]
     */
    function find (array $query = array(), $keysToList = true, $sort = false, $limit = false, $offset = false, & $remainingQuery = array(), & $sorted = false) {
        if (func_num_args() > 5) $remainingQuery = true;
        return parent::find($query, $keysToList, $sort, $limit, $offset, $remainingQuery, $sorted);
    }
    
    /**
     * Does partial search.
     * 
     * Objects are always returned by-identifiers.
     * 
     * @return Sample_Person_Album[]
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
    function filter (array $records, array $query = array(), $sort = false, $limit = false, $offset = false, & $remainingQuery = true, & $sorted = false, $areByIds = false) {
        if (func_num_args() > 5) $remainingQuery = true;
        return parent::filter($records, $query, $sort, $limit, $offset, $remainingQuery, $sorted, $areByIds);
    }
    

    
    function getTitleFieldName() {
        return 'albumName';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_person' => array (
                'srcMapperClass' => 'Sample_Person_Album_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_person',
                'destVarName' => '_personAlbums',
                'destCountVarName' => '_personAlbumsCount',
                'destLoadedVarName' => '_personAlbumsLoaded',
                'fieldLinks' => array (
                    'personId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
            '_personPhotos' => array (
                'srcMapperClass' => 'Sample_Person_Album_Mapper',
                'destMapperClass' => 'Sample_Person_Photo_Mapper',
                'srcVarName' => '_personPhotos',
                'srcNNIdsVarName' => '_personPhotoIds',
                'srcCountVarName' => '_personPhotosCount',
                'srcLoadedVarName' => '_personPhotosLoaded',
                'destVarName' => '_personAlbums',
                'destCountVarName' => '_personAlbumsCount',
                'destLoadedVarName' => '_personAlbumsLoaded',
                'destNNIdsVarName' => '_personAlbumIds',
                'fieldLinks' => array (
                    'personId' => 'personId',
                    'albumId' => 'albumId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'srcLoadNNIdsMethod' => array (
                    0 => true,
                    1 => 'loadPersonPhotoIdsFor',
                ),
                'destLoadNNIdsMethod' => array (
                    0 => true,
                    1 => 'loadPersonAlbumIdsFor',
                ),
                'midTableName' => '#__album_photos',
                'fieldLinks2' => array (
                    'personId' => 'personId',
                    'photoId' => 'photoId',
                ),
            ),
        ));
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), array (
            'person' => array (
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
            ),
            'personPhotos' => array (
                'relationId' => '_personPhotos',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'personPhoto',
                'plural' => 'personPhotos',
                'class' => 'Ac_Model_Association_ManyToMany',
                'loadDestObjectsMapperMethod' => 'loadPersonPhotosFor',
                'loadSrcObjectsMapperMethod' => 'loadForPersonPhotos',
                'getSrcObjectsMapperMethod' => 'getOfPersonPhotos',
                'createDestObjectMethod' => 'createPersonPhoto',
                'listDestObjectsMethod' => 'listPersonPhotos',
                'countDestObjectsMethod' => 'countPersonPhotos',
                'getDestObjectMethod' => 'getPersonPhoto',
                'addDestObjectMethod' => 'addPersonPhoto',
                'isDestLoadedMethod' => 'isPersonPhotosLoaded',
                'loadDestIdsMapperMethod' => 'loadPersonPhotoIdsFor',
                'getDestIdsMethod' => 'getPersonPhotoIds',
                'setDestIdsMethod' => 'setPersonPhotoIds',
                'clearDestObjectsMethod' => 'clearPersonPhotos',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => new Ac_Lang_String('sample_person_albums_single'),
                'pluralCaption' => new Ac_Lang_String('sample_person_albums_plural'),
            ),
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'albumId',
            ),
        );
    }

    /**
     * @return Sample_Person_Album 
     */
    function loadByAlbumId ($albumId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('albumId').' = '.$this->getDb()->q($albumId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) several personAlbums of given one or more people 
     * @param Sample_Person_Album|array $people     
     * @return Sample_Person_Album|array of Sample_Person_Album objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_person');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads several personAlbums of given one or more people 
     * @param Sample_Person|array $people of Sample_Person_Album objects      
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_person');
        return $rel->loadSrc($people); 
    }
    
    /**
     * Loads several people of given one or more personAlbums 
     * @param Sample_Person_Album|array $personAlbums     
     */
    function loadPeopleFor($personAlbums) {
        $rel = $this->getRelation('_person');
        return $rel->loadDest($personAlbums); 
    }

    /**
     * Returns (but not loads!) one or more personAlbums of given one or more personPhotos 
     * @param Sample_Person_Album|array $personPhotos     
     * @return Sample_Person_Album|array of Sample_Person_Album objects  
     */
    function getOfPersonPhotos($personPhotos) {
        $rel = $this->getRelation('_personPhotos');
        $res = $rel->getSrc($personPhotos); 
        return $res;
    }
    
    /**
     * Loads one or more personAlbums of given one or more personPhotos 
     * @param Sample_Person_Photo|array $personPhotos of Sample_Person_Album objects      
     */
    function loadForPersonPhotos($personPhotos) {
        $rel = $this->getRelation('_personPhotos');
        return $rel->loadSrc($personPhotos); 
    }
    
    /**
     * Loads one or more personPhotos of given one or more personAlbums 
     * @param Sample_Person_Album|array $personAlbums     
     */
    function loadPersonPhotosFor($personAlbums) {
        $rel = $this->getRelation('_personPhotos');
        return $rel->loadDest($personAlbums); 
    }


    /**
     * @param Sample_Person_Album|array $personAlbums 
     */
     function loadPersonPhotoIdsFor($personAlbums) {
        $rel = $this->getRelation('_personPhotos');
        return $rel->loadDestNNIds($personAlbums); 
    }
    
    
}

