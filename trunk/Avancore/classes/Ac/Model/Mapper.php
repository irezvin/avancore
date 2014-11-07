<?php

class Ac_Model_Mapper extends Ac_Mixin_WithEvents {
    
    /**
     * function onAfterCreateRecord ($record)
     */
    const EVENT_AFTER_CREATE_RECORD = 'onAfterCreateRecord';

    /**
     * Not used at the time!
     *
     * function onAfterHydrateRecord($record)
     */
    const EVENT_AFTER_HYDRATE_RECORD = 'onAfterHydrateRecord';

    /**
     * function onBeforeStoreRecord($record, array & $hyData, & $newData, & $exists, & $result, 
     *                              & $error)
     */
    const EVENT_BEFORE_STORE_RECORD = 'onBeforeStoreRecord';

    /**
     * function onAfterStoreRecord($record, array & $hyData, & $newData, & $exists, & $result, & $error)
     */
    const EVENT_AFTER_STORE_RECORD = 'onAfterStoreRecord';

    /**
     * function onBeforeReplaceNNRecords($record, & $rowProto, & $rows, & $midTableName, & $result, & $errors)
     */
    const EVENT_BEFORE_REPLACE_NN_RECORDS = 'onBeforeReplaceNNRecords';

    /**
     * function onAfterReplaceNNRecords($record, & $rowProto, & $rows, & $midTableName, & $result, & $errors)
     */
    const EVENT_AFTER_REPLACE_NN_RECORDS = 'onAfterReplaceNNRecords';

    /**
     * function onBeforeDeleteRecord($record, array & $hyData, & $error, & $result)
     */
    const EVENT_BEFORE_DELETE_RECORD = 'onBeforeDeleteRecord';

    /**
     * function onAfterDeleteRecord($record, array & $hyData, & $error, & $result)
     */
    const EVENT_AFTER_DELETE_RECORD = 'onAfterDeleteRecord';

    /**
     * function onGetInfoParams(& $infoParams)
     */
    const EVENT_ON_GET_INFO_PARAMS = 'onGetInfoParams';

    /**
     * function onConvertForLoad($record, $hyData, & $result)
     */
    const EVENT_ON_CONVERT_FOR_LOAD = 'onConvertForLoad';

    /**
     * function onConvertForSave($record, $hyData, & $result)
     */
    const EVENT_ON_CONVERT_FOR_SAVE = 'onConvertForSave';

    /**
     * function onGetSelectPrototype(& $selectPrototype, $primaryAlias)
     */
    const EVENT_ON_GET_SELECT_PROTOTYPE = 'onGetSelectPrototype';

    /**
     * function onGetRelationPrototypes(& $relationPrototypes)
     */
    const EVENT_ON_GET_RELATION_PROTOTYPES = 'onGetRelationPrototype';

    /**
     * function onGetManagerConfig(& $managerConfig)
     */
    const EVENT_ON_GET_MANAGER_CONFIG = 'onGetManagerConfig';

    /**
     * function onUpdated()
     */
    const EVENT_ON_UPDATED = 'onUpdated';
    
    /**
     * function onGetRecordClasses(array & $classes, array $rows)
     */
    const EVENT_ON_GET_RECORD_CLASSES = 'onGetRecordClasses';
    
    /**
     * function onBeforeLoadFromRows(array & $rows, array & $records)
     */
    const EVENT_ON_BEFORE_LOAD_FROM_ROWS = 'onBeforeLoadFromRows';
    
    /**
     * function onAfterLoadFromRows(array $rows, array & $records)
     */
    const EVENT_ON_AFTER_LOAD_FROM_ROWS = 'onAfterLoadFromRows';

    /**
     * function onPeLoad(& $data, $primaryKey, Ac_Model_Object $record, & $error)
     */
    const EVENT_ON_PE_LOAD = 'onPeLoad';
    
    /**
     * function onGetDefaults(array & $defaults)
     */
    const EVENT_ON_GET_DEFAULTS = 'onGetDefaults';
    
    /**
     * function onGetInternalDefaults(array & $defaults)
     */
    const EVENT_ON_GET_INTERNAL_DEFAULTS = 'onGetInternalDefaults';
    
    /**
     * function onReset()
     */
    const EVENT_ON_RESET = 'onReset';

    protected $id = false;
    
    /**
     * @var Ac_Application
     */
    protected $application = false;

    protected $autoincFieldName = false;

    /**
     * @var Ac_Sql_Db
     */
    protected $db = false;

    var $tableName = null;

    var $recordClass = 'Ac_Model_Record';

    var $pk = null;
    
    protected $prototype = false;

    /**
     * Use records collection (records that already were loaded will not be loaded again)
     */
    var $useRecordsCollection = false;
    
    var $nullableSqlColumns = array();
    
    /**
     * @var array ('indexName' => array('fieldName1', 'fieldName2'), ...)
     */
    var $indexData = array();

    protected $recordsCollection = array();
    
    /**
     * Records that are not stored (only those that are created with Ac_Model_Mapper::factory() method).
     * Record will be removed from the array after it is stored.
     * This array is used to check uniqueness and find records by indices disregarding whether record is already stored or not.
     * To prevent deadlocks in case of two conflicting newly created records, first one gets right to be stored, second one will be considered invalid.
     *
     * @var array
     */
    protected $newRecords = array();

    /**
     * Relations that were created (used only if $this->remembersRelations())
     * @var array of Ac_Model_Relation
     */
    protected $relations = false;
    
    /**
     * List of 'intrinsic' relations that cannot be deleted
     * @var array
     */
    protected $intrinsicRelations = false;

    /**
     * List of relations that were added
     * @var array
     */
    protected $additionalRelations = array();
    
    var $useProto = false;

    protected $proto = array();

    /**
     * @var Ac_Model_MapperInfo
     */
    protected $info = false;

    protected $titleFieldExpression = false;
    
    protected $validator = false;
    
    protected $updateMark = false;
    
    protected $updateLevel = 0;
    
    protected $dateFormats = array();
    
    var $managerClass = false;    
    
    protected $defaultQualifier = false;
    
    protected $columnNames = false;
    
    protected $defaults = false;
    
    protected $allRecords = false;
    
    protected $fkFieldsData = false;
    
    protected $internalDefaults = false;
    
    protected $askRelationsForDefaults = true;
    
    protected $computedDefaults = false;
    
    function __construct(array $options = array()) {
        // TODO: application & db are initialized last, id & tableName - first
        parent::__construct($options);
        if (!$this->tableName) trigger_error (__FILE__."::".__FUNCTION__." - tableName missing", E_USER_ERROR);
    }
    
    function setId($id) {
        if ($this->id !== false && $this->id !== $id) throw new Exception("Can setId() only once!");
        $this->id = $id;
    }

    function getId() {
        if ($this->id === false) {
            $this->id = get_class($this);
            if ($this->id == 'Ac_Model_Mapper') {
                $this->id .= '_'.$this->tableName;
            }
        }
        return $this->id;
    }    

