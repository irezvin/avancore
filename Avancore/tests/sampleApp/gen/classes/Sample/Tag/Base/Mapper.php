<?php

class Sample_Tag_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'tagId'; 

    var $recordClass = 'Sample_Tag'; 

    var $tableName = '#__tags'; 

    var $id = 'Sample_Tag_Mapper'; 

    var $columnNames = array ( 0 => 'tagId', 1 => 'title', 2 => 'titleM', 3 => 'titleF', ); 

    var $nullableSqlColumns = array ( 0 => 'titleM', 1 => 'titleF', ); 

    var $defaults = array (
            'tagId' => NULL,
            'title' => NULL,
            'titleM' => NULL,
            'titleF' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'tagId';
    
    protected $askRelationsForDefaults = false;
    
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
            '_people' => false,
            '_peopleCount' => false,
            '_peopleLoaded' => false,
            '_personIds' => false,
        );
    }
    
    /**
     * @return Sample_Tag 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Tag_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Tag 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Tag 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Tag 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Tag 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Tag 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

        
    function getTitleFieldName() {
        return 'title';   
    }
                
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_people' => array (
                'srcMapperClass' => 'Sample_Tag_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_people',
                'srcNNIdsVarName' => '_personIds',
                'srcCountVarName' => '_peopleCount',
                'srcLoadedVarName' => '_peopleLoaded',
                'destVarName' => '_tags',
                'destCountVarName' => '_tagsCount',
                'destLoadedVarName' => '_tagsLoaded',
                'destNNIdsVarName' => '_tagIds',
                'fieldLinks' => array (
                    'tagId' => 'idOfTag',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'midTableName' => '#__people_tags',
                'fieldLinks2' => array (
                    'idOfPerson' => 'personId',
                ),
            ),
        ));
        
    }
        
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Tag',
                'pluralCaption' => 'Tags',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'tagId',
            ),
            'Index_2' => array (
                0 => 'title',
            ),
        );
    }
        
    /**
     * @return Sample_Tag 
     */
    function loadByTagId ($tagId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('tagId').' = '.$this->getDb()->q($tagId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Tag 
     */
    function loadByTitle ($title) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('title').' = '.$this->getDb()->q($title).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) one or more tags of given one or more people 
     * @param Sample_Tag|array $people     
     * @return Sample_Tag|array of Sample_Tag objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_people');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads one or more tags of given one or more people 
     * @param Sample_Person|array $people of Sample_Tag objects
     
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_people');
        return $rel->loadSrc($people); 
    }

    /**
     * Loads one or more people of given one or more tags 
     * @param Sample_Tag|array $tags     
     */
    function loadPeopleFor($tags) {
        $rel = $this->getRelation('_people');
        return $rel->loadDest($tags); 
    }


    /**
     * @param Sample_Tag|array $tags 
     */
     function loadPersonIdsFor($tags) {
        $rel = $this->getRelation('_people');
        return $rel->loadDestNNIds($tags); 
    }

    
}

