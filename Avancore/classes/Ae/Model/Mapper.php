<?php

class Ae_Model_Mapper implements Ae_I_Autoparams {
    
    protected $id = false;
    
    /**
     * @var Ae_Application
     */
    protected $application = false;

    /**
     * @var Ae_Legacy_Database
     */
    protected $database = false;
    
    protected $autoincFieldName = false;

    /**
     * @var Ae_Sql_Db
     */
    protected $sqlDb = false;

    var $tableName = null;

    var $recordClass = 'Ae_Model_Object';

    var $pk = null;

    var $_prototype = false;

    /**
     * Use records collection (records that already were loaded will not be loaded again)
     */
    var $useRecordsCollection = false;
     
    var $_recordsCollection = array();
    
    var $_fieldToSqlColMap = false;

    /**
     * Records that are not stored (only those that are created with Ae_Model_Mapper::factory() method).
     * Record will be removed from the array after it is stored.
     * This array is used to check uniqueness and find records by indices disregarding whether record is already stored or not.
     * To prevent deadlocks in case of two conflicting newly created records, first one gets right to be stored, second one will be considered invalid.
     *
     * @var array
     */
    var $_newRecords = array();

    /**
     * Relations that were created (used only if $this->remembersRelations())
     * @var array of Ae_Model_Relation
     */
    var $_relations = array();

    /**
     * @var array ('indexName' => array('fieldName1', 'fieldName2'), ...)
     */
    var $_indexData = false;

    var $useProto = false;

    var $_proto = array();

    var $_relationPrototypes = false;

    /**
     * @var Ae_Model_MapperInfo
     */
    var $_info = false;

    var $_titleFieldExpression = false;
    
    var $_validator = false;
    
    var $_updateMark = false;
    
    var $_updateLevel = 0;
    
    var $_dateFormats = false;
    
    protected $columnNames = false;
    
    protected $defaults = false;
    
    function Ae_Model_Mapper(array $options = array()) {
        if ($options) Ae_Autoparams::setObjectProperty ($this, $options);
/*     
        if (!$this->database) {
            throw new Exception("No \$database provided");
        }
*/

        if (!$this->tableName) trigger_error (__FILE__."::".__FUNCTION__." - tableName missing", E_USER_ERROR);
    }
    
    function getDefaults() {
        if ($this->defaults === false) {
            $this->getColumnNames();
        }
        return $this->defaults;
    }

    function setId($id) {
        if ($this->id !== false) throw new Exception("Can setId() only once!");
        $this->id = $id;
    }

    function getId() {
        if ($this->id === false) {
            $this->id = get_class($this);
            if ($this->id == 'Ae_Model_Mapper') {
                $this->id .= '_'.$this->tableName;
            }
        }
        return $this->id;
    }    

    function setApplication(Ae_Application $application) {
        $this->application = $application;
        if (!$this->database && !$this->sqlDb) {
            $this->setDatabase($this->application->getLegacyDatabase());
            $this->sqlDb = $this->application->getDb();
        }
    }

    /**
     * @return Ae_Application
     */
    function getApplication() {
        return $this->application;
    }

