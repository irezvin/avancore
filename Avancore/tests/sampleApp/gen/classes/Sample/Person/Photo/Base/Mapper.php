<?php

class Sample_Person_Photo_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'photoId'; 

    var $recordClass = 'Sample_Person_Photo'; 

    var $tableName = '#__person_photos'; 

    var $id = 'Sample_Person_Photo_Mapper'; 

    var $columnNames = array ( 0 => 'photoId', 1 => 'personId', 2 => 'filename', ); 

    var $defaults = array (
            'photoId' => NULL,
            'personId' => NULL,
            'filename' => '',
        ); 

    var $typeName = 'personPhotos'; 
 
   
    protected $autoincFieldName = 'photoId';
    protected $askRelationsForDefaults = false;
 
 
    function listSqlColumns() {
        return $this->columnNames;
    }
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), array (
            '_person' => false,
            '_personAlbums' => false,
            '_personAlbumsCount' => false,
            '_personAlbumsLoaded' => false,
            '_personAlbumIds' => false,
            '_portraitPerson' => false,
            '_personPosts' => false,
            '_personPostsCount' => false,
            '_personPostsLoaded' => false,
        ));
    }
    
    /**
     * @return Sample_Person_Photo 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Person_Photo_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Person_Photo 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Person_Photo 
     */ 
    function reference ($values = array()) {
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

    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_person' => array (
                'srcMapperClass' => 'Sample_Person_Photo_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_person',
                'destVarName' => '_personPhotos',
                'destCountVarName' => '_personPhotosCount',
                'destLoadedVarName' => '_personPhotosLoaded',
                'fieldLinks' => array (
                    'personId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
            '_personAlbums' => array (
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
                'fieldLinks' => array (
                    'personId' => 'personId',
                    'photoId' => 'photoId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'midTableName' => '#__album_photos',
                'fieldLinks2' => array (
                    'personId' => 'personId',
                    'albumId' => 'albumId',
                ),
            ),
            '_portraitPerson' => array (
                'srcMapperClass' => 'Sample_Person_Photo_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_portraitPerson',
                'destVarName' => '_portraitPersonPhoto',
                'fieldLinks' => array (
                    'personId' => 'personId',
                    'photoId' => 'portraitId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => true,
            ),
            '_personPosts' => array (
                'srcMapperClass' => 'Sample_Person_Photo_Mapper',
                'destMapperClass' => 'Sample_Person_Post_Mapper',
                'srcVarName' => '_personPosts',
                'srcCountVarName' => '_personPostsCount',
                'srcLoadedVarName' => '_personPostsLoaded',
                'destVarName' => '_personPhoto',
                'fieldLinks' => array (
                    'personId' => 'personId',
                    'photoId' => 'photoId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
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
            'personAlbums' => array (
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
                'listDestObjectsMethod' => 'listPersonAlbums',
                'countDestObjectsMethod' => 'countPersonAlbums',
                'getDestObjectMethod' => 'getPersonAlbum',
                'addDestObjectMethod' => 'addPersonAlbum',
                'isDestLoadedMethod' => 'isPersonAlbumsLoaded',
                'loadDestIdsMapperMethod' => 'loadPersonAlbumIdsFor',
                'getDestIdsMethod' => 'getPersonAlbumIds',
                'setDestIdsMethod' => 'setPersonAlbumIds',
                'clearDestObjectsMethod' => 'clearPersonAlbums',
            ),
            'portraitPerson' => array (
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
            ),
            'personPosts' => array (
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
                'listDestObjectsMethod' => 'listPersonPosts',
                'countDestObjectsMethod' => 'countPersonPosts',
                'getDestObjectMethod' => 'getPersonPost',
                'addDestObjectMethod' => 'addPersonPost',
                'isDestLoadedMethod' => 'isPersonPostsLoaded',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Person photo',
                'pluralCaption' => 'Person photos',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
    return array (
            'PRIMARY' => array (
                0 => 'photoId',
            ),
        );
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

