<?php

/**
 * Storage *does not* checks for presence in records collection and also
 * provides less convenient interface than Mapper
 */
abstract class Ac_Model_Storage extends Ac_Prototyped implements Ac_I_Search_RecordProvider {
    
    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    /**
     * Mapper that owns the Storage
     * @var Ac_Model_Mapper
     */
    protected $mapper = null;
    
    protected $prototypes = array();
    
    protected $dateFormats = array();

    /**
     * primary key of the table
     */
    protected $primaryKey = false;
    
    /**
     * @var array
     */
    protected $columns = false;
    
    /**
     * @var array
     */
    protected $uniqueIndices = false;

    /**
     * @var array
     */
    protected $nullableColumns = false;
    
    /**
     * @var array
     */
    protected $defaults = false;

    /**
     * @var array
     */
    protected $relationProviders = false;
    
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

    function setApplication(Ac_Application $application) {
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
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
    
    /**
     * Should return array with record titles (value => title) if it is possible to fetch them more efficiently than
     * by loading all matching records.
     */
    function fetchTitlesIfPossible($titleProperty, $valueProperty, $sort, array $query = array()) {
        return false; // not possible
    }
    
    function countIfPossible(array $query = array()) {
        return false; // to abstract to be possible
    }
    
    function countWithValuesIfPossible($fieldName, $fieldValues, $groupByValues = Ac_Model_Mapper::GROUP_NONE) {
        if (!in_array($groupByValues, array(Ac_Model_Mapper::GROUP_NONE, Ac_Model_Mapper::GROUP_KEYS, Ac_Model_Mapper::GROUP_ORDER))) {
            Ac_Util::getClassConstants('Ac_Model_Mapper', 'GROUP_');
            throw Ac_E_InvalidCall::outOfConst('groupByValues', $groupByValues, $allowed, 'Ac_Model_Mapper');
        }
        return $this->implCountWithValuesIfPossible($fieldName, $fieldValues, $groupByValues);
    }
    
    protected function implCountWithValuesIfPossible($fieldName, $fieldValues, $groupByValues) {
        if ($groupByValues == Ac_Model_Mapper::GROUP_NONE) {
            $res = $this->countIfPossible(array($fieldName => $fieldValues));
        } else {
            $res = false;
        }
        return $res;
    }
    
    function __toString() {
        $res = get_class($this);
        if ($this->mapper) $res .= " of ".$this->mapper->getId();
        return $res;
    }
    
    abstract function find(array $query = array(), $keysToList = false, $sort = false, $limit = false, $offset = false, & $remainingQuery = array(), & $sorted = false);
    
    protected function inspect() {
        if ($this->primaryKey === false) $this->primaryKey = null;
        if ($this->columns === false) $this->columns = array();
        if ($this->uniqueIndices === false) $this->uniqueIndices = array();
        if ($this->nullableColumns === false) $this->nullableColumns = array();
        if ($this->defaults === false) $this->defaults = array();
    }

    /**
     * @param string $primaryKey
     */
    function setPrimaryKey($primaryKey) {
        // TODO: add immutability support to this and other inspection-related methods
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return string
     */
    function getPrimaryKey() {
        if ($this->primaryKey === false) {
            $this->inspect();
        }
        return $this->primaryKey;
    }

    function setColumns(array $columns) {
        $this->columns = $columns;
    }

    /**
     * @return array
     */
    function getColumns() {
        if ($this->columns === false) {
            $this->inspect();
        }
        return $this->columns;
    }

    function setUniqueIndices(array $uniqueIndices) {
        $this->uniqueIndices = $uniqueIndices;
    }

    /**
     * @return array
     */
    function getUniqueIndices() {
        if ($this->uniqueIndices === false) {
            $this->inspect();
        }
        return $this->uniqueIndices;
    }

    function setNullableColumns(array $nullableColumns) {
        $this->nullableColumns = $nullableColumns;
    }

    /**
     * @return array
     */
    function getNullableColumns() {
        if ($this->nullableColumns === false) {
            $this->inspect();
        }
        return $this->nullableColumns;
    }

    function setDefaults(array $defaults) {
        $this->defaults = $defaults;
    }

    /**
     * @return array
     */
    function getDefaults() {
        if ($this->defaults === false) {
            $this->inspect();
        }
        return $this->defaults;
    }

    protected function doOnGetRelationProviderPrototypes(array & $prototypes = array()) {
    }
    
    /**
     * @return array
     */
    function listRelationProviders() {
        if (!is_array($this->relationProviders)) {
            $this->relationProviders = array();
            $this->doOnGetRelationProviderPrototypes($this->relationProviders);
        }
        return array_keys($this->relationProviders);
    }
    
    function addRelationProvider($id, $relationProvider, $replace = false) {
        $this->listRelationProviders();
        if (isset($this->relationProviders[$id]) && !$replace) {
            throw Ac_E_InvalidCall::alreadySuchItem('relationProvider', $id);
        }
        if (is_object($relationProvider) && !$relationProvider instanceof Ac_Model_Relation_Provider) {
            throw Ac_E_InvalidCall::wrongType('relationProvider', $relationProvider, array('string', 'array', 'Ac_Model_Relation_Provider'));
        }
        $this->relationProviders[$id] = $relationProvider;
    }
    
    function deleteRelationProvider($relationId, $throwIfNotFound = false) {
        $res = false;
        if (in_array($relationId, $this->listRelationProviders())) {
            unset($this->relationProviders[$relationId]);
            $res = true;
        } elseif ($throwIfNotFound) {
            throw Ac_E_InvalidCall::noSuchItem('relationProvider', $relationId);
        }
        return $res;
    }
    
    /**
     * @param string $relationId Identifier of incoming relation
     * @param bool $dontThrow Don't throw an exception if no such provider found
     * @return Ac_Model_Relation_Provider
     */
    function getRelationProviderByRelationId($relationId, $dontThrow = false) {
        $res = null;
        if (in_array($relationId, $this->listRelationProviders())) {
            if (!is_object($this->relationProviders[$relationId])) {
                $p = $this->relationProviders[$relationId] = Ac_Prototyped::factory($this->relationProviders[$relationId], 
                    'Ac_Model_Relation_Provider');
                if ($p instanceof Ac_I_WithMapper && !$p->getMapper() && $this->mapper) {
                    $p->setMapper($this->mapper);
                }
            }
            $res = $this->relationProviders[$relationId];
        } elseif (!$dontThrow) {
            throw Ac_E_InvalidCall::noSuchItem('relationProvider', $relationId);
        }
        return $res;
    }
    
    
}