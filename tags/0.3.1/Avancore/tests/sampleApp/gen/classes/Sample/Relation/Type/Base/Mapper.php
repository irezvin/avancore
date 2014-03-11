<?php

class Sample_Relation_Type_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'relationTypeId'; 

    var $recordClass = 'Sample_Relation_Type'; 

    var $tableName = '#__relation_types'; 

    var $id = 'Sample_Relation_Type_Mapper'; 

    var $columnNames = array ( 'relationTypeId', 'title', 'isSymmetrical', ); 

    var $defaults = array (
              'relationTypeId' => NULL,
              'title' => NULL,
              'isSymmetrical' => '0',
        ); 
 
    
    protected $autoincFieldName = 'relationTypeId';
    
    /**
     * @return Sample_Relation_Type 
     */ 
    function factory ($className = false) {
        $res = parent::factory($className);
        return $res;
    }
    
    /**
     * @return Sample_Relation_Type 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Relation_Type 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Relation_Type 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Relation_Type 
     */ 
    function loadSingleRecord($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount);
    }

        
    function getTitleFieldName() {
        return 'title';   
    }
                
    protected function getRelationPrototypes() {
        return Ac_Util::m(parent::getRelationPrototypes(), array (
              '_relations' => array (
                  'srcMapperClass' => 'Sample_Relation_Type_Mapper',
                  'destMapperClass' => 'Sample_Relation_Mapper',
                  'srcVarName' => '_relations',
                  'srcCountVarName' => '_relationsCount',
                  'destVarName' => '_relationType',
                  'fieldLinks' => array (
                      'relationTypeId' => 'relationTypeId',
                  ),
                  'srcIsUnique' => true,
                  'destIsUnique' => false,
              ),
        ));
        
    }
        
    protected function doGetInfoParams() {
        return Ac_Util::m(parent::doGetInfoParams(), 
            array (
                  'singleCaption' => 'Relation type',
                  'pluralCaption' => 'Relation types',
                  'hasUi' => false,
            )        );
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
              'PRIMARY' => array (
                  'relationTypeId',
              ),
        );
    }
        
    /**
     * @return Sample_Relation_Type 
     */
    function loadByRelationTypeId ($relationTypeId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('relationTypeId').' = '.$this->getDb()->q($relationTypeId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) one or more relationTypes of given one or more relations 
     * @param Sample_Relation_Type|array $relations     
     * @return array of Sample_Relation_Type objects  
     */
    function getOfRelations($relations) {
        $rel = $this->getRelation('_relations');
        $res = $rel->getSrc($relations); 
        return $res;
    }
    
    /**
     * Loads one or more relationTypes of given one or more relations 
     * @param Sample_Relation|array $relations of Sample_Relation_Type objects
     
     */
    function loadForRelations($relations) {
        $rel = $this->getRelation('_relations');
        return $rel->loadSrc($relations); 
    }

    /**
     * Loads one or more relations of given one or more relationTypes 
     * @param Sample_Relation_Type|array $relationTypes     
     */
    function loadRelationsFor($relationTypes) {
        $rel = $this->getRelation('_relations');
        return $rel->loadDest($relationTypes); 
    }

    
}

