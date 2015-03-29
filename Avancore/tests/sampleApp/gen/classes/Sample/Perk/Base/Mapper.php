<?php

class Sample_Perk_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'perkId'; 

    var $recordClass = 'Sample_Perk'; 

    var $tableName = '#__perks'; 

    var $id = 'Sample_Perk_Mapper'; 

    var $columnNames = array ( 0 => 'perkId', 1 => 'name', ); 

    var $nullableSqlColumns = array ( 0 => 'name', ); 

    var $defaults = array (
            'perkId' => NULL,
            'name' => '',
        ); 
 
    
    protected $autoincFieldName = 'perkId';
    
    protected $askRelationsForDefaults = false;
    
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
            '_tags' => false,
            '_tagsCount' => false,
            '_tagsLoaded' => false,
            '_tagIds' => false,
        );
    }
    
    /**
     * @return Sample_Perk 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Perk_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Perk 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Perk 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Perk 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Perk 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Perk 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

                
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_tags' => array (
                'srcMapperClass' => 'Sample_Perk_Mapper',
                'destMapperClass' => 'Sample_Tag_Mapper',
                'srcVarName' => '_tags',
                'srcNNIdsVarName' => '_tagIds',
                'srcCountVarName' => '_tagsCount',
                'srcLoadedVarName' => '_tagsLoaded',
                'destVarName' => '_perks',
                'destCountVarName' => '_perksCount',
                'destLoadedVarName' => '_perksLoaded',
                'destNNIdsVarName' => '_perkIds',
                'fieldLinks' => array (
                    'perkId' => 'idOfPerk',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'midTableName' => '#__tag_perks',
                'fieldLinks2' => array (
                    'idOfTag' => 'tagId',
                ),
            ),
        ));
        
    }
            
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), array (
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
            ),
        ));
        
    }
        
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Perk',
                'pluralCaption' => 'Perks',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'perkId',
            ),
        );
    }
        
    /**
     * @return Sample_Perk 
     */
    function loadByPerkId ($perkId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('perkId').' = '.$this->getDb()->q($perkId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) one or more perks of given one or more tags 
     * @param Sample_Perk|array $tags     
     * @return Sample_Perk|array of Sample_Perk objects  
     */
    function getOfTags($tags) {
        $rel = $this->getRelation('_tags');
        $res = $rel->getSrc($tags); 
        return $res;
    }
    
    /**
     * Loads one or more perks of given one or more tags 
     * @param Sample_Tag|array $tags of Sample_Perk objects
     
     */
    function loadForTags($tags) {
        $rel = $this->getRelation('_tags');
        return $rel->loadSrc($tags); 
    }

    /**
     * Loads one or more tags of given one or more perks 
     * @param Sample_Perk|array $perks     
     */
    function loadTagsFor($perks) {
        $rel = $this->getRelation('_tags');
        return $rel->loadDest($perks); 
    }


    /**
     * @param Sample_Perk|array $perks 
     */
     function loadTagIdsFor($perks) {
        $rel = $this->getRelation('_tags');
        return $rel->loadDestNNIds($perks); 
    }

    
}

