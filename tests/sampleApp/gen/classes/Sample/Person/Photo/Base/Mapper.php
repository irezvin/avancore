<?php
/**
 * @method Sample_Person_Photo[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Person_Photo_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'photoId';

    var $recordClass = 'Sample_Person_Photo';

    var $tableName = '#__person_photos';

    var $id = 'Sample_Person_Photo_Mapper';

    var $shortId = 'personPhotos';

    var $storage = 'Sample_Person_Photo_Storage';

    var $columnNames = [ 0 => 'photoId', 1 => 'personId', 2 => 'filename', ];

    var $defaults = [
            'photoId' => NULL,
            'personId' => NULL,
            'filename' => '',
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
   
    protected $autoincFieldName = 'photoId';
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_person' => false,
            '_personAlbums' => false,
            '_personAlbumsCount' => false,
            '_personAlbumsLoaded' => false,
            '_personAlbumIds' => false,
            '_portraitPerson' => false,
            '_personPosts' => false,
            '_personPostsCount' => false,
            '_personPostsLoaded' => false,
        ]);
    }
    
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * Creates new PersonPhotos instance that is bound to this mapper.
     * 
     * @param string|array $typeIdOrDefaults Id of child mapper (FALSE = create default if possible)
     * @param array $defaults Values of object properties' to assign
     * @return Sample_Person_Photo 
     */ 
    function createRecord($typeIdOrDefaults = false, array $defaults = []) {
        $res = parent::createRecord($typeIdOrDefaults, $defaults);
        return $res;
    }
    
    /**
     * @return Sample_Person_Photo 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Person_Photo 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Person_Photo 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Person_Photo 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Person_Photo[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Person_Photo[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Person_Photo     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Person_Photo     */
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
     * @return Sample_Person_Photo[]
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
     * @return Sample_Person_Photo[]
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
        return 'filename';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), [
            '_person' => [
                'srcMapperClass' => 'Sample_Person_Photo_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_person',
                'destVarName' => '_personPhotos',
                'destCountVarName' => '_personPhotosCount',
                'destLoadedVarName' => '_personPhotosLoaded',
                'fieldLinks' => [
                    'personId' => 'personId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
            '_personAlbums' => [
                'srcMapperClass' => 'Sample_Person_Photo_Mapper',
                'destMapperClass' => 'Sample_Person_Album_Mapper',
                'srcVarName' => '_personAlbums',
                'srcNNIdsVarName' => '_personAlbumIds',
                'srcCountVarName' => '_personAlbumsCount',
                'srcLoadedVarName' => '_personAlbumsLoaded',
                'destVarName' => '_personPhotos',
                'destCountVarName' => '_personPhotosCount',
                'destLoadedVarName' => '_personPhotosLoaded',
                'destNNIdsVarName' => '_personPhotoIds',
                'fieldLinks' => [
                    'personId' => 'personId',
                    'photoId' => 'photoId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'srcLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadPersonAlbumIdsFor',
                ],
                'destLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadPersonPhotoIdsFor',
                ],
                'midTableName' => '#__album_photos',
                'fieldLinks2' => [
                    'personId' => 'personId',
                    'albumId' => 'albumId',
                ],
            ],
            '_portraitPerson' => [
                'srcMapperClass' => 'Sample_Person_Photo_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_portraitPerson',
                'destVarName' => '_portraitPersonPhoto',
                'fieldLinks' => [
                    'personId' => 'personId',
                    'photoId' => 'portraitId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => true,
            ],
            '_personPosts' => [
                'srcMapperClass' => 'Sample_Person_Photo_Mapper',
                'destMapperClass' => 'Sample_Person_Post_Mapper',
                'srcVarName' => '_personPosts',
                'srcCountVarName' => '_personPostsCount',
                'srcLoadedVarName' => '_personPostsLoaded',
                'destVarName' => '_personPhoto',
                'fieldLinks' => [
                    'personId' => 'personId',
                    'photoId' => 'photoId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
        ]);
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), [
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
            'personAlbums' => [
                'relationId' => '_personAlbums',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'personAlbum',
                'plural' => 'personAlbums',
                'class' => 'Ac_Model_Association_ManyToMany',
                'loadDestObjectsMapperMethod' => 'loadPersonAlbumsFor',
                'loadSrcObjectsMapperMethod' => 'loadForPersonAlbums',
                'getSrcObjectsMapperMethod' => 'getOfPersonAlbums',
                'createDestObjectMethod' => 'createPersonAlbum',
                'getAllDestObjectsMethod' => 'getAllPersonAlbums',
                'listDestObjectsMethod' => 'listPersonAlbums',
                'countDestObjectsMethod' => 'countPersonAlbums',
                'getDestObjectMethod' => 'getPersonAlbum',
                'addDestObjectMethod' => 'addPersonAlbum',
                'isDestLoadedMethod' => 'isPersonAlbumsLoaded',
                'loadDestIdsMapperMethod' => 'loadPersonAlbumIdsFor',
                'getDestIdsMethod' => 'getPersonAlbumIds',
                'setDestIdsMethod' => 'setPersonAlbumIds',
                'clearDestObjectsMethod' => 'clearPersonAlbums',
            ],
            'portraitPerson' => [
                'relationId' => '_portraitPerson',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'portraitPerson',
                'plural' => 'portraitPeople',
                'isReferenced' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadPortraitPeopleFor',
                'loadSrcObjectsMapperMethod' => 'loadForPortraitPeople',
                'getSrcObjectsMapperMethod' => 'getOfPortraitPeople',
                'createDestObjectMethod' => 'createPortraitPerson',
                'getDestObjectMethod' => 'getPortraitPerson',
                'setDestObjectMethod' => 'setPortraitPerson',
                'clearDestObjectMethod' => 'clearPortraitPerson',
            ],
            'personPosts' => [
                'relationId' => '_personPosts',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'personPost',
                'plural' => 'personPosts',
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => 'loadPersonPostsFor',
                'loadSrcObjectsMapperMethod' => 'loadForPersonPosts',
                'getSrcObjectsMapperMethod' => 'getOfPersonPosts',
                'createDestObjectMethod' => 'createPersonPost',
                'getAllDestObjectsMethod' => 'getAllPersonPosts',
                'listDestObjectsMethod' => 'listPersonPosts',
                'countDestObjectsMethod' => 'countPersonPosts',
                'getDestObjectMethod' => 'getPersonPost',
                'addDestObjectMethod' => 'addPersonPost',
                'isDestLoadedMethod' => 'isPersonPostsLoaded',
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_person_photos_single'),

                'pluralCaption' => new Ac_Lang_String('sample_person_photos_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'photoId',
            ],
        ];
    }

    /**
     * @return Sample_Person_Photo 
     */
    function loadByPhotoId ($photoId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('photoId').' = '.$this->getDb()->q($photoId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) several personPhotos of given one or more people 
     * @param Sample_Person_Photo|array $people     
     * @return Sample_Person_Photo|array of Sample_Person_Photo objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_person');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads several personPhotos of given one or more people 
     * @param Sample_Person|array $people of Sample_Person_Photo objects      
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_person');
        return $rel->loadSrc($people); 
    }
    
    /**
     * Loads several people of given one or more personPhotos 
     * @param Sample_Person_Photo|array $personPhotos     
     */
    function loadPeopleFor($personPhotos) {
        $rel = $this->getRelation('_person');
        return $rel->loadDest($personPhotos); 
    }

    /**
     * Returns (but not loads!) one or more personPhotos of given one or more personAlbums 
     * @param Sample_Person_Photo|array $personAlbums     
     * @return Sample_Person_Photo|array of Sample_Person_Photo objects  
     */
    function getOfPersonAlbums($personAlbums) {
        $rel = $this->getRelation('_personAlbums');
        $res = $rel->getSrc($personAlbums); 
        return $res;
    }
    
    /**
     * Loads one or more personPhotos of given one or more personAlbums 
     * @param Sample_Person_Album|array $personAlbums of Sample_Person_Photo objects      
     */
    function loadForPersonAlbums($personAlbums) {
        $rel = $this->getRelation('_personAlbums');
        return $rel->loadSrc($personAlbums); 
    }
    
    /**
     * Loads one or more personAlbums of given one or more personPhotos 
     * @param Sample_Person_Photo|array $personPhotos     
     */
    function loadPersonAlbumsFor($personPhotos) {
        $rel = $this->getRelation('_personAlbums');
        return $rel->loadDest($personPhotos); 
    }


    /**
     * @param Sample_Person_Photo|array $personPhotos 
     */
     function loadPersonAlbumIdsFor($personPhotos) {
        $rel = $this->getRelation('_personAlbums');
        return $rel->loadDestNNIds($personPhotos); 
    }
    
    /**
     * Returns (but not loads!) one or more personPhotos of given one or more people 
     * @param Sample_Person_Photo|array $portraitPeople     
     * @return Sample_Person_Photo|array of Sample_Person_Photo objects  
     */
    function getOfPortraitPeople($portraitPeople) {
        $rel = $this->getRelation('_portraitPerson');
        $res = $rel->getSrc($portraitPeople); 
        return $res;
    }
    
    /**
     * Loads one or more personPhotos of given one or more people 
     * @param Sample_Person|array $portraitPeople of Sample_Person_Photo objects      
     */
    function loadForPortraitPeople($portraitPeople) {
        $rel = $this->getRelation('_portraitPerson');
        return $rel->loadSrc($portraitPeople); 
    }
    
    /**
     * Loads one or more people of given one or more personPhotos 
     * @param Sample_Person_Photo|array $personPhotos     
     */
    function loadPortraitPeopleFor($personPhotos) {
        $rel = $this->getRelation('_portraitPerson');
        return $rel->loadDest($personPhotos); 
    }

    /**
     * Returns (but not loads!) one or more personPhotos of given one or more personPosts 
     * @param Sample_Person_Photo|array $personPosts     
     * @return array of Sample_Person_Photo objects  
     */
    function getOfPersonPosts($personPosts) {
        $rel = $this->getRelation('_personPosts');
        $res = $rel->getSrc($personPosts); 
        return $res;
    }
    
    /**
     * Loads one or more personPhotos of given one or more personPosts 
     * @param Sample_Person_Post|array $personPosts of Sample_Person_Photo objects      
     */
    function loadForPersonPosts($personPosts) {
        $rel = $this->getRelation('_personPosts');
        return $rel->loadSrc($personPosts); 
    }
    
    /**
     * Loads one or more personPosts of given one or more personPhotos 
     * @param Sample_Person_Photo|array $personPhotos     
     */
    function loadPersonPostsFor($personPhotos) {
        $rel = $this->getRelation('_personPosts');
        return $rel->loadDest($personPhotos); 
    }

    
}

