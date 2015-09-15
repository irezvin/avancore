<?php

/**
 * Storage *does not* checks for presence in records collection and also
 * provides less convenient interface than Mapper
 */
abstract class Ac_Model_Storage extends Ac_Prototyped implements Ac_I_Search_RecordProvider {
    
    /**
     * @var Ac_Application
     */
    protected $Application = false;
    
    /**
     * Mapper that owns the Storage
     * @var Ac_Model_Mapper
     */
    protected $mapper = null;
    
    protected $prototypes = array();
    
    protected $dateFormats = array();

    /**
     * Sets Mapper that owns the Storage
     */
    function setMapper(Ac_Model_Mapper $mapper = null) {
        if ($mapper !== ($oldMapper = $this->mapper)) {
            $this->mapper = $mapper;
            return true;
        }
    }

    /**
     * Returns Mapper that owns the Storage
     * @return Ac_Model_Mapper
     */
    function getMapper() {
        return $this->mapper;
    }

    function setApplication(Ac_Application $Application) {
        $this->Application = $Application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->Application;
    }    
    
    /**
     * @return Ac_Model_Object
     */
    abstract function createRecord($typeId = false);
    
    /**
     * Returns array of keys in the storage
     * @return string[]
     */
    abstract function listRecords();
    
    /**
     * @param mixed id string or array; returns count of records that exist in the database
     * @return int
     */
    abstract function recordExists($idOrIds);
    
    
    /**
     * Loads single record
     * @param string $id Identifier of the record
     * @return Ac_Model_Object
     */
    abstract function loadRecord($id);
    
    /**
     * Returns array of records with given identifiers 
     * @return Ac_Model_Object[] array ($id => $record)
     */
    abstract function loadRecordsArray(array $ids);
    
    /**
     * Returns identifier of the object
     */
    abstract function getIdentifier($object);
    
    /**
     * @param array $rows Array of storage-specific rows
     * @return array prepared rows
     */
    function prepareRowsForLoading(array $rows) {
        return $rows;
    }
    
    function getTypeId($preparedRow) {
        return false;
    }
    
    /**
     * @param $rows Storage-specific data for single object
     * @return Ac_Model_Object
     */
    function loadFromRow($preparedRow) {
        $typeId = $this->getTypeId($preparedRow);
        $record = $this->createRecord($typeId);
        $record->load($preparedRow, true);
        return $record;
    }
    
    function loadFromRows(array $rows, $areByIds = false) {
        if ($this->mapper) {
            $res = $this->mapper->loadFromRows($rows, true);
        } else {
            if (!$areByIds) {
                list($idMap, $rowsByIds) = $this->groupRowsByIdentifiers($rows);
                $rows = $rowsByIds;
                unset($idMap);
            }
            $rows = $this->prepareRowsForLoading($rows);
            $res = array();
            foreach ($rows as $k => $row) {
                $res[$k] = $this->loadFromRow($row);
            }
        }
        return $res;
    }
    
    /**
     * Extracts record identifiers from storage-specific items and returns 
     * information on identifier-row pairs. Very specific method to be used by
     * mapper. 
     * 
     * Returns array with two arrays:
     *      array($keyOfRowInRowsArray => $identifierOfRecord)
     *      array($identifierOfRecord => $firstOccuranceOfRowWithThatIdentifier)
     * 
     * @param array $rows Storage-specific items that can be used to load objects
     * @return array array(array(origRowId => identifier), array(identifier => firstOccuredRow))
     */
    abstract function groupRowsByIdentifiers(array $rows);
    
    abstract function peConvertForLoad($object, $hyData);
    
    abstract function peConvertForSave($object, $hyData);
    
    abstract function peLoad($object, $identifier, & $error = null);
    
    abstract function peSave($object, & $hyData, & $exists = null, & $error = null, & $newData = array());

    abstract function peReplaceNNRecords($object, $rowProto, $rows, $midTableName, & $errors = array());
    
    abstract function listGeneratedFields();
    
    /**
     * @param type $hyData 
     * @return bool
     */
    abstract function peDelete($object, $hyData, & $error = null);
    
    /**
     * Locates records in the storage using (supposedly unique) indices
     * 
     * @see Ac_Model_Mapper::findByIndexesInArray
     * 
     * @param object $object Object to provide values of the fields
     * @param array $indices in the format array(idxId => array(field1, field2...))
     * @param bool $ignoreIndicesWithNullValues Don't compare indices that have NULL values in $object (db-like behaviour)
     * @return array (idxId1 => array(id1, id2...), idxId2 => array(id1, id2)) Per-index lists of identifiers
     */
    abstract function checkRecordPresence($object, $indices = array(), $ignoreIndicesWithNullValues = true);
    
    function getPrototype($typeId = false, $again = false) {
        if (!isset($this->prototypes[$typeId]) || $again) {
            $this->prototypes[$typeId] = $this->createRecord($typeId);
        }
        $res = $this->prototypes[$typeId];
        return $res;
    }
    
    /**
	 * @return Parameter $columnFormats for Ac_Legacy_Database::convertDates
     */
    function getDateFormats($dataTypes = false) {
        if (!isset($this->dateFormats[$dataTypes])) {
            $this->dateFormats = array();
            $p = $this->getPrototype();
            $this->dateFormats[$dataTypes] = array();
            foreach ($p->listFields(true) as $f) {
                $pi = $p->getPropertyInfo($f, true);
                if (!$dataTypes) {
                    static $a = array('date', 'time', 'dateTime');
                    if (in_array($pi->dataType, $a)) $this->dateFormats[$dataTypes][$f] = $pi->dataType;
                } else {
                    if (strlen($pi->internalDateFormat)) {
                        $this->dateFormats[$dataTypes][$f] = $pi->internalDateFormat;
                    }
                }
            }
        }
        return $this->dateFormats[$dataTypes];
    }
    
    abstract function find(array $query = array(), $keysToList = false, $sort = false, $limit = false, $offset = false, & $remainingQuery = array(), & $sorted = false);
    
    function __toString() {
        $res = get_class($this);
        if ($this->mapper) $res .= " of ".$this->mapper->getId ();
        return $res;
    }
    
}