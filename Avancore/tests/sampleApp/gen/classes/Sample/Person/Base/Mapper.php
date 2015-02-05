<?php

class Sample_Person_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'personId'; 

    var $recordClass = 'Sample_Person'; 

    var $tableName = '#__people'; 

    var $id = 'Sample_Person_Mapper'; 

    var $columnNames = array ( 0 => 'personId', 1 => 'name', 2 => 'gender', 3 => 'isSingle', 4 => 'birthDate', 5 => 'lastUpdatedDatetime', 6 => 'createdTs', 7 => 'religionId', 8 => 'portraitId', ); 

    var $nullableSqlColumns = array ( 0 => 'lastUpdatedDatetime', 1 => 'religionId', 2 => 'portraitId', ); 

    var $defaults = array (
            'personId' => NULL,
            'name' => NULL,
            'gender' => 'F',
            'isSingle' => '1',
            'birthDate' => NULL,
            'lastUpdatedDatetime' => NULL,
            'createdTs' => 'CURRENT_TIMESTAMP',
            'religionId' => NULL,
            'portraitId' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'personId';
    
    protected $askRelationsForDefaults = false;
    
 
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
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
            '_incomingRelations' => false,
            '_incomingRelationsCount' => false,
            '_incomingRelationsLoaded' => false,
            '_outgoingRelations' => false,
            '_outgoingRelationsCount' => false,
            '_outgoingRelationsLoaded' => false,
            '_shopProducts' => false,
            '_shopProductsCount' => false,
            '_shopProductsLoaded' => false,
        );
    }
    
    /**
     * @return Sample_Person 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Person_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Person 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Person 
     */ 
    function reference ($values = array()) {
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

                
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_portraitPersonPhoto' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Person_Photo_Mapper',
                'srcVarName' => '_portraitPersonPhoto',
                'destVarName' => '_portraitPerson',
                'fieldLinks' => array (
                    'personId' => 'personId',
                    'portraitId' => 'photoId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
            '_religion' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Religion_Mapper',
                'srcVarName' => '_religion',
                'destVarName' => '_people',
                'destCountVarName' => '_peopleCount',
                'destLoadedVarName' => '_peopleLoaded',
                'fieldLinks' => array (
                    'religionId' => 'religionId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
            '_tags' => array (
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
                'fieldLinks' => array (
                    'personId' => 'idOfPerson',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'midTableName' => '#__people_tags',
                'fieldLinks2' => array (
                    'idOfTag' => 'tagId',
                ),
            ),
            '_personAlbums' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Person_Album_Mapper',
                'srcVarName' => '_personAlbums',
                'srcCountVarName' => '_personAlbumsCount',
                'srcLoadedVarName' => '_personAlbumsLoaded',
                'destVarName' => '_person',
                'fieldLinks' => array (
                    'personId' => 'personId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ),
            '_personPhotos' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Person_Photo_Mapper',
                'srcVarName' => '_personPhotos',
                'srcCountVarName' => '_personPhotosCount',
                'srcLoadedVarName' => '_personPhotosLoaded',
                'destVarName' => '_person',
                'fieldLinks' => array (
                    'personId' => 'personId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ),
            '_personPosts' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Person_Post_Mapper',
                'srcVarName' => '_personPosts',
                'srcCountVarName' => '_personPostsCount',
                'srcLoadedVarName' => '_personPostsLoaded',
                'destVarName' => '_person',
                'fieldLinks' => array (
                    'personId' => 'personId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ),
            '_incomingRelations' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Relation_Mapper',
                'srcVarName' => '_incomingRelations',
                'srcCountVarName' => '_incomingRelationsCount',
                'srcLoadedVarName' => '_incomingRelationsLoaded',
                'destVarName' => '_otherPerson',
                'fieldLinks' => array (
                    'personId' => 'otherPersonId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ),
            '_outgoingRelations' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Relation_Mapper',
                'srcVarName' => '_outgoingRelations',
                'srcCountVarName' => '_outgoingRelationsCount',
                'srcLoadedVarName' => '_outgoingRelationsLoaded',
                'destVarName' => '_person',
                'fieldLinks' => array (
                    'personId' => 'personId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ),
            '_shopProducts' => array (
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => 'Sample_Shop_Product_Mapper',
                'srcVarName' => '_shopProducts',
                'srcCountVarName' => '_shopProductsCount',
                'srcLoadedVarName' => '_shopProductsLoaded',
                'fieldLinks' => array (
                    'personId' => 'responsiblePersonId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ),
        ));
        
    }
            
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), array (
            'portraitPersonPhoto' => array (
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
            ),
            'religion' => array (
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
            ),
            'tags' => array (
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
                'listDestObjectsMethod' => 'listTags',
                'countDestObjectsMethod' => 'countTags',
                'getDestObjectMethod' => 'getTag',
                'addDestObjectMethod' => 'addTag',
                'isDestLoadedMethod' => 'isTagsLoaded',
                'loadDestIdsMapperMethod' => 'loadTagIdsFor',
                'getDestIdsMethod' => 'getTagIds',
                'setDestIdsMethod' => 'setTagIds',
                'clearDestObjectsMethod' => 'clearTags',
            ),
            'personAlbums' => array (
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
                'listDestObjectsMethod' => 'listPersonAlbums',
                'countDestObjectsMethod' => 'countPersonAlbums',
                'getDestObjectMethod' => 'getPersonAlbum',
                'addDestObjectMethod' => 'addPersonAlbum',
                'isDestLoadedMethod' => 'isPersonAlbumsLoaded',
            ),
            'personPhotos' => array (
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
                'listDestObjectsMethod' => 'listPersonPhotos',
                'countDestObjectsMethod' => 'countPersonPhotos',
                'getDestObjectMethod' => 'getPersonPhoto',
                'addDestObjectMethod' => 'addPersonPhoto',
                'isDestLoadedMethod' => 'isPersonPhotosLoaded',
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
            'incomingRelations' => array (
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
                'listDestObjectsMethod' => 'listIncomingRelations',
                'countDestObjectsMethod' => 'countIncomingRelations',
                'getDestObjectMethod' => 'getIncomingRelation',
                'addDestObjectMethod' => 'addIncomingRelation',
                'isDestLoadedMethod' => 'isIncomingRelationsLoaded',
            ),
            'outgoingRelations' => array (
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
                'listDestObjectsMethod' => 'listOutgoingRelations',
                'countDestObjectsMethod' => 'countOutgoingRelations',
                'getDestObjectMethod' => 'getOutgoingRelation',
                'addDestObjectMethod' => 'addOutgoingRelation',
                'isDestLoadedMethod' => 'isOutgoingRelationsLoaded',
            ),
            'shopProducts' => array (
                'relationId' => '_shopProducts',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'shopProduct',
                'plural' => 'shopProducts',
                'canLoadDestObjects' => false,
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => NULL,
                'loadSrcObjectsMapperMethod' => 'loadForShopProducts',
                'getSrcObjectsMapperMethod' => 'getOfShopProducts',
                'createDestObjectMethod' => 'createShopProduct',
                'listDestObjectsMethod' => 'listShopProducts',
                'countDestObjectsMethod' => 'countShopProducts',
                'getDestObjectMethod' => 'getShopProduct',
                'addDestObjectMethod' => 'addShopProduct',
                'isDestLoadedMethod' => 'isShopProductsLoaded',
            ),
        ));
        
    }
        
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'People',
                'pluralCaption' => 'People',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'personId',
            ),
        );
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
     * @param Sample_Person|array $shopProducts     
     * @return array of Sample_Person objects  
     */
    function getOfShopProducts($shopProducts) {
        $rel = $this->getRelation('_shopProducts');
        $res = $rel->getSrc($shopProducts); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more shopProducts 
     * @param Sample_Shop_Product|array $shopProducts of Sample_Person objects      
     */
    function loadForShopProducts($shopProducts) {
        $rel = $this->getRelation('_shopProducts');
        return $rel->loadSrc($shopProducts); 
    }
    

    
}

