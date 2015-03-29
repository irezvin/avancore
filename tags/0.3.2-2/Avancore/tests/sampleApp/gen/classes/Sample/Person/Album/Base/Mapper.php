<?php

class Sample_Person_Album_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'albumId'; 

    var $recordClass = 'Sample_Person_Album'; 

    var $tableName = '#__person_albums'; 

    var $id = 'Sample_Person_Album_Mapper'; 

    var $columnNames = array ( 0 => 'albumId', 1 => 'personId', 2 => 'albumName', ); 

    var $defaults = array (
            'albumId' => NULL,
            'personId' => '0',
            'albumName' => '\'\'',
        ); 
 
    
    protected $autoincFieldName = 'albumId';
    
    protected $askRelationsForDefaults = false;
    
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
            '_person' => false,
            '_personPhotos' => false,
            '_personPhotosCount' => false,
            '_personPhotosLoaded' => false,
            '_personPhotoIds' => false,
        );
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
                'midTableName' => '#__album_photos',
                'fieldLinks2' => array (
                    'personId' => 'personId',
                    'photoId' => 'photoId',
                ),
            ),
        ));
        
    }
        
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Person album',
                'pluralCaption' => 'Person albums',
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

