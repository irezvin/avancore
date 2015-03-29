<?php

class Sample_Religion_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'religionId'; 

    var $recordClass = 'Sample_Religion'; 

    var $tableName = '#__religion'; 

    var $id = 'Sample_Religion_Mapper'; 

    var $columnNames = array ( 0 => 'religionId', 1 => 'title', ); 

    var $defaults = array (
            'religionId' => NULL,
            'title' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'religionId';
    
    protected $askRelationsForDefaults = false;
    
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
            '_people' => false,
            '_peopleCount' => false,
            '_peopleLoaded' => false,
        );
    }
    
    /**
     * @return Sample_Religion 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Religion_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Religion 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Religion 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Religion 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Religion 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Religion 
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
                'srcMapperClass' => 'Sample_Religion_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_people',
                'srcCountVarName' => '_peopleCount',
                'srcLoadedVarName' => '_peopleLoaded',
                'destVarName' => '_religion',
                'fieldLinks' => array (
                    'religionId' => 'religionId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ),
        ));
        
    }
            
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), array (
            'people' => array (
                'relationId' => '_people',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'person',
                'plural' => 'people',
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => 'loadPeopleFor',
                'loadSrcObjectsMapperMethod' => 'loadForPeople',
                'getSrcObjectsMapperMethod' => 'getOfPeople',
                'createDestObjectMethod' => 'createPerson',
                'listDestObjectsMethod' => 'listPeople',
                'countDestObjectsMethod' => 'countPeople',
                'getDestObjectMethod' => 'getPerson',
                'addDestObjectMethod' => 'addPerson',
                'isDestLoadedMethod' => 'isPeopleLoaded',
            ),
        ));
        
    }
        
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Religion',
                'pluralCaption' => 'Religion',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'religionId',
            ),
            'Index_2' => array (
                0 => 'title',
            ),
        );
    }
        
    /**
     * @return Sample_Religion 
     */
    function loadByReligionId ($religionId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('religionId').' = '.$this->getDb()->q($religionId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Religion 
     */
    function loadByTitle ($title) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('title').' = '.$this->getDb()->q($title).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) one or more religion of given one or more people 
     * @param Sample_Religion|array $people     
     * @return array of Sample_Religion objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_people');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads one or more religion of given one or more people 
     * @param Sample_Person|array $people of Sample_Religion objects
     
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_people');
        return $rel->loadSrc($people); 
    }

    /**
     * Loads one or more people of given one or more religion 
     * @param Sample_Religion|array $religion     
     */
    function loadPeopleFor($religion) {
        $rel = $this->getRelation('_people');
        return $rel->loadDest($religion); 
    }

    
}

