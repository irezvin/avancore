<?php

class Sample_Publish_Base_ImplMapper extends Ac_Model_Mapper {

    var $pk = 'id'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $tableName = '#__publish'; 

    var $id = 'Sample_Publish_ImplMapper'; 

    var $columnNames = array ( 0 => 'id', 1 => 'sharedObjectType', 2 => 'published', 3 => 'deleted', 4 => 'publishUp', 5 => 'publishDown', 6 => 'authorId', 7 => 'editorId', 8 => 'pubChannelId', 9 => 'dateCreated', 10 => 'dateModified', 11 => 'dateDeleted', ); 

    var $nullableSqlColumns = array ( 0 => 'published', 1 => 'deleted', 2 => 'publishUp', 3 => 'publishDown', 4 => 'authorId', 5 => 'editorId', 6 => 'pubChannelId', ); 

    var $defaults = array (
            'id' => NULL,
            'sharedObjectType' => NULL,
            'published' => '1',
            'deleted' => '0',
            'publishUp' => '0000-00-00 00:00:00',
            'publishDown' => '0000-00-00 00:00:00',
            'authorId' => NULL,
            'editorId' => NULL,
            'pubChannelId' => NULL,
            'dateCreated' => '0000-00-00 00:00:00',
            'dateModified' => '0000-00-00 00:00:00',
            'dateDeleted' => '0000-00-00 00:00:00',
        ); 
 
   
    protected $autoincFieldName = 'id';
    protected $askRelationsForDefaults = false;
 
 
    function listSqlColumns() {
        return $this->columnNames;
    }
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), array (
            '_authorPerson' => false,
            '_editorPerson' => false,
        ));
    }
    
    /**
     * @return Sample_Publish 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Publish_ImplMapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Publish 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Publish 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Publish 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Publish 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Publish 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_authorPerson' => array (
                'srcMapperClass' => 'Sample_Publish_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_authorPerson',
                'fieldLinks' => array (
                    'authorId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
            '_editorPerson' => array (
                'srcMapperClass' => 'Sample_Publish_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_editorPerson',
                'fieldLinks' => array (
                    'editorId' => 'personId',
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
                'singleCaption' => 'Publish',
                'pluralCaption' => 'Publish',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
    return array (
            'PRIMARY' => array (
                0 => 'id',
            ),
            'idxPubChannelId' => array (
                0 => 'pubChannelId',
            ),
        );
    }

    /**
     * @return Sample_Publish 
     */
    function loadById ($id) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('id').' = '.$this->getDb()->q($id).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Publish 
     */
    function loadByPubChannelId ($pubChannelId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('pubChannelId').' = '.$this->getDb()->q($pubChannelId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
}

