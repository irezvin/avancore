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
 
    
    protected $autoincFieldName = 'photoId';
    
    function listSqlColumns() {
        return $this->columnNames;
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
                'destVarName' => '_personPhotos',
                'destCountVarName' => '_personPhotosCount',
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
            '_protraitPerson' => array (
                'srcMapperClass' => 'Sample_Person_Photo_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_protraitPerson',
                'destVarName' => '_protraitPersonPhoto',
                'fieldLinks' => array (
                    'personId' => 'personId',
                    'photoId' => 'portraitId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => true,
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
     * @param Sample_Person_Photo|array $protraitPeople     
     * @return Sample_Person_Photo|array of Sample_Person_Photo objects  
     */
    function getOfProtraitPeople($protraitPeople) {
        $rel = $this->getRelation('_protraitPerson');
        $res = $rel->getSrc($protraitPeople); 
        return $res;
    }
    
    /**
     * Loads one or more personPhotos of given one or more people 
     * @param Sample_Person|array $protraitPeople of Sample_Person_Photo objects
     
     */
    function loadForProtraitPeople($protraitPeople) {
        $rel = $this->getRelation('_protraitPerson');
        return $rel->loadSrc($protraitPeople); 
    }

    /**
     * Loads one or more people of given one or more personPhotos 
     * @param Sample_Person_Photo|array $personPhotos     
     */
    function loadProtraitPeopleFor($personPhotos) {
        $rel = $this->getRelation('_protraitPerson');
        return $rel->loadDest($personPhotos); 
    }

    
}

