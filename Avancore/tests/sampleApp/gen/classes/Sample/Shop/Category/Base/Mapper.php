<?php

class Sample_Shop_Category_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'id'; 

    var $recordClass = 'Sample_Shop_Category'; 

    var $tableName = '#__shop_categories'; 

    var $id = 'Sample_Shop_Category_Mapper'; 

    var $columnNames = array ( 0 => 'id', 1 => 'title', 2 => 'leftCol', 3 => 'rightCol', 4 => 'ignore', 5 => 'parentId', 6 => 'ordering', 7 => 'depth', 8 => 'metaId', 9 => 'pubId', ); 

    var $nullableSqlColumns = array ( 0 => 'title', 1 => 'parentId', 2 => 'metaId', 3 => 'pubId', ); 

    var $defaults = array (
            'id' => NULL,
            'title' => NULL,
            'leftCol' => NULL,
            'rightCol' => NULL,
            'ignore' => NULL,
            'parentId' => NULL,
            'ordering' => NULL,
            'depth' => NULL,
            'metaId' => NULL,
            'pubId' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'id';
    
    protected $askRelationsForDefaults = false;
    
    protected function doGetCoreMixables() { 
        return Ac_Util::m(parent::doGetCoreMixables(), array (
            'Sample_Publish_MapperMixable' => array (
                'class' => 'Sample_Publish_MapperMixable',
                'colMap' => array (
                    'id' => 'pubId',
                ),
            ),
        ));
    }
    
 
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
        );
    }
    
    /**
     * @return Sample_Shop_Category 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Shop_Category_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Category 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Category 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Shop_Category 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Shop_Category 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Shop_Category 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

        
    function getTitleFieldName() {
        return 'title';   
    }
            
    function getDefaultOrdering() {
        return 'ordering';
    }
                
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Shop category',
                'pluralCaption' => 'Shop categories',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'id',
            ),
            'idxPubId' => array (
                0 => 'pubId',
            ),
        );
    }
        
    /**
     * @return Sample_Shop_Category 
     */
    function loadById ($id) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('id').' = '.$this->getDb()->q($id).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Shop_Category 
     */
    function loadByPubId ($pubId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('pubId').' = '.$this->getDb()->q($pubId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
        
}

