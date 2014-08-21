<?php

class Sample_Person_Post_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'id'; 

    var $recordClass = 'Sample_Person_Post'; 

    var $tableName = '#__person_posts'; 

    var $id = 'Sample_Person_Post_Mapper'; 

    var $columnNames = array ( 0 => 'id', 1 => 'personId', 2 => 'photoId', 3 => 'title', 4 => 'content', ); 

    var $nullableSqlColumns = array ( 0 => 'personId', 1 => 'photoId', 2 => 'title', 3 => 'content', ); 

    var $defaults = array (
            'id' => NULL,
            'personId' => NULL,
            'photoId' => NULL,
            'title' => '',
            'content' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'id';
    
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    /**
     * @return Sample_Person_Post 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Person_Post_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Person_Post 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Person_Post 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Person_Post 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Person_Post 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Person_Post 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

        
    function getTitleFieldName() {
        return 'title';   
    }
                
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_person' => array (
                'srcMapperClass' => 'Sample_Person_Post_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_person',
                'destVarName' => '_personPosts',
                'destCountVarName' => '_personPostsCount',
                'fieldLinks' => array (
                    'personId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
            '_personPhoto' => array (
                'srcMapperClass' => 'Sample_Person_Post_Mapper',
                'destMapperClass' => 'Sample_Person_Photo_Mapper',
                'srcVarName' => '_personPhoto',
                'destVarName' => '_personPosts',
                'destCountVarName' => '_personPostsCount',
                'fieldLinks' => array (
                    'personId' => 'personId',
                    'photoId' => 'photoId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
        ));
        
    }
        
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Person post',
                'pluralCaption' => 'Person posts',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'id',
            ),
        );
    }
        
    /**
     * @return Sample_Person_Post 
     */
    function loadById ($id) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('id').' = '.$this->getDb()->q($id).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) several personPosts of given one or more people 
     * @param Sample_Person_Post|array $people     
     * @return Sample_Person_Post|array of Sample_Person_Post objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_person');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads several personPosts of given one or more people 
     * @param Sample_Person|array $people of Sample_Person_Post objects
     
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_person');
        return $rel->loadSrc($people); 
    }

    /**
     * Loads several people of given one or more personPosts 
     * @param Sample_Person_Post|array $personPosts     
     */
    function loadPeopleFor($personPosts) {
        $rel = $this->getRelation('_person');
        return $rel->loadDest($personPosts); 
    }


    /**
     * Returns (but not loads!) several personPosts of given one or more personPhotos 
     * @param Sample_Person_Post|array $personPhotos     
     * @return Sample_Person_Post|array of Sample_Person_Post objects  
     */
    function getOfPersonPhotos($personPhotos) {
        $rel = $this->getRelation('_personPhoto');
        $res = $rel->getSrc($personPhotos); 
        return $res;
    }
    
    /**
     * Loads several personPosts of given one or more personPhotos 
     * @param Sample_Person_Photo|array $personPhotos of Sample_Person_Post objects
     
     */
    function loadForPersonPhotos($personPhotos) {
        $rel = $this->getRelation('_personPhoto');
        return $rel->loadSrc($personPhotos); 
    }

    /**
     * Loads several personPhotos of given one or more personPosts 
     * @param Sample_Person_Post|array $personPosts     
     */
    function loadPersonPhotosFor($personPosts) {
        $rel = $this->getRelation('_personPhoto');
        return $rel->loadDest($personPosts); 
    }

    
}