    function setApplication(Ac_Application $application) {
        $this->application = $application;
        if ($this->relations) {
            foreach ($this->relations as $rel) {
                if (is_object($rel)) $rel->setApplication($application);
            }
        }
        if (!$this->db) {
            $this->setDb($this->application->getDb());
        }
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }

    function setDb(Ac_Sql_Db $db) {
        $this->db = $db;
        if (!strlen($this->pk)) {
            $dbi = $this->db->getInspector();
            $this->getColumnNames();
            $idxs = $dbi->getIndicesForTable($this->db->replacePrefix($this->tableName));
            $this->indexData = array();
            foreach ($idxs as $name => $idx) {
                if (isset($idx['primary']) && $idx['primary']) {
                    if (count($idx['columns']) == 1) {
                        $cVals = array_values($idx['columns']);
                        $this->pk = $cVals[0];
                    } else {
                        $this->pk = $idx['columns'];
                    }
                }
                if (isset($idx['unique']) && $idx['unique'] || isset($idx['primary']) && $idx['primary']) {
                    $this->indexData[$name] = array_values($idx['columns']);
                }
            }
            if (!(is_array($this->pk) && $this->pk || strlen($this->pk))) trigger_error (__FILE__."::".__FUNCTION__." - pk missing", E_USER_ERROR);
        }
    }

    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        return $this->db;
    }

    function hasPublicVars() {
        return true;
    }

    /**
     * @return Ac_Model_Mapper
     */
    static function getMapper ($mapperClass, Ac_Application $application = null) {
        if (is_object($mapperClass) && $mapperClass instanceof Ac_Model_Mapper) {
            $res = $mapperClass;
        } else {
            $res = null;
            if ($application) $res = $application->getMapper($mapperClass);
            foreach(Ac_Application::listInstances() as $className => $ids) {
                foreach ($ids as $appId) {
                    $app = Ac_Application::getApplicationInstance($className, $appId);
                    if ($app->hasMapper($mapperClass)) {
                        $res = $app->getMapper($mapperClass);
                    }
                }
            }
        }
        return $res;
    }
    
    function getColumnNames() {
        if ($this->columnNames === false) {
            $cols = $this->db->getInspector()->getColumnsForTable($this->db->replacePrefix($this->tableName));
            $this->defaults = array();
            foreach ($cols as $name => $col) {
                $this->defaults[$name] = $col['default'];
                if ($col['nullable']) $this->nullableSqlColumns[] = $name;
                if (isset($col['autoInc']) && $col['autoInc'] && ($this->autoincFieldName === false)) {
                    $this->autoincFieldName = $name;
                }
            }
            $this->columnNames = array_keys($this->defaults);
        }
        return $this->columnNames;
    }

    protected function coreCreateRecord($className = false) {
        if ($className === false) $className = $this->recordClass;
        if ($this->useProto) {
            if (!isset($this->proto[$className])) {
                $this->proto[$className] = new $className($this);
            }
            $proto = $this->proto[$className];
            $res = clone $proto;
        } else {
            $res = new $className($this);
        }
        if ($className !== $this->recordClass) {
            if (! $res instanceof $this->recordClass) 
                throw Ac_E_InvalidCall::wrongClass ('className', $className, $this->recordClass);
        }
        return $res;
    }
    
    final function registerRecord(Ac_Model_Object $record) {
        $this->coreRegisterRecord($record);
        $this->triggerEvent(self::EVENT_AFTER_CREATE_RECORD, array(
            $record
        ));
    }
    
    protected function coreRegisterRecord(Ac_Model_Object $record) {
        $this->isMyRecord($record, true);
        $this->memorize($record);
    }
    
    /**
     * Creates new record instance that is bound to $this mapper.
     * 
     * @param string|bool $className Class name of record instance (
     * @throws Ac_E_InvalidCall if $className is NOT a sub-class of $this->recordClass
     * @return Ac_Model_Object
     */
    function createRecord($className = false) {
        $res = $this->coreCreateRecord($className);
        return $res;
    }
    
    /**
     * Deprecated - use Ac_Model_Mapper::createRecord() instead
     * 
     * @deprecated since 0.3.2
     * @param string $className Same as createRecord::$className
     * @return Ac_Model_Object
     */
    static function factory($className = false, 
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        throw new Exception("Cannot use Ac_Model_Mapper::factory() directly; using factory() in descendant "
            . "classes is deprecated too. User createRecord() instead");
    }
    
    /**
     * @param array $values
     * @return Ac_Model_Object
     */
    function reference($values = array()) {
        $res = $this->createRecord();
        $res->setIsReference(true);
        foreach ($values as $k => $v) $res->{$k} = $v;
        return $res;
    }

    function listRecords() {
        if ($this->allRecords) $res = array_keys($this->allRecords);
        else {
            $res = $this->db->fetchColumn("SELECT ".$this->db->n($this->pk)."  FROM ".$this->db->n($this->tableName));
        }
        return $res;
    }

    /**
     * @param mixed id string or array; returns count of records that exist in the database;
     */
    function recordExists($ids) {
        if ($ids) {
            $res = (int) $this->db->fetchValue("SELECT COUNT(".$this->db->n($this->pk).") FROM ".$this->db->n($this->tableName)." WHERE $this->pk ".$this->db->eqCriterion($ids));
        } else $res = false;
        return $res;
    }
    
    /**
     * Loads record(s) -- id can be an array
     * @return Ac_Model_Object
     */
    function loadRecord($id) {
        $res = null;
        if (is_array($id) && count($id) == 1) $id = array_pop($id);
        if (is_array($id)) {
            $res = $this->loadRecordsArray($id);
        } else {
            if ($this->useRecordsCollection && isset($this->recordsCollection[$id])) {
                $res = $this->recordsCollection[$id];
            } else {
                $sql = "SELECT * FROM ".$this->db->n($this->tableName)." WHERE ".$this->db->n($this->pk)." = ".$this->db->q($id)." LIMIT 1";
                $rows = $this->db->fetchArray($sql);
                if (count($rows)) {
                    $objects = $this->loadFromRows($rows);
                    $res = array_pop($objects);
                    if ($this->useRecordsCollection) $this->recordsCollection[$id] = $res;
                } else {
                    $res = null;
                }
            }
        }
        return $res;
    }

    /**
     * @param array ids - Array of record identifiers
     */
    function loadRecordsArray($ids, $keysToList = false) {
        if (!is_bool($keysToList)) throw Ac_E_InvalidCall::wrongType ('$keysToList', $keysToList, 'bool');
        if (!is_array($ids)) trigger_error (__FILE__."::".__FUNCTION__.'$ids must be an array', E_USER_ERROR);
        if ($ids) {
            $where = $this->db->n($this->pk)." ".$this->db->eqCriterion($ids);
            $recs = $this->loadRecordsByCriteria($where, true);
            $res = array();
            /**
             * This helps to maintain records in order that was specified in $ids
             */
            foreach ($ids as $id) {
                if (isset($recs[$id])) {
                    if ($keysToList) $res[$id] = $recs[$id];
                    else $res[] = $recs[$id];
                }
            }

        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * Loads records into rows that reference current table' objects by primary key.
     * Does not work with objects and will overwrite values in $src[$i][$valueProperty].
     * 
     * @param array $src Array of arrays
     * @param string $keyProperty Key in $src elements that contains record key
     * @param string $valueProperty Key in $dest elements that will contain record (defaults to keyProperty)
     * @param mixed $def Value to use if related object isn't found
     * @return array All objects loaded, indexed by their IDs (as in loadRecordsArray())
     */
    function loadObjectsColumn(& $src, $keyProperty, $valueProperty = false, $def = null) {
        $ids = array();
        $objects = array();
        if ($valueProperty === false) $valueProperty = $keyProperty;
        foreach ($src as $v) if (isset($v[$keyProperty])) {
            $ids[] = $v[$keyProperty];
        }
        if ($ids) {
            $objects = $this->loadRecordsArray($ids, true);
        }
        foreach (array_keys($src) as $k) {
            $val = $def;
            if (isset($src[$k][$keyProperty]) && isset($objects[$src[$k][$keyProperty]])) {
                $val = $objects[$src[$k][$keyProperty]];
            }
            $src[$k][$valueProperty] = $val;
        }
        return $objects;
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Ac_Model_Object
     */
    function loadFirstRecord($where = '', $order = '', $joins = '', 
            $limitOffset = false, $tableAlias = false) {
        $arr = $this->loadRecordsByCriteria($where, false, $order, $joins, $limitOffset, 1, $tableAlias);
        if (count($arr)) $res = $arr[0];
            else $res = null;
        return $res;
    }
    
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * 
     * @return Ac_Model_Object
     */
    function loadSingleRecord($where = '', $order = '', $joins = '', 
        $limitOffset = false, $limitCount = false, $tableAlias = false) 
    {
        $arr = $this->loadRecordsByCriteria($where, false, $order, $joins, $limitOffset, $limitCount, $tableAlias);
        if (count($arr) == 1) $res = $arr[0];
            else $res = null;
        return $res;
    }
    
    protected function groupRowsByPks(array $rows) {
        $pkData = array();
        $uniqueRows = array();
        foreach ($rows as $i => $row) {
            $pk = $row[$this->pk];
            $pkData[$i] = $pk;
            if (!isset($uniqueRows[$pk])) {
                $uniqueRows[$pk] = $row;
            }
        }
        return array($pkData, $uniqueRows);
    }
    
    protected function ungroupRowsByPks(array $pkData, array $records) {
        $res = array();
        foreach ($pkData as $id => $pk) {
            $res[$id] = $records[$pk];
        }
        return $res;
    }

    /**
     * @param array $objects Array of associative DB rows
     * @return array
     */
    final function loadFromRows(array $rows, $keysToList = false) {
        $objects = array();
        
        list($pkData, $uniqueRows) = $this->groupRowsByPks($rows);
        
        if ($this->useRecordsCollection) {
            foreach ($uniqueRows as $pk => $row) {
                if (isset($this->recordsCollection[$row[$this->pk]])) {
                    $objects[$pk] = $this->recordsCollection[$row[$this->pk]];
                    unset($uniqueRows[$pk]);
                } else {
                    $objects[$pk] = null;
                }
            }
        }
        
        $this->triggerEvent(self::EVENT_ON_BEFORE_LOAD_FROM_ROWS, array(& $uniqueRows, & $objects));
        $c = $this->getRecordClasses($uniqueRows);
        $loaded = $this->coreLoadFromRows($uniqueRows, $c);
        $this->triggerEvent(self::EVENT_ON_AFTER_LOAD_FROM_ROWS, array($uniqueRows, & $loaded));
        
        foreach ($loaded as $pk => $record) 
            $objects[$pk] = $record;
        
        if ($this->useRecordsCollection) {
            foreach ($loaded as $pk => $record) {
                $this->recordsCollection[$record->getPrimaryKey()] = $record;
            }
        }
        
        $res = $this->ungroupRowsByPks($pkData, $objects);
        
        if ($keysToList) $res = $this->indexObjects($res, $keysToList);
        
        return $res;
    }

    protected function coreLoadFromRows(array $rows, array $recordClasses) {
        $res = array();
        foreach ($rows as $i => $row) {
            if ($this->useRecordsCollection && isset($this->recordsCollection[$row[$this->pk]])) {
                $res[$i] = $this->recordsCollection[$row[$this->pk]];
            } else {
                $class = $recordClasses[$i];
                if (is_object($class) && $class instanceof Ac_Model_Object) {
                    $rec = $class;
                } else {
                    $rec = $this->createRecord($class);
                }
                $rec->load($row, true);
                $res[$i] = $rec;
            }
        }
        return $res;
    }
    
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        $sql = "SELECT ".$this->db->n($this->tableName).".* FROM ".$this->db->n($this->tableName)." $joins  ";
        if ($where) {
            if (is_array($where)) $where = $this->db->valueCriterion($where);
            $sql .= " WHERE ".$where;
        }
        if ($order) $sql .= " ORDER BY ".$order;
        if (is_numeric($limitCount) && !is_numeric($limitOffset)) $limitOffet = false;
        if (is_numeric($limitCount)) {
            $sql = $this->db->applyLimits($sql, $limitCount, $limitOffset, strlen($order)? $order : false);
        }
        $res = $this->loadFromRows($this->db->fetchArray($sql), $keysToList);
        return $res;
    }
    
    /**
     * All-in-one function to hash array of the mapper' records by one ore several keys.
     * 
     * - keysToList($objects, true) returns array(pk => record)
     * - leysToList($objects, false) returns $objects
     * - keysToList($objects, array('field1', 'field2', ...) 
     *   returns array(field1value => array(field2value => recordOrRecords))
     *   where recordOrRecords is SINGLE object when val1, val2 uniquely identify the records
     * - keysToList($objects, array('field1', 'field2', TRUE)) - last level will always be record
     * - keysToList($objects, array('field1', 'field2', FALSE)) - last level will always be array of records
     * 
     * @param array $objects
     * @param true|false|key|array $keysToList
     * @return array sorted by keys (multi-dimensional if there are several keys or single key is not unique)
     */
    function indexObjects(array $objects, $keysToList = true) {
        $res = array();
        if ($keysToList === false) {
            
        } elseif ($keysToList === true || $keysToList === $this->pk 
            || is_array($keysToList) && array_values($keysToList) == array($this->pk)) 
        {
            foreach ($objects as $rec) {
                $res[$rec->getPrimaryKey()] = $rec;
            }
        } else {
            $keys = Ac_Util::toArray($keysToList);
            if (count($keys) > 1) {
                $tmp = $keys;
                $last = array_pop($tmp);
                if ($last === true) $unique = true;
                else if ($last === false) $unique = false;
            }
            if (isset($unique)) {
                $keys = $tmp;
            } else {
                $unique = $this->identifiesRecordBy($keys);
            }
            $res = Ac_Util::indexArray($objects, $keys, $unique);
        }
        return $res;
    }
    
    function getRecordClass($row) {
        $classes = $this->getRecordClasses(array($row));
        return $classes[0];
    }
    
    protected function coreGetRecordClasses(array $rows) {
        $res = array();
        foreach ($rows as $k => $v) $res[$k] = $this->recordClass;
        return $res;
    }
    
    final function getRecordClasses($rows) {
        $res = $this->coreGetRecordClasses($rows);
        $this->triggerEvent(self::EVENT_ON_GET_RECORD_CLASSES, array (& $res, $rows));
        return $res;
    }

    function listModelProperties() {
        $proto = $this->getPrototype();
        return $proto->listPublicProperties();
    }

    /**
     * @return Ac_Model_Object
     */
    function getPrototype() {
        if ($this->prototype === false) {
            $this->prototype = $this->createRecord();
        }
        return $this->prototype;
    }

    // ----------------------------------- Cache support functions --------------------------------

    function getMtime() {
        return $this->application->getFlags()->getMtime('mapper.'.$this->id);
    }
    
    function getLastUpdateTime() {
    	return $this->getMtime();
    }
    
    function markUpdated() {
    	if (!$this->updateLevel) {
            $this->doMarkUpdated();
    		$this->updateMark = false;
    	} else {
    		$this->updateMark = true;
    	}
    }
    
    protected function doMarkUpdated() {
        $this->triggerEvent(self::EVENT_ON_UPDATED);
        $this->application->getFlags()->touch('mapper.'.$this->id);
    }
    
    function beginUpdate() {
    	$this->updateLevel++;
    }
    
    function endUpdate() {
    	if ($this->updateLevel > 0) $this->updateLevel--;
    	if (!$this->updateLevel && $this->updateMark) $this->markUpdated();
    }

    // -------------------------------- Metadata retrieval functions ------------------------------

    /**
     * @return Ac_Model_MapperInfo
     */
    function getInfo() {
        if ($this->info === false) {
            $this->info = new Ac_Model_MapperInfo($this->getId(), $this->getInfoParams());
        }
        return $this->info;
    }
    
    final function getInfoParams() {
        $res = $this->doGetInfoParams();
        $this->triggerEvent(self::EVENT_ON_GET_INFO_PARAMS, array(
            & $res
        ));
        return $res;
    }

    protected function doGetInfoParams() {
        return array();
    }

    // --------------------------- in-memory records registry functions ---------------------------

    function memorize(Ac_Model_Object $record) {
        if ($record->getMapper() !== $this) 
            throw new Ac_E_InvalidUsage("Record '".get_class($record)."' does not belong to mapper '"
                .$this->getId ()."'");
        
        $this->newRecords[$record->_imId] = $record;
    }

    function forget(Ac_Model_Object $record) {
        if ($record->getMapper() !== $this) 
            throw new Ac_E_InvalidUsage("Record '".get_class($record)."' does not belong to mapper '"
                .$this->getId ()."'");
        
        $pk = $record->getPrimaryKey();
        
        if (isset($this->recordsCollection[$pk])) {
            unset($this->recordsCollection[$pk]);
        }
        if (isset($this->newRecords[$record->_imId])) {
            unset($this->newRecords[$record->_imId]);
        }
        
        if (is_array($this->allRecords) && isset($this->allRecords[$pk]) 
            && $this->allRecords[$pk] === $record) {
            $this->allRecords = false;
        }
    }
    
    function notifyKeyAssigned(Ac_Model_Object $record, $oldPk = null) {
        
        if ($oldPk !== null) {
            if (isset($this->recordsCollection[$oldPk])) {
                unset($this->recordsCollection[$oldPk]);
            }
        }
        
        $pk = $record->getPrimaryKey();
        
        if ($record->getMapper() !== $this) 
            throw new Ac_E_InvalidUsage("Record '".get_class($record)."' does not belong to mapper '"
                .$this->getId ()."'");
        
        if ($this->useRecordsCollection && !isset($this->recordsCollection[$pk])) {
            $this->recordsCollection[$pk] = $record;
        }
        
        if (is_array($this->allRecords)) {
            if ($oldPk !== null) {
                unset($this->allRecords[$oldPk]);
            }
            if ($record->hasFullPrimaryKey()) 
                $this->allRecords[isset($pk)? $pk : $record->getPrimaryKey ()] = $record;
        }
        
        if (isset($this->newRecords[$record->_imId])) {
            unset($this->newRecords[$record->_imId]);
        }
    }
    
    function clearCollection() {
        $this->recordsCollection = array();
    }
    
    function register(Ac_Model_Object $record) {
        if ($record->getMapper() !== $this) 
            throw new Ac_E_InvalidUsage("Record '".get_class($record)."' does not belong to mapper '"
                .$this->getId ()."'");
        if ($this->useRecordsCollection && $record->isPersistent()) {
            $this->recordsCollection[$record->getPrimaryKey()] = $record;
        }
    }

    function _find($fields) {
        $res = array();
        foreach (array_keys($this->newRecords) as $k) {
            if ($this->newRecords[$k]->matchesFields($fields)) $res[$k] = $this->newRecords[$k];
        }
        return $res;
    }

    // --------------------- Functions that work with columns, keys and indices -------------------

    /**
     * @param mixed|array $keys One or more keys
     */
    function getKeysCriterion($keys, $tableAlias = false, $default = '0') {
        if (is_array($keys) && !count($keys)) return $default;
        $fieldName = $this->db->n($this->pk);
        if ($tableAlias !== false) $fieldName = $this->db->n($tableAlias).'.'.$fieldName;
        $res = $fieldName.$this->db->eqCriterion($keys);
        return $res;
    }

    function locateRecord($fields, $where = false, $mustBeUnique = false, $searchNewRecords = false) {
        if (strlen($where)) $searchNewRecords = false;
        $crit = $this->indexCrtieria($fields);
        if (strlen($where)) $crit = "($crit) AND ($where)";
        $recs = $this->loadRecordsByCriteria($crit);
        if ($searchNewRecords) {
            $newRecs = $this->find($fields);
            foreach (array_keys($newRecs) as $k) $recs[] = $newRecs[$k];
        }
        $res = null;
        if (count($recs)) {
            if (!$mustBeUnique || count($recs) == 1) $res = $recs[0];
        }
        if (!$res) {
        }
        return $res;
    }

    function getTitleFieldName() {
        return false;
    }

    function isTitleAProperty() {
        return false;
    }

    function getDefaultOrdering() {
        return false;
    }
    
    function setDefaultQualifier($defaultQualifier) {
        if ($defaultQualifier !== ($oldDefaultQualifier = $this->defaultQualifier)) {
            $this->defaultQualifier = $defaultQualifier;
            $this->relations = array();
        }
        $this->defaultQualifier = $defaultQualifier;
    }

    function getDefaultQualifier() {
        if ($this->defaultQualifier === true) {
            return $this->pk;
        }
        return $this->defaultQualifier;
    }
    
    /**
     * @return array (array($pk1, $title1), array($pk2, $title2), ...)
     */
    function getRecordTitles($where = false, $ordering = false, $extraJoins = false, $titleFieldName = false, $titleIsProperty = '?', $valueFieldName = false, $valueIsProperty = false) {
        if ($titleFieldName === false) {
            if (!strlen($this->titleFieldExpression)) $titleFieldName = $this->getTitleFieldName();
            else $titleFieldName = $this->titleFieldExpression;
        }
        if ($titleIsProperty == '?') $titleIsProperty = $this->isTitleAProperty();
        if (!$titleFieldName) {
            $pkf = $this->listPkFields();
            $titleFieldName = $pkf[0];
        }
        $qpkf = array();
        if ($valueFieldName === false)
        foreach ($this->listPkFields() as $pkf) $qpkf[] = $this->db->n('t').'.'.$this->db->n($pkf);
        else {
            $vf = $valueFieldName;
            foreach (Ac_Util::toArray($vf) as $pkf) $qpkf[] = $this->db->n('t').'.'.$this->db->n($pkf);
        }
        $spk = count($qpkf) == 1;
        $qpkf = implode(", ", $qpkf);
        $res = array();
        if (!$titleIsProperty && !$valueIsProperty) {
            $sql = "SELECT DISTINCT t.".$titleFieldName." AS _title_, ".$qpkf." FROM ".$this->db->n($this->tableName)." AS t";
            if ($extraJoins) $sql .= " ".$extraJoins;
            if ($where) $sql .= " WHERE ".$where;
            if ($ordering) $sql .= " ORDER BY ".$ordering;
            foreach ($this->db->fetchArray($sql) as $row) {
                $title = $row['_title_'];
                $pk = Ac_Util::array_values(array_slice($row, 1));
                if ($spk) $pk = $pk[0];
                $res[] = array($pk, $title);
            }
        } else {
            $coll = new Ac_Model_Collection(get_class($this), false, $where, $ordering, $extraJoins);
            $coll->setSequential();
            $coll->useCursor();
            while ($rec = $coll->getNext()) {
                if ($valueFieldName === false) $pk = $rec->getPrimaryKey();
                else {
                    if (is_array($valueFieldName)) {
                        $pk = array();
                        if ($valueIsProperty) foreach ($vf as $f) $pk[] = $rec->getField($f);
                            else foreach ($vf as $f) $pk[] = $rec->$f;
                    } else {
                        $pk = $valueIsProperty? $rec->getField($valueFieldName) : $rec->{$valueFieldName};
                    }
                }
                $title = $rec->getField($titleFieldName);
                $res[] = array($pk, $title);
            }
        }
        return $res;
    }

    function listPkFields() {
        return array($this->pk);
    }

    /**
     * @return array
     *
     * Note: PRIMARY index (primary key) is also listed!
     */
    function listUniqueIndices() {
        return array_keys($this->getIndexData());
    }

    function listUniqueIndexFields($indexName) {
        if (!in_array($indexName, $this->listUniqueIndices())) trigger_error("No such index: '{$indexName}'", E_USER_ERROR);
        $indexData = $this->getIndexData();
        return $indexData[$indexName];
    }

    /**
     * @return array ('indexName' => array('fieldName1', 'fieldName2'), ...)
     */
    function getIndexData() {
        return $this->indexData;
    }
    
    /**
     * @return bool|string FALSE or name of autoinc field
     */
    function getAutoincFieldName() {
        return $this->autoincFieldName;
    }

    /**
     * Checks if given field(s) are enough to identify one record from the table (they must form PK or other unique index)
     * @param array|string $fieldNameOrNames Names of fields to check
     */
    function identifiesRecordBy($fieldNameOrNames) {
        $indexData = $this->getIndexData();
        $indexData['PRIMARY'] = array($this->pk);
        if (!is_array($fieldNameOrNames)) $fieldNameOrNames = array($fieldNameOrNames);
        $res = false;
        foreach ($indexData as $fieldsOfIndex) {
            if (!array_diff($fieldsOfIndex, $fieldNameOrNames)) {
                $res = true;
                break;
            }
        }
        return $res;
    }

    /**
     * Checks record's presence in the database using all known "unique" indices. Since some "unique" indices can be not backed by the database, arrays of found PKs are
     * returned for each index.
     *
     * @param Ac_Model_Object $record
     * @param bool $dontReturnOwnKey If row with same PK as one of current instance is found, don't add it's PK to resultset
     * @param array usingIndices Names of indices to check (by default, all indices will be used)
     * @param array $customIndices ($indexName => array('key1', 'key2'...))
     * @param bool $withNewRecords Whether to check new records WITHOUT PKs that are stored in the memory. Note that in-memory comparsion is evaluated using different rules (see below)
     * @return array($indexName => array($pk1, $pk2...))
     * @see Ac_Model_Mapper::checkRecordUniqueness
     *
     * On comparsion with new records:
     * - since new records don't have primary keys, links of their instances will be returned instead of PKs
     */
    function checkRecordPresence($record, $dontReturnOwnKey = false, $usingIndices=array(), $customIndices=array(), $withNewRecords = false) {
    	$res = array();
        $pkCols = array();
        if (!$usingIndices) $usingIndices = array_merge($this->listUniqueIndices(), array_keys($customIndices));
        // If we don't have to return own key, it doesn't  matter whether we will find own instance by primary key or not
        // if ($dontReturnOwnKey) $usingIndices = array_diff($usingIndices, array('PRIMARY'));
        foreach ($this->listPkFields() as $pkf) $pkCols[] = $this->db->n($pkf);
        $cpk = count($pkCols) > 1;
        $pkCols = implode(", ", $pkCols);
        foreach ($usingIndices as $idxName) {
            $idxFields = isset($customIndices[$idxName])? $customIndices[$idxName] : $this->listUniqueIndexFields($idxName);
            $crit = $this->indexCrtieria($record, $idxFields, true);
            if ($crit) {
                $sql = "SELECT ".$pkCols." FROM ".$this->db->n($this->tableName)." WHERE ".$crit;
                $pks = $cpk? $this->db->fetchArray($sql) : $this->db->fetchColumn($sql);
                if ($dontReturnOwnKey) {
                    foreach (array_keys($pks) as $i) if ($record->matchesPk($pks[$i])) unset($pks[$i]);
                }
                if ($pks) $res[$idxName] = $pks;
            }
            if ($withNewRecords) {
                $newRecords = $this->find($record->getDataFields($idxFields));
                if ($dontReturnOwnKey && isset($newRecords[$record->_imId])) unset($newRecords[$record->_imId]);
                foreach (array_keys($newRecords) as $k) $res[$idxName][] = $newRecords[$k];
            }
        }
        return $res;
    }
    
    // --------------- SQL to property mapping support (mostly template methods) ---------------

    /**
     * @return array('sqlCol1', 'sqlCol2'...)
     */
    function listSqlColumns() {
    	return $this->columnNames;
    }
    
    /**
     * @return array('sqlCol1', 'sqlCol2'...)
     */
    function listNullableSqlColumns() {
    	return $this->nullableSqlColumns;
    }
    
    // --------------- Function that work with associations and relations ---------------

    function listRelations() {
        if ($this->relations === false) {
            $this->relations = $this->getRelationPrototypes();
            foreach ($this->relations as $k => $rel) {
                if (is_object($rel)) {
                    $rel->setImmutable(true);
                }
            }
            $this->intrinsicRelations = array_keys($this->relations);
        }
        return array_keys($this->relations);
    }

    function listIncomingRelations() {
        foreach ($this->listRelations() as $relId) {
            $rel = $this->getRelation($relId);
            if (!$rel->srcOutgoing) $res[] = $relId;
        }
        return $res;
    }

    function listOutgoingRelations() {
        $res = array_diff($this->listRelations(), $this->listIncomingRelations());
        return $res;
    }

    /**
     * @return Ac_Model_Relation
     */
    protected function createRelation($proto) {
        // Replace mapper classes with mapper instances, if possible
        if ($this->application) $proto['application'] = $this->application;
        if (isset($proto['srcMapperClass']) && $proto['srcMapperClass'] == $this->getId()) {
            $proto['srcMapper'] = $this;
        } elseif (isset($proto['destMapperClass']) && $proto['destMapperClass'] == $this->getId()) {
            $proto['destMapper'] = $this;
        }
        $proto['immutable'] = true;
        $res = Ac_Prototyped::factory($proto, 'Ac_Model_Relation');
        return $res;
    }

    /**
     * @return Ac_Model_Relation
     */
    function getRelation($relId) {
        if ($this->relations === false) $this->listRelations();
        if (isset($this->relations[$relId])) {
            if (is_array($this->relations[$relId])) {
                $res = $this->createRelation($this->relations[$relId]);
                $this->relations[$relId] = $res;
            } else {
                $res = $this->relations[$relId];
            }
        } else {
            throw Ac_E_InvalidCall::noSuchItem('relation', $relId, 'listRelations');
        }
        return $res;
    }
    
    function addRelation($id, $relation) {
        if (!in_array($id, $this->listRelations())) {
            $this->additionalRelations[] = $id;
            if (is_array($relation)) {
                // prototype will be stored for future use
            } elseif (is_object($relation) && $relation instanceof Ac_Model_Relation) {
                $relation->setApplication($this->application);
                if ($relation->srcMapperClass == $this->getId()) {
                    $relation->setSrcMapper($this);
                } elseif ($relation->destMapperClass == $this->getId()) {
                    $relation->setDestMapper($this);
                }
                $relation->setImmutable(true);
                $this->relations[$id] = $relation;
            } else {
                throw Ac_E_InvalidCall::wrongType('relation', $relation, array('array', 'Ac_Model_Relation'));
            }
        } else {
            throw Ac_E_InvalidCall::alreadySuchItem('relation', $id, 'deleteRelation');
        }
    }
    
    function deleteRelation($id) {
        if (in_array($id, $this->listRelations())) {
            if (!in_array($id, $this->listIntrinsicRelations())) {
                unset($this->relations[$id]);
                $this->additionalRelations = array_diff($this->additionalRelations, array($id));
            } else {
                throw new Ac_E_InvalidCall("Cannot delete relation '{$id}' that is intrinsic to "
                    .$this->getId()." mapper; check with listIntrinsicRelations() next time");
            }
        } else {
            throw Ac_E_InvalidCall::noSuchItem('relation', $id, 'listRelations');
        }
    }
    
    function listIntrinsicRelations() {
        if ($this->intrinsicRelations === false) $this->listRelations();
        return $this->intrinsicRelations;
    }
    
    function isIntrinsicRelation($id) {
        return in_array($id, $this->listIntrinsicRelations());
    }
    
    function listAdditionalRelations() {
        return $this->additionalRelations;
    }
    
    protected function doGetRelationPrototypes() {
        return array();
    }

    final function getRelationPrototypes() {
        $res = $this->doGetRelationPrototypes();
        $this->triggerEvent(self::EVENT_ON_GET_RELATION_PROTOTYPES, array(
            & $res
        ));
        return $res;
    }

    // TODO: Add suport for records that are in random access collections (invoke 
    // methods listAssocFor(), loadAssocFor(), countAssocFor() for all records 
    // of collection)
    function loadAssocFor ($record, $relId) {
        $rel = $this->getRelation($relId);
        $rel->loadDest($record);
    }

    /**
     * Loads associated records keys for $record
     * Currently this function works same as loadAssocFor()
     */
    function listAssocFor ($record, $relId) {
        $rel = $this->getRelation($relId);
        $rel->loadDest($record);
    }

    function loadAssocCountFor ($record, $relId) {
        $rel = $this->getRelation($relId);
        $rel->loadDestCount($record);
    }

    function loadAssocNNIdsFor ($record, $relId) {
        $rel = $this->getRelation($relId);
        $rel->loadDestNNIds($record);
    }
    
    /**
     * @param Ac_Model_Object $record
     * @return string|false
     */
    protected function indexCrtieria($record, $fieldNames, $mustBeFull) {
        $vals = $record->getDataFields($fieldNames, !$mustBeFull);
        if ($mustBeFull && (count($vals) < count($fieldNames))) return false;
        $cr = array();
        foreach ($fieldNames as $fn) {
            $cr[] = $this->db->n($fn).' = '.$this->db->q($vals[$fn]);
        }
        return "(".implode(" AND ", $cr).")";
    }            

    /**
     * @return Ac_Model_Validator
     */
    function getCommonValidator() {
        if ($this->validator === false) {
            $this->validator = new Ac_Model_Validator($this->getPrototype(), $this->getPrototype()->getPropertiesInfo());
        }
        return $this->validator;
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

    function isMyRecord($rec, $throw = false) {
        $res = $rec instanceof $this->recordClass;
        if ($throw && !$rec) {
            throw new Ac_E_InvalidUsage("\$rec is of class ".get_class($rec).", must be an instance of "
                .$this->recordClass." to be supported by persistence functions of ".get_class($this));
        }
        return $res;
    }
    
    /**
     * @param mixed $primaryKey
     * @return array with persistence data | FALSE if record not found
     */
    function peLoad($record, $primaryKey, & $error = null) {
        $data = $this->db->fetchRow('SELECT * FROM '.$this->db->n($this->tableName).' WHERE '.$this->pk.' = '.$this->db->q($primaryKey));
        $this->triggerEvent(self::EVENT_ON_PE_LOAD, array(& $data, $primaryKey, $record, & $error));
        return $data;
    }

    protected function corePeSave($record, & $hyData, & $exists = null, & $error = null, & $newData = array()) {
        if (is_null($exists)) $exists = array_key_exists($this->pk, $dataToSave);
        
        // leave only existing columns
        $dataToSave = array_intersect_key($hyData, array_flip($this->listSqlColumns()));
        
        if ($exists) {
            $query = $this->db->updateStatement($this->tableName, $dataToSave, $this->pk, false);
            if ($this->db->query($query) !== false) $res = $dataToSave;
            else {
                $descr = $this->db->getErrorDescr();
                if (is_array($descr)) $descr = implode("; ", $descr);
                $error = $this->db->getErrorCode().': '.$descr;
                $res = false;
            }
        } else {
            $query = $this->db->insertStatement($this->tableName, $dataToSave);
            if ($this->db->query($query)) {
                if (strlen($ai = $this->getAutoincFieldName()) && !isset($dataToSave[$ai])) {
                    $newData = array($ai => $this->getLastGeneratedId());
                }
                $res = true;
            } else {
                $descr = $this->db->getErrorDescr();
                if (is_array($descr)) $descr = implode("; ", $descr);
                $error = $this->db->getErrorCode().':'.$descr;
                $res = false;
            }
        }
        if ($res) $this->markUpdated();
        return $res;
    }
    
    /**
     * @param mixed $hyData Persistence data
     * @return mixed persistence data on success, FALSE on failure
     */
    final function peSave($record, $hyData, $exists = null, & $error = null, & $newData = array()) {
        $res = null;
        $this->triggerEvent(self::EVENT_BEFORE_STORE_RECORD, array(
            $record, & $hyData, & $newData, & $exists, & $res, & $error
        ));
        if (is_null($res)) { // Do core code only if $res is not determied
            $res = $this->corePeSave($record, $hyData, $exists, $error, $newData);
        }
        $this->triggerEvent(self::EVENT_AFTER_STORE_RECORD, array(
            $record, & $hyData, & $newData, & $exists, & $res, & $error
        ));
        return $res;
    }
    
    protected function getLastGeneratedId() {
        return $this->db->getLastInsertId();
    }
    
    /**
     * @param type $hyData 
     * @return bool
     */
    protected function corePeDelete($record, $hyData, & $error = null) {
        $key = $hyData[$this->pk];
        $res = (bool) $this->db->query($sql = "DELETE FROM ".$this->db->n($this->tableName)." WHERE "
            .$this->db->n($this->pk)." ".$this->db->eqCriterion($key));
        if ($res) $this->markUpdated();
        return $res;
    }
    
    /**
     * @param type $hyData 
     * @return bool
     */
    final function peDelete($record, $hyData, & $error = null) {
        $res = null;
        
        $this->triggerEvent(self::EVENT_BEFORE_DELETE_RECORD, array(
            $record, & $hyData, & $error, & $res
        ));
        
        
        if (is_null($res)) 
            $res = $this->corePeDelete ($record, $hyData, $error);
        
        $this->triggerEvent(self::EVENT_AFTER_DELETE_RECORD, array(
            $record, & $hyData, & $error, & $res
        ));
        return $res;
    }
    
    protected function corePeConvertForLoad($record, $hyData) {
        $res = $hyData;
        $d = $this->db->getDialect();
        if ($d->hasToConvertDatesOnLoad()) {
            $res = $this->convertDates($oid, $this->getDateFormats()); 
        }
        return $res;
    }
    
    final function peConvertForLoad($record, $hyData) {
        $res = $this->corePeConvertForLoad($record, $hyData);
        $this->triggerEvent(self::EVENT_ON_CONVERT_FOR_LOAD, array(
            $record, $hyData, & $res
        ));
        return $res;
    }
    
    protected function corePeConvertForSave($record, $hyData) {
        $res = $hyData;
        $d = $this->db->getDialect();
        $df = $this->getDateFormats();
        if ($df) {
            foreach ($df as $prop => $type) {
                if (array_key_exists($prop, $res)) {
                    $res[$prop] = $d->convertDateForStore($res[$prop], $type);
                }
            }
        }
        return $res;
    }
    
    final function peConvertForSave($record, $hyData) {
        $res = $this->corePeConvertForSave($record, $hyData);
        $this->triggerEvent(self::EVENT_ON_CONVERT_FOR_SAVE, array(
            $record, $hyData, & $res
        ));
        return $res;
    }

    protected function corePeReplaceNNRecords($record, $rowProto, $rows, $midTableName, & $errors = array()) {
        $res = true;
        if (count($rowProto)) {
            $sqlDb = $this->db;
            if (!$sqlDb->query('DELETE FROM '.$sqlDb->n($midTableName).' WHERE '.$sqlDb->valueCriterion($rowProto))) {
                $errors['idsDelete'] = 'Cannot clear link records';
                $res = false;
            }
            if (count($rows)) {
                if (!$sqlDb->query($sqlDb->insertStatement($midTableName, $rows, true))) {
                    $errors['idsInsert'] = 'Cannot store link records';
                    $res = false;
                }
            }
        }
        return $res;
    }
    
    final function peReplaceNNRecords($record, $rowProto, $rows, $midTableName, & $errors = array()) {
        $res = null;
        $this->triggerEvent(self::EVENT_BEFORE_REPLACE_NN_RECORDS, array(
            $record, & $rowProto, & $rows, & $midTableName, & $res, & $errors
        ));
        
        if (is_null($res)) 
            $res = $this->corePeReplaceNNRecords ($record, $rowProto, $rows, $midTableName, $errors);
        
        $this->triggerEvent(self::EVENT_AFTER_REPLACE_NN_RECORDS, array(
            $record, & $rowProto, & $rows, & $midTableName, & $res, & $errors
        ));
        return $res;
    }
    
    /**
     * Any call will fetch ALL records into the memory.
     * 
     * - FALSE to fetch ALL records, 
     * - array with PKs to get SOME records 
     * - PK to get Single record 
     * 
     * 
     * @param false|scalar|array $key
     * @return array|Ac_Model_Object
     */
    function getAllRecords($key = false) {
        if ($this->allRecords === false) {
            
            if (($o = $this->getDefaultOrdering()) !== false) $ord = $o;
            elseif (strlen($t = $this->getTitleFieldName()) && !$this->isTitleAProperty()) $ord = $t.' ASC';
                else $ord = '';
                
            $this->allRecords = $this->loadRecordsByCriteria('', true, $ord);
        }
        if ($key === false) {
            $res = $this->allRecords;
        } else {
            if (is_array($key)) {
                $res = array();
                foreach (array_intersect(array_keys($this->allRecords), $key) as $k) {
                    $res[$k] = $this->allRecords[$k];
                }
            }
            elseif (isset($this->allRecords[$key])) $res = $this->allRecords[$key];
                else $res = null;
        }
        return $res;
    }
    
    function hasAllRecords() {
        return is_array($this->allRecords);
    }
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = array(
            'class' => 'Ac_Sql_Select',
			'tables' => array(
				$primaryAlias => array(
					'name' => $this->tableName, 
				),
			),
			'tableProviders' => array(
				'model' => array(
					'class' => 'Ac_Model_Sql_TableProvider',
                    'mapperAlias' => $primaryAlias,
					'mapper' => $this,
				),
			),
        );
        return $res;
    }
    
    final function getSqlSelectPrototype($primaryAlias = 't') {
        $res = $this->doGetSqlSelectPrototype($primaryAlias);
        $this->triggerEvent(self::EVENT_ON_GET_SELECT_PROTOTYPE, array(
            & $res, $primaryAlias
        ));
        return $res;
    }
    
    /**
     * @param array $prototypeExtra
     * @param string $primaryAlias
     * @return Ac_Sql_Select
     */
    function createSqlSelect(array $prototypeExtra = array(), $primaryAlias = 't') {
        $res = new Ac_Sql_Select($this->getDb(), Ac_Util::m($this->getSqlSelectPrototype($primaryAlias), 
            $prototypeExtra));
        return $res;
    }
    
    protected function doGetManagerConfig() {
        return array();
    }
    
    final function getManagerConfig() {
        $res = $this->doGetManagerConfig();
        $this->triggerEvent(self::EVENT_ON_GET_MANAGER_CONFIG, array(
            & $res
        ));
        return $res;
    }
    
    /**
     * Preloads direct and indirect relations 
     *
     * @param array $records Flat array of current mapper' records
     * @param array $relationData array('relationId1', array('relationId2', 'relationId2.2'))
     */
    function preloadRelations(array $records, array $relationData) {
        foreach ($relationData as $relId) {
            $recs = $records;
            $relId = Ac_Util::toArray($relId);
            $mapper = $this;
            while (($id = array_shift($relId)) !== null) {
                $rel = $mapper->getRelation($id);
                $recs = $rel->loadDest($recs);
                if (count($relId)) {
                    $mapper = $this->getApplication()->getMapper($rel->destMapperClass);
                    $recs = Ac_Util::flattenArray($recs);
                }
            }
        }
    }
    
    function reset() {
        $this->recordsCollection = array();
        $this->newRecords = array();
        $this->allRecords = false;
        $this->fkFieldsData = false;
        $this->triggerEvent(self::EVENT_ON_RESET);
    }

    /**
     * @return array information about fields that are parts of foreign keys 
     * Return format:
     * [ $fieldId => [ 
     *        'isRestricted' => bool, 
     *        'isOutgoing' => bool,
     *        'isNullable' => bool,
     *        'relations' => [ $relId => [
     *          'objectProperty' => $objectProperty,
     *          'varName' => $varName, 
     *          'isRestricted' => $isRestricted, 
     *          'otherFields' => [ ... ]
     *        ] ]
     * ] ]
     */
    function getFkFieldsData() {
        if ($this->fkFieldsData === false) {
            $this->fkFieldsData = array();
            $proto = $this->getPrototype();
            $rel2propMap = array();
            foreach (array_keys($proto->listAssociations()) as $propName) {
                $prop = $proto->getPropertyInfo($propName, true);
                if (isset($prop->relationId) && strlen($prop->relationId)) {
                    $rel2propMap[$prop->relationId] = $propName;
                }
            }
            
            // step1: list all participating fields
            foreach ($this->listRelations() as $relId) {
                $rel = $this->getRelation($relId);
                if (($rel->srcOutgoing && $rel->getSrcMapper() === $this && $rel->getDestMapper())) {
                    $fl = $rel->fieldLinks;
                    $fields = array_keys($fl);
                    foreach ($fields as $fieldId) {
                        if (isset($rel2propMap[$relId])) $inf['objectProperty'] = $rel2propMap[$relId];
                            else $inf['objectProperty'] = false;
                        $inf = array(
                            'otherFields' => array_diff($fields, array($fieldId)),
                            'varName' => $rel->srcVarName                        
                        );
                        $this->fkFieldsData[$fieldId]['relations'][$relId] = $inf;
                    }
                }
            }
            
            $null = array_flip(array_intersect(
                array_keys($this->fkFieldsData), 
                $this->listNullableSqlColumns()
            ));
            
            // step2: calculate restricted fields
            foreach ($this->fkFieldsData as $fieldId => $rels) {
                $this->fkFieldsData[$fieldId]['isNullable'] = isset($null[$fieldId]);
                $pi = $proto->getPropertyInfo($fieldId, true);
                foreach ($rels as $relId => $inf) {
                    $isRestricted = false;
                    if (isset($pi->isRestricted)) $isRestricted = $pi->isRestricted;
                    else {
                        if ($fieldId === $this->pk) $isRestricted = true;
                        elseif (count($rels['relations']) > 1) {
                            $isRestricted = true;
                        }
                    }
                    $this->fkFieldsData[$fieldId]['isRestricted'] = $isRestricted;
                }
            }
        }
        return $this->fkFieldsData;
    }    
    
    function addEventListener($objectOrCallback, $event) {
        if ($event === self::EVENT_ON_GET_RELATION_PROTOTYPES) {
            $this->askRelationsForDefaults = true;
        }
        elseif ($event === self::EVENT_ON_GET_DEFAULTS) {
            $this->computedDefaults = false;
        }
        elseif ($event === self::EVENT_ON_GET_INTERNAL_DEFAULTS) {
            $this->internalDefaults = false;
        }
        return parent::addEventListener($objectOrCallback, $event);
    }
    
    function getDefaults($full = false) {
        if ($this->computedDefaults === false) {
            if ($this->defaults === false)
                $this->getColumnNames();
            $this->computedDefaults = $this->defaults;
            $this->triggerEvent(self::EVENT_ON_GET_DEFAULTS, array(& $this->computedDefaults));
        }
        $res = $this->computedDefaults;
        if ($full) Ac_Util::ms($res, $this->getInternalDefaults());
        return $res;
    }
    
    final function getInternalDefaults() {
        if ($this->internalDefaults === false) {
            $this->internalDefaults = $this->doGetInternalDefaults();
            $this->triggerEvent(self::EVENT_ON_GET_INTERNAL_DEFAULTS, array(& $this->internalDefaults));
            if ($this->askRelationsForDefaults || $this->additionalRelations) {
                Ac_Util::ms($this->internalDefaults, $this->getRelationDefaults());
            }
        }
        return $this->internalDefaults;
    }
    
    protected function doGetInternalDefaults() {
        return array();
    }
    
    protected function getRelationDefaults() {
        if ($this->askRelationsForDefaults) $kk = array_keys($this->relations);
            else $kk = $this->additionalRelations;
        $res = array();
        foreach ($kk as $k) {
            $rel = $this->getRelation($k);
            if ($rel->getSrcMapper() === $this) {
                foreach (array('getSrcVarName', 'getSrcNNIdsVarName', 'getSrcCountVarName', 
                    'getSrcLoadedVarName') as $p) {
                    if (strlen($var = $rel->$p())) $res[$var] = false;
                }
            }
        }
        return $res;
    }
    
    static function mapRows(array $rows, array $origToTargetMap, $singleRow = false) {
        if ($origToTargetMap) {
            if ($singleRow) $rows = array($rows);
            $res = array();
            foreach ($rows as $idx => $row) {
                foreach ($row as $key => $value) {
                    if (isset($origToTargetMap[$key])) {
                        $key = $origToTargetMap[$key];
                        if ($key === false) continue;
                    }
                    $res[$idx][$key] = $value;
                }
            }
            if ($singleRow) $res = $res[0];
        } else {
            $res = $rows;
        }
        return $res;
    }
    
}