    function setDatabase(Ae_Legacy_Database $database) {
        $this->database = $database;
        if (!$this->sqlDb) $this->sqlDb = new Ae_Sql_Db_Ae($this->database);
        if (!strlen($this->pk)) {
            $dbi = $this->database->getInspector();
            $idxs = $dbi->getIndicesForTable($this->database->replacePrefix($this->tableName));
            $this->_indexData = array();
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
                    $this->_indexData[$name] = array_values($idx['columns']);
                }
            }
            if (!(is_array($this->pk) && $this->pk || strlen($this->pk))) trigger_error (__FILE__."::".__FUNCTION__." - pk missing", E_USER_ERROR);
        }
    }

    /**
     * @return Ae_Legacy_Database
     */
    function getDatabase() {
        return $this->database;
    }

    function setSqlDb(Ae_Sql_Db $sqlDb) {
        $this->sqlDb = $sqlDb;
    }

    /**
     * @return Ae_Sql_Db
     */
    function getSqlDb() {
        return $this->sqlDb;
    }
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Ae_Model_Mapper
     */
    function getMapper ($mapperClass, $application = null) {
        if (is_object($mapperClass) && $mapperClass instanceof Ae_Model_Mapper) {
            $res = $mapperClass;
        } else {
            $res = null;
            if ($application) {
                if ($application instanceof Ae_Application) {
                    $res = $application->getMapper($mapperClass);
                } else throw new Exception("\$application should be an Ae_Application instance");
            }
            foreach(Ae_Application::listInstances() as $className => $ids) {
                foreach ($ids as $appId) {
                    $app = Ae_Application::getInstance($className, $appId);
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
            $cols = $this->database->getInspector()->getColumnsForTable($this->database->replacePrefix($this->tableName));
            $this->defaults = array();
            foreach ($cols as $nm => $col) {
                $this->defaults[$nm] = $col['default'];
                if (isset($col['autoInc']) && $col['autoInc'] && ($this->autoincFieldName === false)) {
                    $this->autoincFieldName = $nm;
                }
            }
            $this->columnNames = array_keys($this->defaults);
        }
        return $this->columnNames;
    }

    /**
     * @return Ae_Model_Object
     */
    function & factory($className = false) {
        if ($className === false) $className = $this->recordClass;
        if ($this->useProto) {
            if (!isset($this->_proto[$className])) {
                $this->_proto[$className] = new $className($this);
            }
            $proto = & $this->_proto[$className];
            $res = $proto;
        } else {
            $res = new $this->recordClass($this);
        }
        $this->_memorize($res);
        return $res;
    }
    
    
    /**
     * @param array $values
     * @return Ae_Model_Object
     */
    function & reference($values = array()) {
        $res = & $this->factory();
        $res->setIsReference(true);
        foreach ($values as $k => $v) $res->{$k} = $v;
        return $res;
    }
    

    function listRecords() {
        $sql = "SELECT ".$this->database->NameQuote($this->pk)."  FROM ".$this->database->NameQuote($this->tableName);
        $this->database->setQuery($sql);
        $res = $this->database->loadResultArray();
        return $res;
    }

    /**
     * @param mixed id string or array; returns count of records that exist in the database;
     */
    function recordExists($ids) {
        if ($ids) {
            $sql = "SELECT COUNT(".$this->database->NameQuote($this->pk).") FROM ".$this->database->NameQuote($this->tableName)." WHERE $this->pk ".$this->_sqlEqCriteria($ids);
            $this->database->setQuery($sql);
            $res = intval($this->database->loadResult());
        } else $res = false;
        return $res;
    }

    /**
     * Loads record(s) -- id can be an array
     * @return Ae_Model_Object
     */
    function & loadRecord($id) {
        $res = null;
        if (is_array($id) && count($id) == 1) $id = array_pop($id);
        if (is_scalar($id) && strlen($id)) {
            if ($this->useRecordsCollection && isset($this->_recordsCollection[$id])) {
                $res = & $this->_recordsCollection[$id];
            } else {
                $sql = "SELECT * FROM {$this->tableName} WHERE {$this->pk} = ".$this->database->Quote($id);
                $this->database->setQuery($sql);
                $rows = $this->database->loadAssocList();
                if ($rows && $arrayRow = $rows[0]) {
                    $className = $this->getRecordClass ($arrayRow);
                    $record = new $className($this);
                    $record->load ($arrayRow, null, true);
                } else {
                    $record = null;
                }
                $res = & $record;
                if ($this->useRecordsCollection && $res) $this->_recordsCollection[$id] = & $res;
            }
        } else {
            if (is_array($id)) {
                $res = $this->loadRecordsArray($id);
            } else {
                trigger_error (__FILE__."::".__FUNCTION__."Unsupported \$id type: ".gettype($id).", scalar or array must be provided", E_USER_ERROR);
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
            $where = $this->database->NameQuote($this->pk)." ".$this->_sqlEqCriteria($ids);
            $recs = $this->loadRecordsByCriteria($where, true, $ordering);
            $res = array();


            /**
             * This helps to maintain records in order that was specified in $ids
             */
            if ($ordering === false)
            foreach ($ids as $id) {
                if (isset($recs[$id])) {
                    if ($keysToList) $res[$id] = $recs[$id];
                    else $res[] = & $recs[$id];
                }
            }
            else $res = $recs;

        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Ae_Model_Object
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
     * @return Ae_Model_Object
     */
    function loadSingleRecord($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        $arr = $this->loadRecordsByCriteria($where, $order, $joins, $limitOffset, $limitCount);
        if (count($arr) == 1) $res = $arr[0];
            else $res = null;
        return $res;
    }
    
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        $sql = "SELECT ".$this->database->NameQuote($this->tableName).".* FROM ".$this->database->NameQuote($this->tableName)." $joins  ";
        if ($where) $sql .= " WHERE ".$where;
        if ($order) $sql .= " ORDER BY ".$order;
        if (is_numeric($limitCount) && !is_numeric($limitOffset)) $limitOffet = false;
        if (is_numeric($limitCount)) {
            //$sql .= $this->database->getLimitsClause($limitCount, $limitOffset);
            $sql = $this->database->applyLimits($sql, $limitCount, $limitOffset, strlen($order)? $order : false);
        }
        
        $this->database->setQuery($sql);
        //$objectList = $this->database->loadAssocList();

        $result = $this->database->getResultResource();
        //var_dump($sql);
        
        $res =  array();

        while($row = $this->database->fetchAssoc($result)) {
            $recId = $row[$this->pk];

            if ($this->useRecordsCollection && isset($this->_recordsCollection[$recId])) {
                $rec = $this->_recordsCollection[$recId];
            } else {
                $className = $this->dispatcher->loadClass($this->getRecordClass($row));
                $rec = new $className ($this->database);
                $rec->load($row, null, true);
                if ($this->useRecordsCollection) $this->_recordsCollection[$recId] = $rec;
            }
            if ($keysToList) {
                $res[$rec->{$this->pk}] = $rec;
            } else {
                $res[] = $rec;
            }
        }
        $this->database->freeResultResource($result);

        return $res;
    }

    function getRecordClass($row) {
        $res = $this->recordClass;
        return $res;
    }

    /**
     * Helper function to create search criterias for Joomla searchbots
     *
     * @static
     * @param string $searchText text to search (words are split by whitespace); false to turn the search off
     * @param string $searchMode matching option (exact|any|all)
     */

    function getSearchCriteria ($fieldNames, $searchText, $searchMode) {
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
        $proto = & $this->getPrototype();
        return $proto->listPublicProperties();
    }

    /**
     * @return Ae_Model_Object
     */
    function getPrototype() {
        if ($this->_prototype === false) {
            $this->_prototype = & $this->factory();
        }
        return $this->_prototype;
    }

    // ----------------------------------- Cache support functions --------------------------------

    function getMtime() {
        return $this->database->getTime($this->tableName);
    }
    
    function getLastUpdateTime() {
    	return $this->getMtime();
    }
    
    function markUpdated() {
    	if (!$this->_updateLevel) {
    		$this->database->touch($this->tableName);
    		$this->_updateMark = false;
    	} else {
    		$this->_updateMark = true;
    	}
    }
    
    function beginUpdate() {
    	$this->_updateLevel++;
    }
    
    function endUpdate() {
    	if ($this->_updateLevel > 0) $this->_updateLevel--;
    	if (!$this->_updateLevel && $this->_updateMark) $this->markUpdated();
    }

    // -------------------------------- Metadata retrieval functions ------------------------------

    /**
     * @return Ae_Model_MapperInfo
     */
    function getInfo() {
        if ($this->_info === false) {
            $this->_info = new Ae_Model_MapperInfo(Ae_Util::fixClassName(get_class($this)), $this->_doGetInfoParams());
        }
        return $this->_info;
    }

    function _doGetInfoParams() {
        return array();
    }

    // --------------------------- in-memory records registry functions ---------------------------

    function _memorize(& $record) {
        $this->_newRecords[$record->_imId] = & $record;
        //var_dump("Memorizing (".get_class($this).") ".$record->_imId." / total ".count($this->_newRecords));
    }

    function _forget(& $record) {
        if (isset($this->_newRecords[$record->_imId])) {
            //var_dump("Forgetting (".get_class($this).") ".$record->_imId." / total ".count($this->_newRecords));
            unset($this->_newRecords[$record->_imId]);
        }
    }

    function _find($fields) {
        $res = array();
        foreach (array_keys($this->_newRecords) as $k) {
            if ($this->_newRecords[$k]->matchesFields($fields)) $res[$k] = & $this->_newRecords[$k];
        }
        return $res;
    }

    // --------------------- Functions that work with columns, keys and indices -------------------

    /**
     * @param mixed|array $keys One or more keys
     */
    function getKeysCriterion($keys, $tableAlias = false, $default = '0') {
        if (is_array($keys) && !count($keys)) return $default;
        $fieldName = $this->database->NameQuote($this->pk);
        if ($tableAlias !== false) $fieldName = $this->database->NameQuote($tableAlias).'.'.$fieldName;
        $res = $fieldName.$this->_sqlEqCriteria($keys);
        return $res;
    }

    function indexCrtieria($fields) {
        foreach ($fields as $f => $v) {
            $cr[] = $this->database->NameQuote($f).' = '.$this->database->Quote($v);
        }
        return "(".implode(" AND ", $cr).")";
    }

    function & locateRecord($fields, $where = false, $mustBeUnique = false, $searchNewRecords = false) {
        if (strlen($where)) $searchNewRecords = false;
        $crit = $this->indexCrtieria($fields);
        if (strlen($where)) $crit = "($crit) AND ($where)";
        $recs = $this->loadRecordsByCriteria($crit);
        //var_dump($crit);
        if ($searchNewRecords) {
            $newRecs = $this->_find($fields);
            foreach (array_keys($newRecs) as $k) $recs[] = & $newRecs[$k];
        }
        $res = null;
        if (count($recs)) {
            if (!$mustBeUnique || count($recs) == 1) $res = & $recs[0];
        }
        if (!$res) {
            //var_dump($fields, count($this->_newRecords));
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
            if (!strlen($this->_titleFieldExpression)) $titleFieldName = $this->getTitleFieldName();
            else $titleFieldName = $this->_titleFieldExpression;
        }
        if ($titleIsProperty == '?') $titleIsProperty = $this->isTitleAProperty();
        if (!$titleFieldName) {
            $pkf = $this->listPkFields();
            $titleFieldName = $pkf[0];
        }
        $qpkf = array();
        if ($valueFieldName === false)
        foreach ($this->listPkFields() as $pkf) $qpkf[] = $this->database->NameQuote('t').'.'.$this->database->NameQuote($pkf);
        else {
            $vf = $valueFieldName;
            if (!is_array($vf)) $vf = array($vf);
            foreach ($vf as $pkf) $qpkf[] = $this->database->NameQuote('t').'.'.$this->database->NameQuote($pkf);
        }
        $spk = count($qpkf) == 1;
        $qpkf = implode(", ", $qpkf);
        if (!$titleIsProperty && !$valueIsProperty) {
            $sql = "SELECT DISTINCT t.".$titleFieldName." AS _title_, ".$qpkf." FROM ".$this->database->NameQuote($this->tableName)." AS t";
            if ($extraJoins) $sql .= " ".$extraJoins;
            if ($where) $sql .= " WHERE ".$where;
            if ($ordering) $sql .= " ORDER BY ".$ordering;
            $this->database->setQuery($sql);
            $rr = $this->database->getResultResource();
            $res = array();
            while ($row = $this->database->fetchAssoc($rr)) {
                $title = $row['_title_'];
                $pk = Ae_Util::array_values(array_slice($row, 1));
                if ($spk) $pk = $pk[0];
                $res[] = array($pk, $title);
            }
        } else {
            $coll = new Ae_Model_Collection(get_class($this), false, $where, $ordering, $extraJoins);
            $coll->setSequential();
            $coll->useCursor();
            while ($rec = & $coll->getNext()) {
                if ($valueFieldName === false) $pk = & $rec->getPrimaryKey();
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
        if ($this->_indexData === false) $this->_indexData = $this->_doGetUniqueIndexData();
        return array_keys($this->_indexData);
    }

    function listUniqueIndexFields($indexName) {
        if (!in_array($indexName, $this->listUniqueIndices())) trigger_error("No such index: '{$indexName}'", E_USER_ERROR);
        return $this->_indexData[$indexName];
    }

    /**
     * @return array ('indexName' => array('fieldName1', 'fieldName2'), ...)
     */
    function _doGetUniqueIndexData() {
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
        if ($this->_indexData === false) $this->_indexData = $this->_doGetUniqueIndexData();
        if (!is_array($fieldNameOrNames)) $fieldNameOrNames = array($fieldNameOrNames);
        $res = false;
        foreach ($this->_indexData as $fieldsOfIndex) {
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
     * @param Ae_Model_Object $record
     * @param bool $dontReturnOwnKey If row with same PK as one of current instance is found, don't add it's PK to resultset
     * @param array usingIndices Names of indices to check (by default, all indices will be used)
     * @param array $customIndices ($indexName => array('key1', 'key2'...))
     * @param bool $withNewRecords Whether to check new records WITHOUT PKs that are stored in the memory. Note that in-memory comparsion is evaluated using different rules (see below)
     * @return array($indexName => array($pk1, $pk2...))
     * @see Ae_Model_Mapper::checkRecordUniqueness
     *
     * On comparsion with new records:
     * - since new records don't have primary keys, links of their instances will be returned instead of PKs
     */
    function checkRecordPresence(& $record, $dontReturnOwnKey = false, $usingIndices=array(), $customIndices=array(), $withNewRecords = false) {
    	$res = array();
        $pkCols = array();
        if (!$usingIndices) $usingIndices = array_merge($this->listUniqueIndices(), array_keys($customIndices));
        // If we don't have to return own key, it doesn't  matter whether we will find own instance by primary key or not
        // if ($dontReturnOwnKey) $usingIndices = array_diff($usingIndices, array('PRIMARY'));
        foreach ($this->listPkFields() as $pkf) $pkCols[] = $this->database->NameQuote($pkf);
        $cpk = count($pkCols) > 1;
        $pkCols = implode(", ", $pkCols);
        foreach ($usingIndices as $idxName) {
            $idxFields = isset($customIndices[$idxName])? $customIndices[$idxName] : $this->listUniqueIndexFields($idxName);
            $crit = $this->_indexCrtieria($record, $idxFields, true);
            if ($crit) {
                $sql = "SELECT ".$pkCols." FROM ".$this->database->NameQuote($this->tableName)." WHERE ".$crit;
                $this->database->setQuery($sql);
                $pks = $cpk? $this->database->loadAssocList() : $this->database->loadResultArray();
                if ($dontReturnOwnKey) {
                    foreach (array_keys($pks) as $i) if ($record->matchesPk($pks[$i])) unset($pks[$i]);
                }
                if ($pks) $res[$idxName] = $pks;
            }
            if ($withNewRecords) {
                $newRecords = $this->_find($record->getDataFields($idxFields));
                if ($dontReturnOwnKey && isset($newRecords[$record->_imId])) unset($newRecords[$record->_imId]);
                foreach (array_keys($newRecords) as $k) $res[$idxName][] = & $newRecords[$k];
            }
        }
        return $res;
    }
    
    // --------------- SQL to property mapping support (mostly template methods) ---------------

    /**
     * @return array('sqlCol1', 'sqlCol2'...)
     */
    function listSqlColumns() {
    	return array();
    }
    
    /**
     * @return array('sqlCol1', 'sqlCol2'...)
     */
    function listNullableSqlColumns() {
    	return array();
    }
    
    /**
     * @return array(modelFieldName => '{t}.colName', modelFieldName2 => 'SOME_FUNC({t}.colName1, {t}.colName2)'
     */
    function _doGetFieldToSqlColMap() {
    	return array();
    }
    
    /**
     * @return array(modelFieldName => '{t}.colName', modelFieldName2 => 'SOME_FUNC({t}.colName1, {t}.colName2)'
     */
    function getFieldToSqlColMap() {
    	if ($this->_fieldToSqlColMap === false) $this->_fieldToSqlColMap = $this->_doGetFieldToSqlColMap();
    	return $this->_fieldToSqlColMap;
    }
    
    // --------------- Function that work with associations and relations ---------------

    function listRelations() {
        if ($this->_relationPrototypes === false) $this->_relationPrototypes = $this->_getRelationPrototypes();
        return array_keys($this->_relationPrototypes);
    }

    function listIncomingRelations() {
        if ($this->_relationPrototypes === false) $this->_relationPrototypes = $this->_getRelationPrototypes();
        $res = array();
        foreach ($this->_relationPrototypes as $n => $p) {
            if (!isset($p['srcOutgoing']) || !$p['srcOutgoing']) $res[] = $n;
        }
        return $res;
    }

    function listOutgoingRelations() {
        $res = array_diff($this->listRelations(), $this->listIncomingRelations());
        return $res;
    }

    function getRelationPrototype($relId) {
        if (!in_array($relId, $this->listRelations())) trigger_error ("No such relation: '{$relId}' in mapper ".get_class($this), E_USER_ERROR);
        return $this->_relationPrototypes[$relId];
    }

    /**
     * @return Ae_Model_Relation
     */
    function & createRelation($relId) {
        $proto = $this->getRelationPrototype($relId);
        
        // Replace mapper classes with mapper instances, if possible
        if ($this->application) $proto['application'] = $this->application;
        if (isset($proto['srcMapperClass']) && $proto['srcMapperClass'] == $this->getId()) {
            $proto['srcMapper'] = $this;
        } elseif (isset($proto['destMapperClass']) && $proto['destMapperClass'] == $this->getId()) {
            $proto['destMapper'] = $this;
        }
        $res = & Ae_Model_Relation::factory($proto);
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
     * @return Ae_Model_Relation
     */
    function getRelation($relId) {
        if (!isset($this->_relations[$relId])) {
            $res = & $this->createRelation($relId);
            if ($this->remembersRelations()) $this->_relations[$relId] = & $res;
        } else $res = & $this->_relations[$relId];
        return $res;
    }

    // TODO: make this work!
    /*
     function loadRelationCascade(& $left, $relations) {
     if (!is_array($relations)) $relations = array($relations);
     $na = func_num_args();
     if ($na > 2) for ($i = 2; $i < $na - 1; $i++) $relations[] = func_get_arg($i);
     $m = & $this;
     $curr = & $left;
     foreach ($relations as $r) {
     $relation = & $m->getRelation($r);
     $recs = $relation->loadDest($curr);
     $curr = Ae_Util::flattenArray($recs);
     $m = & Ae_Model_Mapper::getMapper($relation->destMapperClass);
     }
     }
     */

    function _getRelationPrototypes() {
        return array();
    }

    // TODO: Add suport for records that are in random access collections (invoke methods listAssocFor(), loadAssocFor(), countAssocFor() for all records of collection)
    function loadAssocFor (& $record, $relId) {
        $rel = & $this->getRelation($relId);
        $rel->loadDest($record);
    }

    /**
     * Loads associated records keys for $record
     * Currently this function works same as loadAssocFor()
     */
    function listAssocFor (& $record, $relId) {
        $rel = & $this->getRelation($relId);
        $rel->loadDest($record);
    }

    function loadAssocCountFor (& $record, $relId) {
        $rel = & $this->getRelation($relId);
        $rel->loadDestCount($record);
    }

    function loadAssocNNIdsFor (& $record, $relId) {
        $rel = & $this->getRelation($relId);
        $rel->loadDestNNIds($record);
    }
    
    function canDelete (& $record) {
        // TODO: canDelete()
    }

    function beforeDelete (& $record) {
        // TODO: beforeDelete()
    }

    // ----------------- Functions that work with arrays of records -------------

    /**
     * @param Ae_Model_Object $record
     * @param array $dest
     */
    function putToArrayByPk(& $record, & $dest) {
        $dest[$record->{$this->pk}] = & $record;
    }

    /**
     * @param array $src
     * @param string $pk
     * @return Ae_Model_Object or $default if it is not found
     */
    function getFromArrayByPk(& $src, $pk, $default = null) {
        $res = $default;
        if (isset($src[$pk])) $res = $src[$pk];
        return $res;
    }

    /**
     * @param array $src
     * @return array(array($pk1, & $rec1), array($pk2, & $rec2), ...)
     */
    function getFlatArrayWithPks(& $src) {
        $res = array();
        foreach (array_keys($src) as $pk) {
            $res[] = array($pk, & $src[$pk]);
        }
        return $res;
    }
     
    // ----------------- Functions that work with records -----------------

    // ----------------- Supplementary functions -----------------


    /**
     * @param Ae_Model_Object $record
     * @return string|false
     */
    function _indexCrtieria(& $record, $fieldNames, $mustBeFull) {
        $vals = $record->getDataFields($fieldNames, !$mustBeFull);
        if ($mustBeFull && (count($vals) < count($fieldNames))) return false;
        $cr = array();
        foreach ($fieldNames as $fn) {
            $cr[] = $this->database->NameQuote($fn).' = '.$this->database->Quote($vals[$fn]);
        }
        return "(".implode(" AND ", $cr).")";
    }

    /**
     * @param mixed ids Scalar or Array
     * @returns string " = 'Scalar'" or " IN ('Array[1]', 'Array[2]', 'Array[3]')";
     */
    function _sqlEqCriteria($ids) {
        if (!is_array($ids)) {
            $res = " = ".$this->database->Quote($ids);
        } else {
            foreach ($ids as $i => $id) $ids[$i] = $this->database->Quote($id);
            $ids = array_unique($ids);
            $res = " IN (".implode(", ", $ids).")";
        }
        return $res;
    }

    function _quoteNames($fieldNames) {
        $res = array();
        foreach($fieldNames as $fieldName) $res[] = $this->database->NameQuote($fieldName);
        return $res;
    }
    
    /**
     * @return Ae_Model_Validator
     */
    function getCommonValidator() {
        if ($this->_validator === false) {
            $this->_validator = new Ae_Model_Validator($this->getPrototype(), $this->getPrototype()->getOwnPropertiesInfo());
        }
        return $this->_validator;
    }
    
    /**
	 * @return Parameter $columnFormats for Ae_Legacy_Database::convertDates
     */
    function getDateFormats() {
        if ($this->_dateFormats === false) {
            $this->_dateFormats = array();
            $p = & $this->getPrototype();
            foreach ($p->listOwnFields(true) as $f) {
                $pi = $p->getPropertyInfo($f, true);
                if (strlen($pi->internalDateFormat)) $this->_dateFormats[$f] = $pi->internalDateFormat;
            }
        }
        return $this->_dateFormats;
    }
    
}

?>