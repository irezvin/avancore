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
     * function onBeforeStoreRecord($record, array & $hyData, array & $newData, & $exists, & $result, 
     *                              & $error)
     */
    const EVENT_BEFORE_STORE_RECORD = 'onBeforeStoreRecord';

    /**
     * function onAfterStoreRecord($record, array & $hyData, array & $newData, & $exists, & $result, & $error)
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
     * function onRelationNotFound($id, & $res)
     */
    const EVENT_ON_RELATION_NOT_FOUND = 'onRelationNotFound';
    
    /**
     * function onUpdated()
     */
    const EVENT_ON_UPDATED = 'onUpdated';

    protected $id = false;
    
    /**
     * @var Ac_Application
     */
    protected $application = false;

    /**
     * @var Ac_Legacy_Database
     */
    protected $database = false;
    
    protected $autoincFieldName = false;

    /**
     * @var Ac_Sql_Db
     */
    protected $db = false;

    var $tableName = null;

    var $recordClass = 'Ac_Model_Object';

    var $pk = null;
    
    protected $prototype = false;

    /**
     * Use records collection (records that already were loaded will not be loaded again)
     */
    var $useRecordsCollection = false;
    
    var $nullableSqlColumns = array();
     
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
    protected $relations = array();

    /**
     * @var array ('indexName' => array('fieldName1', 'fieldName2'), ...)
     */
    protected $indexData = false;

    var $useProto = false;

    protected $proto = array();

    protected $relationPrototypes = false;

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
    
    protected $columnNames = false;
    
    protected $defaults = false;
    
    protected $allRecords = false;
    
    function __construct(array $options = array()) {
        parent::__construct($options);
        if (!$this->tableName) trigger_error (__FILE__."::".__FUNCTION__." - tableName missing", E_USER_ERROR);
    }
    
    function getDefaults() {
        if ($this->defaults === false) {
            $this->getColumnNames();
        }
        return $this->defaults;
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
        if (!$this->database && !$this->db) {
            //$this->setDatabase($this->application->getLegacyDatabase());
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
    final function createRecord($className = false) {
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
        $res = $this->db->fetchColumn("SELECT ".$this->db->n($this->pk)."  FROM ".$this->db->n($this->tableName));
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
        if (is_scalar($id) && strlen($id)) {
            if ($this->useRecordsCollection && isset($this->recordsCollection[$id])) {
                $res = $this->recordsCollection[$id];
            } else {
                $sql = "SELECT * FROM ".$this->db->n($this->tableName)." WHERE ".$this->db->n($this->pk)." = ".$this->db->q($id);
                $rows = $this->db->fetchArray($sql);
                if ($rows && $arrayRow = $rows[0]) {
                    $className = $this->getRecordClass ($arrayRow);
                    $record = new $className($this);
                    $record->load ($arrayRow, true);
                } else {
                    $record = null;
                }
                $res = $record;
                if ($this->useRecordsCollection && $res) $this->recordsCollection[$id] = $res;
            }
        } else {
            if (is_array($id)) {
                $res = $this->loadRecordsArray($id);
            } else {
                trigger_error (__FILE__."::".__FUNCTION__."Unsupported \$id type: ".gettype($id).", scalar or array must be provided, ".Ac_Util::typeClass($id)." given", E_USER_ERROR);
            }
        }
        return $res;
    }

    /**
     * @param array ids - Array of record identifiers
     */
    function loadRecordsArray($ids, $keysToList = false, $ordering = false) {
        if (!is_array($ids)) trigger_error (__FILE__."::".__FUNCTION__.'$ids must be an array', E_USER_ERROR);
        if ($ids) {
            $where = $this->db->n($this->pk)." ".$this->db->eqCriterion($ids);
            $recs = $this->loadRecordsByCriteria($where, true, $ordering);
            $res = array();


            /**
             * This helps to maintain records in order that was specified in $ids
             */
            if ($ordering === false)
            foreach ($ids as $id) {
                if (isset($recs[$id])) {
                    if ($keysToList) $res[$id] = $recs[$id];
                    else $res[] = $recs[$id];
                }
            }
            else $res = $recs;

        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * Loads records into already loaded rows that reference current table' objects by the key
     * Does not work with objects and will overwrite values in $src[$i][$valueProperty]
     * 
     * @param array $src Array of arrays
     * @param string $keyProperty Key in $src elements that contains record key
     * @param string $valueProperty Key in $dest elements that will contain record
     * @param mixed $def Value to use if related object isn't found
     * @return array All objects loaded, indexed by their IDs (as in loadRecrodsArray())
     */
    function loadObjectsColumn(& $src, $keyProperty, $valueProperty, $def = null) {
        $ids = array();
        $objects = array();
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
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false) {
        $arr = $this->loadRecordsByCriteria($where, false, $order, $joins, $limitOffset, 1);
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
    function loadSingleRecord($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        $arr = $this->loadRecordsByCriteria($where, $order, $joins, $limitOffset, $limitCount);
        if (count($arr) == 1) $res = $arr[0];
            else $res = null;
        return $res;
    }

    /**
     * @param array $rows Array of associative DB rows
     * @return array
     */
    function loadFromRows(array $rows) {
        $res = array();
        $c = $this->recordClass;
        foreach ($rows as $i => $row) {
            $rec = new $c ($this);
            $rec->load($row, true);
            $res[$i] = $rec;
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
        
        $res = array();

        foreach($this->db->fetchArray($sql) as $row) {
            $recId = $row[$this->pk];

            if ($this->useRecordsCollection && isset($this->recordsCollection[$recId])) {
                $rec = $this->recordsCollection[$recId];
            } else {
                $className = $this->getRecordClass($row);
                $rec = new $className ($this);
                $rec->load($row, true);
                if ($this->useRecordsCollection) $this->recordsCollection[$recId] = $rec;

            }
            if ($keysToList) {
                $res[$rec->{$this->pk}] = $rec;
            } else {
                $res[] = $rec;
            }
        }
        return $res;
    }
    
    function getRecordClass($row) {
        $res = $this->recordClass;
        return $res;
    }

    /**
     * Helper function to create search criterias for Joomla searchbots
     *
     * @param string $searchText text to search (words are split by whitespace); false to turn the search off
     * @param string $searchMode matching option (exact|any|all)
     */

    static function getSearchCriteria ($fieldNames, $searchText, $searchMode) {
        if (!in_array($searchMode, array('exact', 'any', 'all'))) {
            trigger_error ('Invalid searchMode, \'exact\'|\'any\'|\'all\' expected, assuming \'exact\'', E_USER_WARNING);
            $searchMode = 'exact';
        }

        $where = array();
        if ($searchMode == 'exact') {
            foreach ($fieldNames as $fieldName) {
                $where[] = " LOWER($fieldName) LIKE LOWER('%$searchText%') ";
            }
            $res = implode (' OR ', $where);
        } else {
            $words = preg_split('/\\s+/', $searchText);
            $where2 = array();
            foreach ($words as $word) {
                foreach ($fieldNames as $fieldName) {
                    $where2[] = " LOWER($fieldName) LIKE LOWER('%$searchText%') ";
                }
                $where[] = '('.implode (' OR ', $where2).')';
            }
            if ($searchMode == 'any') $res = implode(' OR ', $where);
            else $res = implode(' AND ', $where);
        }
        if (strlen($res)) $res = '('.$res.')';
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
            $this->triggerEvent(self::EVENT_ON_UPDATED);
    		$this->application->getFlags()->touch('mapper.'.$this->id);
    		$this->updateMark = false;
    	} else {
    		$this->updateMark = true;
    	}
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

    function memorize($record) {
        $this->newRecords[$record->_imId] = $record;
    }

    function forget($record) {
        if (isset($this->newRecords[$record->_imId])) {
            unset($this->newRecords[$record->_imId]);
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
            if (!is_array($vf)) $vf = array($vf);
            foreach ($vf as $pkf) $qpkf[] = $this->db->n('t').'.'.$this->db->n($pkf);
        }
        $spk = count($qpkf) == 1;
        $qpkf = implode(", ", $qpkf);
        if (!$titleIsProperty && !$valueIsProperty) {
            $sql = "SELECT DISTINCT t.".$titleFieldName." AS _title_, ".$qpkf." FROM ".$this->db->n($this->tableName)." AS t";
            if ($extraJoins) $sql .= " ".$extraJoins;
            if ($where) $sql .= " WHERE ".$where;
            if ($ordering) $sql .= " ORDER BY ".$ordering;
            $res = array();
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
                    $pk = array();
                    if ($valueIsProperty) foreach ($vf as $f) $pk[] = $rec->getField($f);
                    else foreach ($vf as $f) $pk[] = $rec->$f;
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
        if ($this->indexData === false) $this->indexData = $this->doGetUniqueIndexData();
        return array_keys($this->indexData);
    }

    function listUniqueIndexFields($indexName) {
        if (!in_array($indexName, $this->listUniqueIndices())) trigger_error("No such index: '{$indexName}'", E_USER_ERROR);
        return $this->indexData[$indexName];
    }

    /**
     * @return array ('indexName' => array('fieldName1', 'fieldName2'), ...)
     */
    protected function doGetUniqueIndexData() {
        return array();
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
        if ($this->indexData === false) $this->indexData = $this->doGetUniqueIndexData();
        if (!is_array($fieldNameOrNames)) $fieldNameOrNames = array($fieldNameOrNames);
        $res = false;
        foreach ($this->indexData as $fieldsOfIndex) {
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
        if ($this->relationPrototypes === false) $this->relationPrototypes = $this->getRelationPrototypes();
        return array_keys($this->relationPrototypes);
    }

    function listIncomingRelations() {
        if ($this->relationPrototypes === false) $this->relationPrototypes = $this->getRelationPrototypes();
        $res = array();
        foreach ($this->relationPrototypes as $n => $p) {
            if (!isset($p['srcOutgoing']) || !$p['srcOutgoing']) $res[] = $n;
        }
        return $res;
    }

    function listOutgoingRelations() {
        $res = array_diff($this->listRelations(), $this->listIncomingRelations());
        return $res;
    }

    protected final function getRelationPrototype($relId) {
        if (!in_array($relId, $this->listRelations())) {
            $res = null;
            $this->triggerEvent(self::EVENT_ON_RELATION_NOT_FOUND, array(
                $relId, & $res
            ));
            if (!$res)
                trigger_error ("No such relation: '{$relId}' in mapper ".get_class($this), E_USER_ERROR);
        } else {
            $res = $this->relationPrototypes[$relId];
        }
        return $res;
    }

    /**
     * @return Ac_Model_Relation
     */
    function createRelation($relId) {
        $proto = $this->getRelationPrototype($relId);
        if (is_object($proto) && $proto instanceof Ac_Model_Relation) {
            $proto->setApplication($this->application);
            if ($proto->srcMapperClass == $this->getId()) {
                $proto->setSrcMapper($this);
            } elseif ($proto->destMapperClass == $this->getId()) {
                $proto->setDestMapper($this);
            }
            $res = $proto;
        } else {
            // Replace mapper classes with mapper instances, if possible
            if ($this->application) $proto['application'] = $this->application;
            if (isset($proto['srcMapperClass']) && $proto['srcMapperClass'] == $this->getId()) {
                $proto['srcMapper'] = $this;
            } elseif (isset($proto['destMapperClass']) && $proto['destMapperClass'] == $this->getId()) {
                $proto['destMapper'] = $this;
            }
        }
        $res = Ac_Model_Relation::factory($proto);
        return $res;
    }

    /**
     * @return bool Whether Mapper has to store created relations
     *
     * This function can be overridden in child classes.
     */
    function remembersRelations() {
        return false;
    }

    /**
     * @return Ac_Model_Relation
     */
    function getRelation($relId) {
        if (!isset($this->relations[$relId])) {
            $res = $this->createRelation($relId);
            if ($this->remembersRelations()) $this->relations[$relId] = $res;
        } else $res = $this->relations[$relId];
        return $res;
    }

    // TODO: make this work!
    /*
     function loadRelationCascade($left, $relations) {
     if (!is_array($relations)) $relations = array($relations);
     $na = func_num_args();
     if ($na > 2) for ($i = 2; $i < $na - 1; $i++) $relations[] = func_get_arg($i);
     $m = $this;
     $curr = $left;
     foreach ($relations as $r) {
     $relation = $m->getRelation($r);
     $recs = $relation->loadDest($curr);
     $curr = Ac_Util::flattenArray($recs);
     $m = Ac_Model_Mapper::getMapper($relation->destMapperClass);
     }
     }
     */

    protected function doGetRelationPrototypes() {
        return array();
    }

    final function getRelationPrototypes() {
        if ($this->relationPrototypes === false) {
            $this->relationPrototypes = $this->doGetRelationPrototypes();
            $this->triggerEvent(self::EVENT_ON_GET_RELATION_PROTOTYPES, array(
                & $this->relationPrototypes
            ));
        }
        return $this->relationPrototypes;
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
    
    // ----------------- Functions that work with arrays of records -------------

    /**
     * @param Ac_Model_Object $record
     * @param array $dest
     */
    function putToArrayByPk(& $record, $dest) {
        $dest[$record->{$this->pk}] = $record;
    }

    /**
     * @param array $src
     * @param string $pk
     * @return Ac_Model_Object or $default if it is not found
     */
    function getFromArrayByPk($src, $pk, $default = null) {
        $res = $default;
        if (isset($src[$pk])) $res = $src[$pk];
        return $res;
    }

    /**
     * @param array $src
     * @return array(array($pk1, & $rec1), array($pk2, & $rec2), ...)
     */
    function getFlatArrayWithPks($src) {
        $res = array();
        foreach (array_keys($src) as $pk) {
            $res[] = array($pk, & $src[$pk]);
        }
        return $res;
    }
     
    // ----------------- Functions that work with records -----------------

    // ----------------- Supplementary functions -----------------


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
            throw new Ac_E_InvalidUsage("\$rec is of class ".get_class($rec).", must be an instance of ".$this->recordClass." to be supported by persistence functions of ".get_class($this));
        }
        return $res;
    }
    
    /**
     * @param mixed $primaryKey
     * @return array with persistence data | FALSE if record not found
     */
    function peLoad($record, $primaryKey, & $error = null) {
        $data = $this->db->fetchRow('SELECT * FROM '.$this->db->n($this->tableName).' WHERE '.$this->pk.' = '.$this->db->q($primaryKey));
        return $data;
    }

    protected function corePeSave($record, & $hyData, & $exists = null, & $error = null, & $newData = array()) {
        if (is_null($exists)) $exists = array_key_exists($this->pk, $hyData);
        if ($exists) {
            $query = $this->db->updateStatement($this->tableName, $hyData, $this->pk, false);
            if ($this->db->query($query) !== false) $res = $hyData;
            else {
                $descr = $this->db->getErrorDescr();
                if (is_array($descr)) $descr = implode("; ", $descr);
                $error = $this->db->getErrorCode().': '.$descr;
                $res = false;
            }
        } else {
            $query = $this->db->insertStatement($this->tableName, $hyData);
            if ($this->db->query($query)) {
                if (strlen($ai = $this->getAutoincFieldName()) && !isset($hyData[$ai])) {
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
        $res = (bool) $this->db->query("DELETE FROM ".$this->db->n($this->tableName)." WHERE ".$this->db->n($this->pk)." ".$this->db->eqCriterion($key));
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
            if (strlen($t = $this->getTitleFieldName()) && !$this->isTitleAProperty()) $ord = $t.' ASC';
            elseif (($o = $this->getDefaultOrdering()) !== false) $ord = $o;
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
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = array(
			'tables' => array(
				$primaryAlias => array(
					'name' => $this->tableName, 
				),
			),
			'tableProviders' => array(
				'model' => array(
					'class' => 'Ac_Model_Sql_TableProvider',
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
    
    
}