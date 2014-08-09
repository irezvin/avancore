<?php

class Sample_Orientation_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'sexualOrientationId'; 

    var $recordClass = 'Sample_Orientation'; 

    var $tableName = '#__orientation'; 

    var $id = 'Sample_Orientation_Mapper'; 

    var $columnNames = array ( 'sexualOrientationId', 'title', ); 

    var $defaults = array (
              'sexualOrientationId' => NULL,
              'title' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'sexualOrientationId';
    
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    /**
     * @return Sample_Orientation 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Orientation_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Orientation 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Orientation 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Orientation 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Orientation 
     */ 
    function loadSingleRecord($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount);
    }

        
    function getTitleFieldName() {
        return 'title';   
    }
                
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
              '_people' => array (
                  'srcMapperClass' => 'Sample_Orientation_Mapper',
                  'destMapperClass' => 'Sample_Person_Mapper',
                  'srcVarName' => '_people',
                  'srcCountVarName' => '_peopleCount',
                  'destVarName' => '_orientation',
                  'fieldLinks' => array (
                      'sexualOrientationId' => 'sexualOrientationId',
                  ),
                  'srcIsUnique' => true,
                  'destIsUnique' => false,
              ),
        ));
        
    }
        
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                  'singleCaption' => 'Orientation',
                  'pluralCaption' => 'Orientation',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
              'PRIMARY' => array (
                  'sexualOrientationId',
              ),
        );
    }
        
    /**
     * @return Sample_Orientation 
     */
    function loadBySexualOrientationId ($sexualOrientationId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('sexualOrientationId').' = '.$this->getDb()->q($sexualOrientationId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) one or more orientation of given one or more people 
     * @param Sample_Orientation|array $people     
     * @return array of Sample_Orientation objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_people');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads one or more orientation of given one or more people 
     * @param Sample_Person|array $people of Sample_Orientation objects
     
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_people');
        return $rel->loadSrc($people); 
    }

    /**
     * Loads one or more people of given one or more orientation 
     * @param Sample_Orientation|array $orientation     
     */
    function loadPeopleFor($orientation) {
        $rel = $this->getRelation('_people');
        return $rel->loadDest($orientation); 
    }

    
}

