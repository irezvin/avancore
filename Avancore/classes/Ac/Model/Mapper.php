<?php

class Ac_Model_Mapper extends Ac_Mixin_WithEvents implements Ac_I_LifecycleAwareCollection {
    
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
    const EVENT_ON_GET_RELATION_PROTOTYPES = 'onGetRelationPrototypes';

    /**
     * function onGetAssociationPrototypes(& $associationPrototypes)
     */
    const EVENT_ON_GET_ASSOCIATION_PROTOTYPES = 'onGetAssociationPrototypes';
    
    /**
     * function onGetManagerConfig(& $managerConfig)
     */
    const EVENT_ON_GET_MANAGER_CONFIG = 'onGetManagerConfig';

    /**
     * function onUpdated()
     */
    const EVENT_ON_UPDATED = 'onUpdated';
    
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
    
    /**
     * function onGetSqlTable($alias, $prevAlias, Ac_Sql_Select_TableProvider $tableProvider, & $result)
     */
    const EVENT_ON_GET_SQL_TABLE  = 'onGetSqlTable';
    
    /**
     * function onListDataProperties(array & $dataProperties)
     */
    const EVENT_ON_LIST_DATA_PROPERTIES = 'onListDataProperties';
    
    const EVENT_ON_LIST_MAPPERS = 'onListMappers';
    
    const INSTANCE_ID_PREFIX = '\\i\\';
    
    const INSTANCE_ID_PREFIX_LENGTH = 3;
    
    var $tableName = null;

    var $recordClass = 'Ac_Model_Record';

    var $pk = null;

    /**
     * Use records collection (records that already were loaded will not be loaded again)
     */
    var $useRecordsCollection = false;
    
    var $trackNewRecords = false;
    
    var $nullableSqlColumns = array();
    
    /**
     * @var array ('indexName' => array('fieldName1', 'fieldName2'), ...)
     */
    var $indexData = array();
    
    var $useProto = false;
    
    var $managerClass = false;

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
    
    protected $prototype = false;

    protected $recordsCollection = array();
    
    protected $collectionConflictMode = false;    
    
    protected $lastCollectionObject = null;
    
    protected $lastCollectionKey = null;
    
    
    /**
     * Keys in $recordsCollection of records that didn't have their instance IDs
     * @array (key => true)
     */
    protected $keysOfNewRecords = array();
    
    protected $allRecordsLoaded = false;
    
    /**
     * which field is used as identifier for every record (if applicable)
     */
    protected $identifierField = false;
    
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
     * Ac_Model_Association instances (common for all records)
     * @var array
     */
    protected $associations = false;
    
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

    protected $proto = array();

    /**
     * @var Ac_Model_MapperInfo
     */
    protected $info = false;

    protected $titleFieldExpression = false;
    
    protected $validator = false;
    
    protected $updateMark = false;
    
    protected $updateLevel = 0;
    
    protected $columnNames = false;
    
    protected $dataProperties = false;
    
    protected $defaults = false;
    
    protected $allRecords = false;
    
    protected $fkFieldsData = false;
    
    protected $internalDefaults = false;
    
    protected $askRelationsForDefaults = true;
    
    protected $computedDefaults = false;
    
    protected $mappers = false;
    
    protected $typeField = false;
    
    /**
     * @var Ac_Model_Storage
     */
    protected $storage = false;
        
    function __construct(array $options = array()) {
        // TODO: application & db are initialized last, id & tableName - first
        parent::__construct($options);
    }
    
    function initFromPrototype(array $prototype = array(), $strictParams = null) {
        $first = array_flip(array('id', 'tableName', 'db', 'application'));
        $prototype = array_merge(array_intersect_key($prototype, $first), array_diff_key($prototype, $first));
        return parent::initFromPrototype($prototype, $strictParams);
    }
    
    function setId($id) {
        if ($this->id !== false && $this->id !== $id) throw new Exception("Can setId() only once! Old id was '{$this->id}', new one is '{$id}'");
        $this->id = $id;
    }
    
