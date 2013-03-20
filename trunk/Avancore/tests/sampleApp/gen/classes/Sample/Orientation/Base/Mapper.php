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
    
    /**
     * @return Sample_Orientation 
     */ 
    function factory ($className = false) {
        $res = parent::factory($className);
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
                
    function _getRelationPrototypes() {
        return Ac_Util::m(parent::_getRelationPrototypes(), array (
              '_people' => array (
                  'srcMapperClass' => 'Sample_Orientation_Mapper',
                  'destMapperClass' => 'Sample_People_Mapper',
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
        
    function _doGetInfoParams() {
        return array (
              'singleCaption' => 'Orientation',
              'pluralCaption' => 'Orientation',
              'hasUi' => false,
        );
    }
    
        
    function _doGetUniqueIndexData() {
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
        $recs = $this->loadRecordsByCriteria(''.$this->database->NameQuote('sexualOrientationId').' = '.$this->database->Quote($sexualOrientationId).'');
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
     * @param Sample_People|array $people of Sample_Orientation objects
     
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

