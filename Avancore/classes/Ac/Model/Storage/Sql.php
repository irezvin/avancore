<?php

abstract class Ac_Model_Storage_Sql extends Ac_Model_Storage {

    /**
     * Loads first record matching criteria
     * 
     * @see Ac_Model_Storage_Sql::loadRecordsByCriteria
     * @return Ac_Model_Object Or NULL if record not found
     */
    abstract function loadFirstRecord($where = '', $order = '', $joins = '', 
        $limitOffset = false, $tableAlias = false);
    
    /**
     * Returns the record if only one record is in the result set, 
     * otherwise returns NULL (if zero or more than one records found)
     * 
     * @see Ac_Model_Storage_Sql::loadRecordsByCriteria
     * @return Ac_Model_Object Or NULL zero or more than one records found
     */
    abstract function loadSingleRecord($where = '', $order = '', $joins = '', 
        $limitOffset = false, $limitCount = false, $tableAlias = false);
    
    
    /**
     * Returns the record if only one record is in the result set, 
     * otherwise returns NULL (if zero or more than one records found).
     * 
     * @see Ac_Model_Storage_Sql::loadRecordsByCriteria
     * @return Ac_Model_Object[] Keys always match record identifiers
     */
    abstract function loadRecordsByCriteria($where = '', $order = '', $joins = '', 
        $limitOffset = false, $limitCount = false, $tableAlias = false);

    /**
     * @var Ac_Sql_Db
     */
    protected $db = false;

    function setDb(Ac_Sql_Db $db) {
        $this->db = $db;
    }

    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        return $this->db;
    }    
    
    function setMapper(Ac_Model_Mapper $mapper = null) {
        if (parent::setMapper($mapper)) {
            if (!$this->application && $mapper) $this->setApplication($mapper->getApplication());
            return true;
        }
    }
    
    function setApplication(Ac_Application $application) {
        parent::setApplication($application);
        if (!$this->db && $application) $this->setDb($application->getDb());
    }
    
}