    function getId() {
        if ($this->id === false) {
            $this->id = get_class($this);
            if ($this->id == 'Ac_Model_Mapper') {
                if (strlen($this->tableName)) {
                    $this->id .= '_'.$this->tableName;
                }
            }
        }
        return $this->id;
    }    

    function setApplication(Ac_Application $application) {
        $this->application = $application;
        
        // buggy at the moment - works bad with Ac_Prototyped::factoryCollection()
        /*if (strlen($this->getId())) 
            $application->addMapper($this, true);*/
        
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
        if (!strlen($this->pk) && strlen($this->tableName)) {
            $this->inspect();
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
    
    protected function inspect() {
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

    /**
     * @return Ac_Model_Mapper
     */
    static function getMapper($mapperClass, Ac_Application $application = null) {
        if (is_object($mapperClass) && $mapperClass instanceof Ac_Model_Mapper) {
            $res = $mapperClass;
        } else {
            $res = null;
            if ($application) $res = $application->getMapper($mapperClass);
            else {
                foreach(Ac_Application::listInstances() as $className => $ids) {
                    foreach ($ids as $appId) {
                        $app = Ac_Application::getApplicationInstance($className, $appId);
                        if ($app->hasMapper($mapperClass)) {
                            $res = $app->getMapper($mapperClass);
                        }
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
    
    final function listDataProperties() {
        if ($this->dataProperties === false) {
            $this->dataProperties = $this->getColumnNames();
            $this->doOnListDataProperties($this->dataProperties);
            $this->triggerEvent(self::EVENT_ON_LIST_DATA_PROPERTIES, array(& $this->dataProperties));
        }
        return $this->dataProperties;
    }
    
    protected function doOnListDataProperties(array & $dataProperties) {
    }
    
    final function registerRecord(Ac_Model_Object $record) {
        $this->coreRegisterRecord($record);
        $this->triggerEvent(self::EVENT_AFTER_CREATE_RECORD, array(& $record));
    }
    
    protected function coreRegisterRecord(Ac_Model_Object $record) {
        $this->isMyRecord($record, true);
    }
    
    /**
     * Creates new record instance that is bound to $this mapper.
     * 
     * @param string $typeId Id of child mapper (FALSE = create default if possible)
     * @return Ac_Model_Object
     */
    function createRecord($typeId = false) {
        $res = $this->getStorage()->createRecord($typeId);
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
    
    function listRecords() {
        if ($this->allRecordsLoaded)
            $res = array_diff(array_keys($this->allRecords), array_keys($this->newRecords));
        else {
            $res = $this->getStorage()->listRecords();
        }
        return $res;
    }

    /**
     * @param mixed id string or array; returns count of records that exist in the database
     */
    function recordExists($idOrIds) {
        $res = null;
        if ($this->useRecordsCollection && $this->recordsCollection) {
            $ids = array_unique(Ac_Util::toArray($idOrIds));
            $res = count(array_intersect(array_keys($this->recordsCollection), $ids));
            if ($res < count($ids) && !$this->allRecordsLoaded) $res = null;
        }
        if (is_null($res)) 
            $res = $this->getStorage()->recordExists($idOrIds);
        return $res;
    }
    
    /**
     * Loads single record
     * @param string Identifiers of the record to be loaded
     * @return Ac_Model_Object
     */
    function loadRecord($id) {
        if ($this->useRecordsCollection && isset($this->recordsCollection[$id])) {
            $res = $this->recordsCollection[$id];
        } else {
            $res = $this->getStorage()->loadRecord($id);
        }
        return $res;
    }

    /**
     * Loads array of records.

     * @return Ac_ModelObject[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        if (!is_bool($keysToList)) throw Ac_E_InvalidCall::wrongType ('$keysToList', $keysToList, 'bool');
        $ids = array_unique($ids);
        if ($this->useRecordsCollection) {
            $records = array_intersect_key($this->recordsCollection, array_flip($ids));
            if (count($records) < count($uIds)) {
                $loadIds = array_diff($uIds, array_keys($records));
            } else {
                $loadIds = array();
            }
        } else {
            $records = array();
            $loadIds = $ids;
        }
        if ($loadIds) {
            foreach ($this->getStorage()->loadRecordsArray($loadIds) as $id => $rec) {
                $records[$id] = $rec;
            }
        }
        $res = array();
        /**
         * This helps to maintain records in order that was specified in $ids
         */
        $fIds = array_intersect($ids, array_keys($records));
        if ($keysToList) {
            foreach ($fIds as $id) {
                $res[$id] = $records[$id];
            }
        } else {
            foreach ($fIds as $id) {
                $res[] = $records[$id];
            }
        }
        return $res;
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @deprecated Will be removed in 0.4
     * @return Ac_Model_Object
     */
    function loadFirstRecord($where = '', $order = '', $joins = '', 
            $limitOffset = false, $tableAlias = false) {
        $storage = $this->getStorage();
        if (!$storage instanceof Ac_Model_Storage_Sql) 
            throw new Ac_E_InvalidCall(get_class($storage)." of mapper '{$this->id}' is not Ac_Model_Storage_Sql");
        $res = $storage->loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
        return $res;
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @deprecated Will be removed in 0.4
     * @return Ac_Model_Object
     */
    function loadSingleRecord($where = '', $order = '', $joins = '', 
        $limitOffset = false, $limitCount = false, $tableAlias = false) 
    {
        $storage = $this->getStorage();
        if (!$storage instanceof Ac_Model_Storage_Sql) 
            throw new Ac_E_InvalidCall(get_class($storage)." of mapper '{$this->id}' is not Ac_Model_Storage_Sql");
        $res = $storage->loadSingleRecord($where, $order, $joins, $limitOffset, $tableAlias);
        return $res;
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Ac_Model_Object[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        $storage = $this->getStorage();
        if (!$storage instanceof Ac_Model_Storage_Sql) 
            throw new Ac_E_InvalidCall(get_class($storage)." of mapper '{$this->id}' is not Ac_Model_Storage_Sql");
        $res = $storage->loadRecordsByCriteria($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
        if ($keysToList === false) $res = array_values($res);
            elseif ($keysToList !== true) $res = $this->indexObjects($res, $keysToList);
        return $res;
    }

    /**
     * @param array $rows Array of storage-specific rows
     * @return Ac_Model_Object[]
     */
    final function loadFromRows(array $rows, $keysToList = false) {
        $objects = array();
        
        list($idData, $uniqueRows) = $this->getStorage()->groupRowsByIdentifiers($rows);
        
        if ($this->useRecordsCollection) {
            foreach (array_keys($uniqueRows) as $identifier) {
                if (isset($this->recordsCollection[$identifier])) {
                    $objects[$identifier] = $this->recordsCollection[$identifier];
                    unset($uniqueRows[$identifier]);
                } else {
                    $objects[$identifier] = null;
                }
            }
        }
        
        $this->triggerEvent(self::EVENT_ON_BEFORE_LOAD_FROM_ROWS, array(& $uniqueRows, & $objects));
        
        if ($this->useRecordsCollection) {
            foreach (array_intersect_key($this->recordsCollection, $uniqueRows) as $id => $object) {
                $objects[$id] = $object;
                unset($uniqueRows[$key]);
            }
        }

        $uniqueRows = $this->getStorage()->prepareRowsForLoading($uniqueRows);
        $loaded = $this->coreLoadFromRows($uniqueRows);
        
        $this->triggerEvent(self::EVENT_ON_AFTER_LOAD_FROM_ROWS, array($uniqueRows, & $loaded));
        
        foreach ($loaded as $identifier => $record) {
            $objects[$identifier] = $record;
        }

        if ($this->useRecordsCollection) {
            foreach ($loaded as $identifier => $record) {
                $this->recordsCollection[$identifier] = $record;
            }
        }
        
        if ($keysToList) {
            $areById = 
                $keysToList === true 
                || (
                    $this->identifierField !== false 
                    && (
                        $keysToList === $this->identifierField 
                        || is_array($keysToList) && array_values($keysToList) == array($this->identifierField)
                    )
                );
            if ($areById) {
                $res = $objects;
            }   else {
                $res = $this->indexObjects($objects, $keysToList);
            }
        } else {
            $res = array();
            foreach ($idData as $rowIndex => $id) if (isset($objects[$id])) {
                $res[$rowIndex] = $objects[$id];
            }
        }
        
        return $res;
    }

    protected function coreLoadFromRows(array $rows) {
        $res = array();
        $storage = $this->getStorage();
        $rows = $this->getStorage()->prepareRowsForLoading($rows);
        foreach ($rows as $id => $row) {
            if ($this->useRecordsCollection && isset($this->recordsCollection[$id])) {
                $res[$id] = $this->recordsCollection[$id];
            } else {
                $res[$id] = $storage->loadFromRow($row);
            }
        }
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
            
        } elseif ($keysToList === true || ($this->identifierField !== false && ($keysToList === $this->identifierField 
            || is_array($keysToList) && array_values($keysToList) == array($this->identifierField)))) 
        {
            foreach ($objects as $rec) {
                $res[$this->getIdentifier($record)] = $rec;
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
    
    function listModelProperties() {
        $proto = $this->getPrototype();
        return $proto->listDataProperties();
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

    // --------------------- Functions that work with columns, keys and indices -------------------

    function getTitleFieldName() {
        return false;
    }

    function isTitleAProperty() {
        return false;
    }

    function getDefaultOrdering() {
        return false;
    }
    
    /**
     * @return array (array($pk1, $title1), array($pk2, $title2), ...)
     */
    function getRecordTitles($where = false, $ordering = false, $extraJoins = false, $titleFieldName = false, $titleIsProperty = '?', $valueFieldName = false, $valueIsProperty = false) {
        if ($titleFieldName === false) {
            if (!strlen($this->titleFieldExpression)) $titleFieldName = $this->getTitleFieldName();
            else $titleFieldName = $this->titleFieldExpression;
        }
        if ($titleIsProperty === '?') $titleIsProperty = $this->isTitleAProperty();
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
     * @deprecated
     */
    function getAutoincFieldName() {
        return $this->autoincFieldName;
    }
    
    function listGeneratedFields() {
        return $this->getStorage()->listGeneratedFields();
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
            $crit = $this->indexCriteria($record, $idxFields, true);
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
            foreach ($this->relations as $rel) {
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
     * @return array
     */
    function getAssociations() {
        if ($this->associations === false) {
            $this->associations = array();
            $proto = $this->getAssociationPrototypes();
            $this->addAssociations($proto);
        }
        return $this->associations;
    }
    
    /**
     * @return array
     */
    function listAssociations() {
        if ($this->associations === false) $this->getAssociations();
        return array_keys($this->associations);
    }
    
    /**
     * @return Ac_Model_Association_Abstract
     */
    function getAssociation($id, $dontThrow = false) {
        $res = null;
        if ($this->associations === false) $this->getAssociations();
        if (isset($this->associations[$id])) $res = $this->associations[$id];
        if (!$res && !$dontThrow) 
            throw Ac_E_InvalidCall::noSuchItem ('association', $id, 'listAssociations');
        return $res;
    }
    
    function addAssociations(array $associations) {
        $objects = Ac_Prototyped::factoryCollection($associations, 'Ac_I_ModelAssociation', 
            array('mapper' => $this, 'immutable' => true), 'id', true, true);
        
        foreach ($objects as $k => $a) {
            if (isset($this->associations[$k]) && $this->associations[$k] !== $a) {
                throw Ac_E_InvalidCall::alreadySuchItem('association', $k);
            }
            $this->associations[$k] = $a;
        }
        return $objects;
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
    
    protected function doGetAssociationPrototypes() {
        return array();
    }

    final function getRelationPrototypes() {
        $res = $this->doGetRelationPrototypes();
        $this->triggerEvent(self::EVENT_ON_GET_RELATION_PROTOTYPES, array(
            & $res
        ));
        return $res;
    }

    protected final function getAssociationPrototypes() {
        $res = $this->doGetAssociationPrototypes();
        $this->triggerEvent(self::EVENT_ON_GET_ASSOCIATION_PROTOTYPES, array(
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
    protected function indexCriteria($record, $fieldNames, $mustBeFull) {
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
        $data = $this->getStorage()->peLoad($record, $primaryKey, $error);
        $this->triggerEvent(self::EVENT_ON_PE_LOAD, array(& $data, $primaryKey, $record, & $error));
        return $data;
    }

    protected function corePeSave($record, & $hyData, & $exists = null, & $error = null, & $newData = array()) {
        $res = $this->getStorage()->peSave($record, $hyData, $exists, $error, $newData);
        if ($res) $this->markUpdated();
        return $res;
    }
        
    
    /**
     * @param mixed $hyData Persistence data
     * @return mixed persistence data on success, FALSE on failure
     */
    final function peSave($record, $hyData, & $exists = null, & $error = null, & $newData = array()) {
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
    
    /**
     * @param type $hyData 
     * @return bool
     */
    protected function corePeDelete($record, $hyData, & $error = null) {
        $res = $this->getStorage()->peDelete($record, $hyData, $error);
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
        $res = $this->getStorage()->peConvertForLoad($record, $hyData);
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
        $res = $this->getStorage()->peConvertForSave($record, $hyData);
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
        $res = $this->getStorage()->peReplaceNNRecords($record, $rowProto, $rows, $midTableName, $errors);
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
        if ($this->askRelationsForDefaults) {
            $this->listRelations();
            $kk = array_keys($this->relations);
        } else {
            $kk = $this->additionalRelations;
        }
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
    
    function addMixable(Ac_I_Mixable $mixable, $id = false, $canReplace = false) {
        parent::addMixable($mixable, $id, $canReplace);
        Ac_Model_Data::clearMetaCache($this->getId());
        $this->dataProperties = false;
        $this->resetPrototype();
    }
    
    function resetPrototype() {
        $this->prototype = false;
    }
    
    /**
     * Locates joined SQL table for Ac_Sql_Select and returns its' prototype or an instance
     * (can return Ac_Sql_Table instance too)
     * 
     * Is called by Ac_Model_Sql_TableProvider when it has resolved the mapper but not found
     * any satisfying property
     *  
     * @param string $alias Alias of currently saught table
     * @param string $prevAlias Alias of the table that result (probably) have to be joined to
     * @param Ac_Sql_Select_TableProvider $provider TableProvider that made the request
     */
    final function getSqlTable ($alias, $prevAlias, Ac_Sql_Select_TableProvider $provider) {
        $result = $this->doOnGetSqlTable($alias, $prevAlias, $provider);
        if (!$result) {
            $this->triggerEvent(self::EVENT_ON_GET_SQL_TABLE, array($alias, $prevAlias, $provider, & $result));
        }
        return $result;
    }
 
    /**
     * @see Ac_Model_Mapper::getSqlTable
     */
    protected function doOnGetSqlTable($alias, $prevAlias, Ac_Sql_Select_TableProvider $provider) {
    }

    function hasStorage() {
        return (bool) $this->storage;
    }
    
    function listMappers() {
        return array();
    }
    
    /**
     * @return Ac_Model_Storage
     */
    function getStorage($dontThrowIfCantCreate = false) {
        if ($this->storage === false) {
            $this->storage = null;
            $storage = null;
            if (!$this->listMappers()) {
                if (strlen($this->tableName) && strlen($this->recordClass)) 
                    $storage = $this->createMonoTableStorage();
            } else {
                if (strlen($this->tableName) && strlen($this->typeField)) {
                    $storage = $this->createStorageWithTypeField();
                }
            }
            if (!$storage && !$dontThrowIfCantCreate) {
                throw new Ac_E_InvalidUsage(__METHOD__.": cannot guess default Ac_Model_Storage, setStorage() first");
            }
            $this->storage = $storage;
        }
        if (!is_object($this->storage)) {
            $proto = $this->storage;
            $this->storage = null;
            $this->setStorage(Ac_Prototyped::factory($proto, 'Ac_Model_Storage'));
        }
        return $this->storage;
    }
    
    /**
     * @param Ac_Model_Storage $storage
     */
    function setStorage($storage) {
        if ($this->storage !== $storage) {
            if (is_object($storage) && !$storage instanceof Ac_Model_Storage)
                throw Ac_E_InvalidCall::wrongClass('storage',  $storage, 'Ac_Model_Storage');
            if (is_object($this->storage)) $this->storage->setMapper(null);
            $this->storage = $storage;
            if (is_object($this->storage)) $this->storage->setMapper($this);
        }
    }
    
    protected function createMonoTableStorage() {
        $res = array(
            'class' => 'Ac_Model_Storage_MonoTable',
            'tableName' => $this->tableName,
            'recordClass' => $this->recordClass,
            'primaryKey' => $this->pk,
            'autoincFieldName' => $this->autoincFieldName,
            'application' => $this->application,
            'db' => $this->db,
            'sqlColumns' => $this->listSqlColumns(),
        );
        return $res;
    }
    
    protected function createStorageWithTypeField() {
        $res = $this->createMonoTableStorage();
        $res['class'] = 'Ac_Model_Storage_WithTypeField';
        $res['typeField'] = $this->typeField;
        return $res;
    }
    
    function isAbstract() {
        return $this->recordClass == false;
    }

    /**
     * Sets which field is used as identifier for every record (if applicable)
     */
    function setIdentifierField($identifierField) {
        if ($identifierField !== ($oldIdentifierField = $this->identifierField)) {
            $this->identifierField = $identifierField;
        }
    }

    /**
     * Returns which field is used as identifier for every record (if applicable)
     */
    function getIdentifierField() {
        return $this->identifierField;
    }
    

    function setRowIdentifierField($rowIdentifierField) {
        $this->rowIdentifierField = $rowIdentifierField;
    }

    function getRowIdentifierField() {
        return $this->rowIdentifierField;
    }    
    
    protected $rowIdentifierField = false;
    
    function getIdentifier(Ac_Model_Object $record) {
        if (strlen($this->identifierField)) {
            $res = $record->{$this->identifierField};
            if ($res === false) $res = $this->getStorage()->getIdentifier($record);
        } else {
            $res = $this->getStorage()->getIdentifier($record);
        }
        return $res;
    }
    
    protected function getIdentifierFromRow(array $row) {
        if (strlen($this->rowIdentifierField)) $res = $row[$this->rowIdentifierField];
            else $res = $this->getStorage()->getIdentifierFromRow($row);
        return $res;
    }
    
    // --------------------------- in-memory records registry functions ---------------------------

    function find($fields, $strict = false, $newRecordsOnly = true) {
        $res = array();
        $src = $newRecordsOnly? array_intersect_key($this->recordsCollection, $this->keysOfNewRecords) : $this->recordsCollection;
        foreach ($src as $k => $object) {
            if ($object->matchesFields($fields, $strict)) $res[$k] = $object;
        }
        return $res;
    }
    
    function findRegisteredObject($object) {
        if (
            $this->lastCollectionObject === $object 
            && $this->lastCollectionKey !== null 
            && isset($this->recordsCollection[$this->lastCollectionKey]) 
            && $this->recordsCollection[$this->lastCollectionKey] === $object
        ) {
            $res = $this->lastCollectionKey;
        } else {
            $this->lastCollectionObject = $object;
            $id = $this->getIdentifierOfObject($object);
            if (isset($this->recordsCollection[$id]) && $this->recordsCollection[$id] === $object) $res = $id;
            else {
                $res = array_search($object, $this->recordsCollection, true);
                if ($res === false) $res = null;
            }
            $this->lastCollectionKey = $res;
        }
        return $res;
    }

    function setCollectionConflictMode($collectionConflictMode) {
        static $const = array(
            Ac_I_ObjectsCollection::CONFLICT_IGNORE_NEW,
            Ac_I_ObjectsCollection::CONFLICT_REMOVE_OLD,
            Ac_I_ObjectsCollection::CONFLICT_REMOVE_THROW
        );
        if (!in_array($collectionConflictMode, $const)) 
            throw Ac_E_InvalidCall::outOfConst ('collectionConflictMode', $collectionConflictMode, $const, 'Ac_I_ObjectsCollection');
        $this->collectionConflictMode = $collectionConflictMode;
    }

    function getCollectionConflictMode() {
        return $this->collectionConflictMode;
    }
    
    protected function handleCollectionConflict($id, $object) {
        if ($this->collectionConflictMode === Ac_I_ObjectsCollection::CONFLICT_REMOVE_OLD) {
            $this->lastCollectionKey = $id;
            $this->lastCollectionObject = $this->recordsCollection[$id];
            $this->unregisterObject($this->recordsCollection[$id]);
            $this->recordsCollection[$id] = $object;
            if (!strncmp($id, self::INSTANCE_ID_PREFIX, self::INSTANCE_ID_PREFIX_LENGTH))
                $this->keysOfNewRecords[$id] = true;
            $res = true;
        } elseif ($this->collectionConflictMode === Ac_I_ObjectsCollection::CONFLICT_THROW) {
            throw Ac_E_InvalidCall::alreadySuchItem('object', $id, 'unregisterObject');
        } else {
            $res = false;
        }
        return $res;
    }
    
    function registerOrActualizeObject($object, & $actualizeResult = Ac_I_ObjectsCollection::ACTUALIZE_NO_SUCH_OBJECT) {
        $id = $this->getIdentifierOfObject($object);
        $actualizeResult = Ac_I_ObjectsCollection::ACTUALIZE_NO_SUCH_OBJECT;
        $res = $id;
        if (isset($this->recordsCollection[$id])) {
            if ($this->recordsCollection[$id] === $object) {
                $actualizeResult = Ac_I_ObjectsCollection::ACTUALIZE_SAME;
                $reg = false;
            } else {
                $reg = $this->handleCollectionConflict($id, $object);
                if (!$reg) $res = false;
            }
        } else {
            if (null !== $otherId = $this->findRegisteredObject($object)) {
                $actualizeResult = $this->actualizeRegisteredObject($object);
                $reg = false;
            } else {
                $this->recordsCollection[$id] = $object;
                if (!strncmp($id, self::INSTANCE_ID_PREFIX, self::INSTANCE_ID_PREFIX_LENGTH))
                    $this->keysOfNewRecords[$id] = true;
                $reg = true;
            }
        }
        if ($reg && is_object($object) && $object instanceof Ac_I_CollectionAwareObject) {
            $object->notifyRegisteredInCollection($this);
        }
        return $res;
    }
    
    function unregisterObject($object) {
        $id = $this->findRegisteredObject($object);
        if ($id !== null) {
            $res = true;
            unset($this->recordsCollection[$id]);
            if (isset($this->keysOfNewRecords[$id]))
                unset($this->keysOfNewRecords[$id]);
            if (is_object($object) && $object instanceof Ac_I_CollectionAwareObject) {
                $object->notifyUnregisteredFromCollection($this);
            }
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * This method is a sinonym for unregisterAllObjects()
     * @see Ac_Model_Mapper::unregisterAllObjects()
     */
    function clearCollection() {
        return $this->unregisterAllObjects();
    }
    
    function unregisterAllObjects() {
        $tmp = $this->recordsCollection;
        $this->recordsCollection = array();
        $this->keysOfNewRecords = array();
        foreach ($tmp as $object) {
            if (is_object($object) && $object instanceof Ac_I_CollectionAwareObject) {
                $object->notifyUnregisteredFromCollection($this);
            }
        }
    }
    
    function getRegisteredObjects($identifiers = false) {
        $res = $this->recordsCollection;
        if ($identifiers !== false) {
            if (!is_array($identifiers)) {
                if (isset($res[$identifiers])) $res = $res[$identifiers];
                    else $res = null;
            } else {
                $res = array_intersect_key($res, array_flip($identifiers));
            }
        }
        return $res;
    }
    
    function getIdentifierOfObject($object) {
        $res = $this->getIdentifier($object);
        if (!strlen($res)) $res = self::INSTANCE_ID_PREFIX.$object->getModelObjectInstanceId();
        return $res;
    }
    
    function actualizeRegisteredObject ($object) {
        $id = $this->getIdentifierOfObject($object);
        if (isset($this->recordsCollection[$id]) && $this->recordsCollection[$id] === $object) {
            $res = Ac_I_ObjectsCollection::ACTUALIZE_SAME;
        } else {
            $currId = $this->findRegisteredObject($object);
            if ($currId === null) {
                $res = Ac_I_ObjectsCollection::ACTUALIZE_NO_SUCH_OBJECT;
            } else {
                unset($this->recordsCollection[$currId]);
                if (isset($this->keysOfNewRecords[$currId]))
                    unset($this->keysOfNewRecords[$currId]);
                if (isset($this->recordsCollection[$id])) {
                    if ($this->handleCollectionConflict($id, $object)) {
                        $res = Ac_I_ObjectsCollection::ACTUALIZE_ID_CHANGED;
                    } else {
                        $res = Ac_I_ObjectsCollection::ACTUALIZE_REMOVED;
                        if (is_object($object) && $object instanceof Ac_I_CollectionAwareObject) {
                            $object->notifyUnregisteredFromCollection($this);
                        }
                    }
                } else {
                    $res = Ac_I_ObjectsCollection::ACTUALIZE_ID_CHANGED;
                    $this->recordsCollection[$id] = $object;
                    if (!strncmp($id, self::INSTANCE_ID_PREFIX, self::INSTANCE_ID_PREFIX_LENGTH))
                        $this->keysOfNewRecords[$id] = true;
                }
            }
        }
        return $res;
    }
    
    function notifyCollectionObjectStage($object, $stage) {
        
        $collAware = is_object($object) && $object instanceof Ac_I_CollectionAwareObject;
        
        switch ($stage) {
            
            case Ac_I_LifecycleAwareCollection::STAGE_CREATED:
                if ($this->trackNewRecords) $this->registerOrActualizeObject ($object);
                elseif ($collAware && $object->isObjectCollectionRegistered($this)) 
                    $this->unregisterObject ($object);
                break;
                
            case Ac_I_LifecycleAwareCollection::STAGE_DELETED:
                if (!$collAware || $object->isObjectCollectionRegistered($this))
                    $this->unregisterObject($object);
                break;
                
            case Ac_I_LifecycleAwareCollection::STAGE_SAVED:
            case Ac_I_LifecycleAwareCollection::STAGE_LOADED:
            case Ac_I_LifecycleAwareCollection::STAGE_REVERTED:
                
                if ($this->useRecordsCollection) $this->registerOrActualizeObject($object);
                elseif (!$collAware || $object->isObjectCollectionRegistered($this))
                    $this->actualizeRegisteredObject($object);
                
        }
    }
    
    function __sleep() {
        $res = array_keys(get_object_vars($this));
        $res = array_diff($res, array('recordsCollection'));
        return $res;
    }
    
}