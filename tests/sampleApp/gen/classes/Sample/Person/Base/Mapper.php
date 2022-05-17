<?php
/**
 * @method Sample_Person[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Person_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'personId';

    var $recordClass = 'Sample_Person';

    var $tableName = '#__people';

    var $id = 'Sample_Person_Mapper';

    var $shortId = 'people';

    var $storage = 'Sample_Person_Storage';

    var $columnNames = [ 0 => 'personId', 1 => 'name', 2 => 'gender', 3 => 'isSingle', 4 => 'birthDate', 5 => 'lastUpdatedDatetime', 6 => 'createdTs', 7 => 'religionId', 8 => 'portraitId', ];

    var $nullableColumns = [ 0 => 'lastUpdatedDatetime', 1 => 'religionId', 2 => 'portraitId', ];

    var $defaults = [
            'personId' => NULL,
            'name' => NULL,
            'gender' => 'F',
            'isSingle' => 1,
            'birthDate' => NULL,
            'lastUpdatedDatetime' => NULL,
            'createdTs' => 'CURRENT_TIMESTAMP',
            'religionId' => NULL,
            'portraitId' => NULL,
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
   
    protected $autoincFieldName = 'personId';
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_portraitPersonPhoto' => false,
            '_religion' => false,
            '_tags' => false,
            '_tagsCount' => false,
            '_tagsLoaded' => false,
            '_tagIds' => false,
            '_personAlbums' => false,
            '_personAlbumsCount' => false,
            '_personAlbumsLoaded' => false,
            '_personPhotos' => false,
            '_personPhotosCount' => false,
            '_personPhotosLoaded' => false,
            '_personPosts' => false,
            '_personPostsCount' => false,
            '_personPostsLoaded' => false,
            '_authorPublish' => false,
            '_authorPublishCount' => false,
            '_authorPublishLoaded' => false,
            '_editorPublish' => false,
            '_editorPublishCount' => false,
            '_editorPublishLoaded' => false,
            '_incomingRelations' => false,
            '_incomingRelationsCount' => false,
            '_incomingRelationsLoaded' => false,
            '_outgoingRelations' => false,
            '_outgoingRelationsCount' => false,
            '_outgoingRelationsLoaded' => false,
            '_extraCodeShopProducts' => false,
            '_extraCodeShopProductsCount' => false,
            '_extraCodeShopProductsLoaded' => false,
            '_noteShopProducts' => false,
            '_noteShopProductsCount' => false,
            '_noteShopProductsLoaded' => false,
        ]);
    }
    
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * Creates new People instance that is bound to this mapper.
     * 
     * @param string|array $typeIdOrDefaults Id of child mapper (FALSE = create default if possible)
     * @param array $defaults Values of object properties' to assign
     * @return Sample_Person 
     */ 
    function createRecord($typeIdOrDefaults = false, array $defaults = []) {
        $res = parent::createRecord($typeIdOrDefaults, $defaults);
        return $res;
    }
    
    /**
     * @return Sample_Person 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Person 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Person 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Person 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Person[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Person[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Person     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Person     */
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
     * @return Sample_Person[]
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
     * @return Sample_Person[]
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
            '_portraitPersonPhoto' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Person_Photo_Mapper',
                'srcVarName' => '_portraitPersonPhoto',
                'destVarName' => '_portraitPerson',
                'fieldLinks' => [
                    'personId' => 'personId',
                    'portraitId' => 'photoId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
            '_religion' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Religion_Mapper',
                'srcVarName' => '_religion',
                'destVarName' => '_people',
                'destCountVarName' => '_peopleCount',
                'destLoadedVarName' => '_peopleLoaded',
                'fieldLinks' => [
                    'religionId' => 'religionId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
            '_tags' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Tag_Mapper',
                'srcVarName' => '_tags',
                'srcNNIdsVarName' => '_tagIds',
                'srcCountVarName' => '_tagsCount',
                'srcLoadedVarName' => '_tagsLoaded',
                'destVarName' => '_people',
                'destCountVarName' => '_peopleCount',
                'destLoadedVarName' => '_peopleLoaded',
                'destNNIdsVarName' => '_personIds',
                'fieldLinks' => [
                    'personId' => 'idOfPerson',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'srcLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadTagIdsFor',
                ],
                'destLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadPersonIdsFor',
                ],
                'midTableName' => '#__people_tags',
                'fieldLinks2' => [
                    'idOfTag' => 'tagId',
                ],
            ],
            '_personAlbums' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Person_Album_Mapper',
                'srcVarName' => '_personAlbums',
                'srcCountVarName' => '_personAlbumsCount',
                'srcLoadedVarName' => '_personAlbumsLoaded',
                'destVarName' => '_person',
                'fieldLinks' => [
                    'personId' => 'personId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
            '_personPhotos' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Person_Photo_Mapper',
                'srcVarName' => '_personPhotos',
                'srcCountVarName' => '_personPhotosCount',
                'srcLoadedVarName' => '_personPhotosLoaded',
                'destVarName' => '_person',
                'fieldLinks' => [
                    'personId' => 'personId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
            '_personPosts' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Person_Post_Mapper',
                'srcVarName' => '_personPosts',
                'srcCountVarName' => '_personPostsCount',
                'srcLoadedVarName' => '_personPostsLoaded',
                'destVarName' => '_person',
                'fieldLinks' => [
                    'personId' => 'personId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
            '_authorPublish' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Publish_ImplMapper',
                'srcVarName' => '_authorPublish',
                'srcCountVarName' => '_authorPublishCount',
                'srcLoadedVarName' => '_authorPublishLoaded',
                'destVarName' => '_authorPerson',
                'fieldLinks' => [
                    'personId' => 'authorId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
            '_editorPublish' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Publish_ImplMapper',
                'srcVarName' => '_editorPublish',
                'srcCountVarName' => '_editorPublishCount',
                'srcLoadedVarName' => '_editorPublishLoaded',
                'destVarName' => '_editorPerson',
                'fieldLinks' => [
                    'personId' => 'editorId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
            '_incomingRelations' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Relation_Mapper',
                'srcVarName' => '_incomingRelations',
                'srcCountVarName' => '_incomingRelationsCount',
                'srcLoadedVarName' => '_incomingRelationsLoaded',
                'destVarName' => '_otherPerson',
                'fieldLinks' => [
                    'personId' => 'otherPersonId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
            '_outgoingRelations' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Relation_Mapper',
                'srcVarName' => '_outgoingRelations',
                'srcCountVarName' => '_outgoingRelationsCount',
                'srcLoadedVarName' => '_outgoingRelationsLoaded',
                'destVarName' => '_person',
                'fieldLinks' => [
                    'personId' => 'personId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
            '_extraCodeShopProducts' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Shop_Product_Mapper',
                'srcVarName' => '_extraCodeShopProducts',
                'srcCountVarName' => '_extraCodeShopProductsCount',
                'srcLoadedVarName' => '_extraCodeShopProductsLoaded',
                'fieldLinks' => [
                    'personId' => 'responsiblePersonId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
            '_noteShopProducts' => [
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Shop_Product_Mapper',
                'srcVarName' => '_noteShopProducts',
                'srcCountVarName' => '_noteShopProductsCount',
                'srcLoadedVarName' => '_noteShopProductsLoaded',
                'destVarName' => '_notePerson',
                'fieldLinks' => [
                    'personId' => 'noteAuthorId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
        ]);
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), [
            'portraitPersonPhoto' => [
                'relationId' => '_portraitPersonPhoto',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'portraitPersonPhoto',
                'plural' => 'portraitPersonPhotos',
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadPortraitPersonPhotosFor',
                'loadSrcObjectsMapperMethod' => 'loadForPortraitPersonPhotos',
                'getSrcObjectsMapperMethod' => 'getOfPortraitPersonPhotos',
                'createDestObjectMethod' => 'createPortraitPersonPhoto',
                'getDestObjectMethod' => 'getPortraitPersonPhoto',
                'setDestObjectMethod' => 'setPortraitPersonPhoto',
                'clearDestObjectMethod' => 'clearPortraitPersonPhoto',
            ],
            'religion' => [
                'relationId' => '_religion',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'religion',
                'plural' => 'religion',
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadReligionFor',
                'loadSrcObjectsMapperMethod' => 'loadForReligion',
                'getSrcObjectsMapperMethod' => 'getOfReligion',
                'createDestObjectMethod' => 'createReligion',
                'getDestObjectMethod' => 'getReligion',
                'setDestObjectMethod' => 'setReligion',
                'clearDestObjectMethod' => 'clearReligion',
            ],
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
            'personAlbums' => [
                'relationId' => '_personAlbums',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'personAlbum',
                'plural' => 'personAlbums',
                'class' => 'Ac_Model_Association_Many',
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
            ],
            'personPhotos' => [
                'relationId' => '_personPhotos',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'personPhoto',
                'plural' => 'personPhotos',
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => 'loadPersonPhotosFor',
                'loadSrcObjectsMapperMethod' => 'loadForPersonPhotos',
                'getSrcObjectsMapperMethod' => 'getOfPersonPhotos',
                'createDestObjectMethod' => 'createPersonPhoto',
                'getAllDestObjectsMethod' => 'getAllPersonPhotos',
                'listDestObjectsMethod' => 'listPersonPhotos',
                'countDestObjectsMethod' => 'countPersonPhotos',
                'getDestObjectMethod' => 'getPersonPhoto',
                'addDestObjectMethod' => 'addPersonPhoto',
                'isDestLoadedMethod' => 'isPersonPhotosLoaded',
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
            'authorPublish' => [
                'relationId' => '_authorPublish',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'authorPublish',
                'plural' => 'authorPublish',
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => 'loadAuthorPublishFor',
                'loadSrcObjectsMapperMethod' => 'loadForAuthorPublish',
                'getSrcObjectsMapperMethod' => 'getOfAuthorPublish',
                'createDestObjectMethod' => 'createAuthorPublish',
                'getAllDestObjectsMethod' => 'getAllAuthorPublish',
                'listDestObjectsMethod' => 'listAuthorPublish',
                'countDestObjectsMethod' => 'countAuthorPublish',
                'getDestObjectMethod' => 'getAuthorPublish',
                'addDestObjectMethod' => 'addAuthorPublish',
                'isDestLoadedMethod' => 'isAuthorPublishLoaded',
            ],
            'editorPublish' => [
                'relationId' => '_editorPublish',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'editorPublish',
                'plural' => 'editorPublish',
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => 'loadEditorPublishFor',
                'loadSrcObjectsMapperMethod' => 'loadForEditorPublish',
                'getSrcObjectsMapperMethod' => 'getOfEditorPublish',
                'createDestObjectMethod' => 'createEditorPublish',
                'getAllDestObjectsMethod' => 'getAllEditorPublish',
                'listDestObjectsMethod' => 'listEditorPublish',
                'countDestObjectsMethod' => 'countEditorPublish',
                'getDestObjectMethod' => 'getEditorPublish',
                'addDestObjectMethod' => 'addEditorPublish',
                'isDestLoadedMethod' => 'isEditorPublishLoaded',
            ],
            'incomingRelations' => [
                'relationId' => '_incomingRelations',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'incomingRelation',
                'plural' => 'incomingRelations',
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => 'loadIncomingRelationsFor',
                'loadSrcObjectsMapperMethod' => 'loadForIncomingRelations',
                'getSrcObjectsMapperMethod' => 'getOfIncomingRelations',
                'createDestObjectMethod' => 'createIncomingRelation',
                'getAllDestObjectsMethod' => 'getAllIncomingRelations',
                'listDestObjectsMethod' => 'listIncomingRelations',
                'countDestObjectsMethod' => 'countIncomingRelations',
                'getDestObjectMethod' => 'getIncomingRelation',
                'addDestObjectMethod' => 'addIncomingRelation',
                'isDestLoadedMethod' => 'isIncomingRelationsLoaded',
            ],
            'outgoingRelations' => [
                'relationId' => '_outgoingRelations',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'outgoingRelation',
                'plural' => 'outgoingRelations',
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => 'loadOutgoingRelationsFor',
                'loadSrcObjectsMapperMethod' => 'loadForOutgoingRelations',
                'getSrcObjectsMapperMethod' => 'getOfOutgoingRelations',
                'createDestObjectMethod' => 'createOutgoingRelation',
                'getAllDestObjectsMethod' => 'getAllOutgoingRelations',
                'listDestObjectsMethod' => 'listOutgoingRelations',
                'countDestObjectsMethod' => 'countOutgoingRelations',
                'getDestObjectMethod' => 'getOutgoingRelation',
                'addDestObjectMethod' => 'addOutgoingRelation',
                'isDestLoadedMethod' => 'isOutgoingRelationsLoaded',
            ],
            'extraCodeShopProducts' => [
                'relationId' => '_extraCodeShopProducts',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'extraCodeShopProduct',
                'plural' => 'extraCodeShopProducts',
                'canLoadDestObjects' => false,
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => NULL,
                'loadSrcObjectsMapperMethod' => 'loadForExtraCodeShopProducts',
                'getSrcObjectsMapperMethod' => 'getOfExtraCodeShopProducts',
                'createDestObjectMethod' => 'createExtraCodeShopProduct',
                'getAllDestObjectsMethod' => 'getAllExtraCodeShopProducts',
                'listDestObjectsMethod' => 'listExtraCodeShopProducts',
                'countDestObjectsMethod' => 'countExtraCodeShopProducts',
                'getDestObjectMethod' => 'getExtraCodeShopProduct',
                'addDestObjectMethod' => 'addExtraCodeShopProduct',
                'isDestLoadedMethod' => 'isExtraCodeShopProductsLoaded',
            ],
            'noteShopProducts' => [
                'relationId' => '_noteShopProducts',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'noteShopProduct',
                'plural' => 'noteShopProducts',
                'canLoadDestObjects' => false,
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => NULL,
                'loadSrcObjectsMapperMethod' => 'loadForNoteShopProducts',
                'getSrcObjectsMapperMethod' => 'getOfNoteShopProducts',
                'createDestObjectMethod' => 'createNoteShopProduct',
                'getAllDestObjectsMethod' => 'getAllNoteShopProducts',
                'listDestObjectsMethod' => 'listNoteShopProducts',
                'countDestObjectsMethod' => 'countNoteShopProducts',
                'getDestObjectMethod' => 'getNoteShopProduct',
                'addDestObjectMethod' => 'addNoteShopProduct',
                'isDestLoadedMethod' => 'isNoteShopProductsLoaded',
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_people_single'),

                'pluralCaption' => new Ac_Lang_String('sample_people_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'personId',
            ],
        ];
    }

    /**
     * @return Sample_Person 
     */
    function loadByPersonId ($personId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('personId').' = '.$this->getDb()->q($personId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) one or more people of given one or more personPhotos 
     * @param Sample_Person|array $portraitPersonPhotos     
     * @return Sample_Person|array of Sample_Person objects  
     */
    function getOfPortraitPersonPhotos($portraitPersonPhotos) {
        $rel = $this->getRelation('_portraitPersonPhoto');
        $res = $rel->getSrc($portraitPersonPhotos); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more personPhotos 
     * @param Sample_Person_Photo|array $portraitPersonPhotos of Sample_Person objects      
     */
    function loadForPortraitPersonPhotos($portraitPersonPhotos) {
        $rel = $this->getRelation('_portraitPersonPhoto');
        return $rel->loadSrc($portraitPersonPhotos); 
    }
    
    /**
     * Loads one or more personPhotos of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadPortraitPersonPhotosFor($people) {
        $rel = $this->getRelation('_portraitPersonPhoto');
        return $rel->loadDest($people); 
    }

    /**
     * Returns (but not loads!) several people of given one or more religion 
     * @param Sample_Person|array $religion     
     * @return Sample_Person|array of Sample_Person objects  
     */
    function getOfReligion($religion) {
        $rel = $this->getRelation('_religion');
        $res = $rel->getSrc($religion); 
        return $res;
    }
    
    /**
     * Loads several people of given one or more religion 
     * @param Sample_Religion|array $religion of Sample_Person objects      
     */
    function loadForReligion($religion) {
        $rel = $this->getRelation('_religion');
        return $rel->loadSrc($religion); 
    }
    
    /**
     * Loads several religion of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadReligionFor($people) {
        $rel = $this->getRelation('_religion');
        return $rel->loadDest($people); 
    }

    /**
     * Returns (but not loads!) one or more people of given one or more tags 
     * @param Sample_Person|array $tags     
     * @return Sample_Person|array of Sample_Person objects  
     */
    function getOfTags($tags) {
        $rel = $this->getRelation('_tags');
        $res = $rel->getSrc($tags); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more tags 
     * @param Sample_Tag|array $tags of Sample_Person objects      
     */
    function loadForTags($tags) {
        $rel = $this->getRelation('_tags');
        return $rel->loadSrc($tags); 
    }
    
    /**
     * Loads one or more tags of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadTagsFor($people) {
        $rel = $this->getRelation('_tags');
        return $rel->loadDest($people); 
    }


    /**
     * @param Sample_Person|array $people 
     */
     function loadTagIdsFor($people) {
        $rel = $this->getRelation('_tags');
        return $rel->loadDestNNIds($people); 
    }
    
    /**
     * Returns (but not loads!) one or more people of given one or more personAlbums 
     * @param Sample_Person|array $personAlbums     
     * @return array of Sample_Person objects  
     */
    function getOfPersonAlbums($personAlbums) {
        $rel = $this->getRelation('_personAlbums');
        $res = $rel->getSrc($personAlbums); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more personAlbums 
     * @param Sample_Person_Album|array $personAlbums of Sample_Person objects      
     */
    function loadForPersonAlbums($personAlbums) {
        $rel = $this->getRelation('_personAlbums');
        return $rel->loadSrc($personAlbums); 
    }
    
    /**
     * Loads one or more personAlbums of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadPersonAlbumsFor($people) {
        $rel = $this->getRelation('_personAlbums');
        return $rel->loadDest($people); 
    }

    /**
     * Returns (but not loads!) one or more people of given one or more personPhotos 
     * @param Sample_Person|array $personPhotos     
     * @return array of Sample_Person objects  
     */
    function getOfPersonPhotos($personPhotos) {
        $rel = $this->getRelation('_personPhotos');
        $res = $rel->getSrc($personPhotos); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more personPhotos 
     * @param Sample_Person_Photo|array $personPhotos of Sample_Person objects      
     */
    function loadForPersonPhotos($personPhotos) {
        $rel = $this->getRelation('_personPhotos');
        return $rel->loadSrc($personPhotos); 
    }
    
    /**
     * Loads one or more personPhotos of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadPersonPhotosFor($people) {
        $rel = $this->getRelation('_personPhotos');
        return $rel->loadDest($people); 
    }

    /**
     * Returns (but not loads!) one or more people of given one or more personPosts 
     * @param Sample_Person|array $personPosts     
     * @return array of Sample_Person objects  
     */
    function getOfPersonPosts($personPosts) {
        $rel = $this->getRelation('_personPosts');
        $res = $rel->getSrc($personPosts); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more personPosts 
     * @param Sample_Person_Post|array $personPosts of Sample_Person objects      
     */
    function loadForPersonPosts($personPosts) {
        $rel = $this->getRelation('_personPosts');
        return $rel->loadSrc($personPosts); 
    }
    
    /**
     * Loads one or more personPosts of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadPersonPostsFor($people) {
        $rel = $this->getRelation('_personPosts');
        return $rel->loadDest($people); 
    }

    /**
     * Returns (but not loads!) one or more people of given one or more publish 
     * @param Sample_Person|array $authorPublish     
     * @return array of Sample_Person objects  
     */
    function getOfAuthorPublish($authorPublish) {
        $rel = $this->getRelation('_authorPublish');
        $res = $rel->getSrc($authorPublish); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more publish 
     * @param Sample_Publish|array $authorPublish of Sample_Person objects      
     */
    function loadForAuthorPublish($authorPublish) {
        $rel = $this->getRelation('_authorPublish');
        return $rel->loadSrc($authorPublish); 
    }
    
    /**
     * Loads one or more publish of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadAuthorPublishFor($people) {
        $rel = $this->getRelation('_authorPublish');
        return $rel->loadDest($people); 
    }

    /**
     * Returns (but not loads!) one or more people of given one or more publish 
     * @param Sample_Person|array $editorPublish     
     * @return array of Sample_Person objects  
     */
    function getOfEditorPublish($editorPublish) {
        $rel = $this->getRelation('_editorPublish');
        $res = $rel->getSrc($editorPublish); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more publish 
     * @param Sample_Publish|array $editorPublish of Sample_Person objects      
     */
    function loadForEditorPublish($editorPublish) {
        $rel = $this->getRelation('_editorPublish');
        return $rel->loadSrc($editorPublish); 
    }
    
    /**
     * Loads one or more publish of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadEditorPublishFor($people) {
        $rel = $this->getRelation('_editorPublish');
        return $rel->loadDest($people); 
    }

    /**
     * Returns (but not loads!) one or more people of given one or more relations 
     * @param Sample_Person|array $incomingRelations     
     * @return array of Sample_Person objects  
     */
    function getOfIncomingRelations($incomingRelations) {
        $rel = $this->getRelation('_incomingRelations');
        $res = $rel->getSrc($incomingRelations); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more relations 
     * @param Sample_Relation|array $incomingRelations of Sample_Person objects      
     */
    function loadForIncomingRelations($incomingRelations) {
        $rel = $this->getRelation('_incomingRelations');
        return $rel->loadSrc($incomingRelations); 
    }
    
    /**
     * Loads one or more relations of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadIncomingRelationsFor($people) {
        $rel = $this->getRelation('_incomingRelations');
        return $rel->loadDest($people); 
    }

    /**
     * Returns (but not loads!) one or more people of given one or more relations 
     * @param Sample_Person|array $outgoingRelations     
     * @return array of Sample_Person objects  
     */
    function getOfOutgoingRelations($outgoingRelations) {
        $rel = $this->getRelation('_outgoingRelations');
        $res = $rel->getSrc($outgoingRelations); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more relations 
     * @param Sample_Relation|array $outgoingRelations of Sample_Person objects      
     */
    function loadForOutgoingRelations($outgoingRelations) {
        $rel = $this->getRelation('_outgoingRelations');
        return $rel->loadSrc($outgoingRelations); 
    }
    
    /**
     * Loads one or more relations of given one or more people 
     * @param Sample_Person|array $people     
     */
    function loadOutgoingRelationsFor($people) {
        $rel = $this->getRelation('_outgoingRelations');
        return $rel->loadDest($people); 
    }

    /**
     * Returns (but not loads!) one or more people of given one or more shopProducts 
     * @param Sample_Person|array $extraCodeShopProducts     
     * @return array of Sample_Person objects  
     */
    function getOfExtraCodeShopProducts($extraCodeShopProducts) {
        $rel = $this->getRelation('_extraCodeShopProducts');
        $res = $rel->getSrc($extraCodeShopProducts); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more shopProducts 
     * @param Sample_Shop_Product|array $extraCodeShopProducts of Sample_Person objects      
     */
    function loadForExtraCodeShopProducts($extraCodeShopProducts) {
        $rel = $this->getRelation('_extraCodeShopProducts');
        return $rel->loadSrc($extraCodeShopProducts); 
    }
    

    /**
     * Returns (but not loads!) one or more people of given one or more shopProducts 
     * @param Sample_Person|array $noteShopProducts     
     * @return array of Sample_Person objects  
     */
    function getOfNoteShopProducts($noteShopProducts) {
        $rel = $this->getRelation('_noteShopProducts');
        $res = $rel->getSrc($noteShopProducts); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more shopProducts 
     * @param Sample_Shop_Product|array $noteShopProducts of Sample_Person objects      
     */
    function loadForNoteShopProducts($noteShopProducts) {
        $rel = $this->getRelation('_noteShopProducts');
        return $rel->loadSrc($noteShopProducts); 
    }
    

    
}

