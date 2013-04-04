<?php

class Ac_Model_Mapper implements Ac_I_Prototyped {
    
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
     
    protected $recordsCollection = array();
    
    protected $fieldToSqlColMap = false;

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
    
    function Ac_Model_Mapper(array $options = array()) {
        if ($options) Ac_Accessor::setObjectProperty ($this, $options);
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
    static function getMapper ($mapperClass, $application = null) {
        if (is_object($mapperClass) && $mapperClass instanceof Ac_Model_Mapper) {
            $res = $mapperClass;
        } else {
            $res = null;
            if ($application) {
                if ($application instanceof Ac_Application) {
                    $res = $application->getMapper($mapperClass);
                } else throw new Exception("\$application should be an Ac_Application instance");
            }
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
     * @return Ac_Model_Object
     */
    function factory($className = false) {
        if ($className === false) $className = $this->recordClass;
        if ($this->useProto) {
            if (!isset($this->proto[$className])) {
                $this->proto[$className] = new $className($this);
            }
            $proto = $this->proto[$className];
            $res = $proto;
        } else {
            $res = new $this->recordClass($this);
        }
        $this->memorize($res);
        return $res;
    }
    
    
    /**
     * @param array $values
     * @return Ac_Model_Object
     */
    function reference($values = array()) {
        $res = $this->factory();
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
            $this->prototype = $this->factory();
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
            $this->info = new Ac_Model_MapperInfo(Ac_Util::fixClassName(get_class($this)), $this->doGetInfoParams());
        }
        return $this->info;
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
        //var_dump($crit);
        if ($searchNewRecords) {
            $newRecs = $this->find($fields);
            foreach (array_keys($newRecs) as $k) $recs[] = $newRecs[$k];
        }
        $res = null;
        if (count($recs)) {
            if (!$mustBeUnique || count($recs) == 1) $res = $recs[0];
        }
        if (!$res) {
            //var_dump($fields, count($this->newRecords));
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
    protected function doGetFieldToSqlColMap() {
    	return array();
    }
    
    /**
     * @return array(modelFieldName => '{t}.colName', modelFieldName2 => 'SOME_FUNC({t}.colName1, {t}.colName2)'
     */
    function getFieldToSqlColMap() {
    	if ($this->fieldToSqlColMap === false) $this->fieldToSqlColMap = $this->doGetFieldToSqlColMap();
    	return $this->fieldToSqlColMap;
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

    protected function getRelationPrototype($relId) {
        if (!in_array($relId, $this->listRelations())) trigger_error ("No such relation: '{$relId}' in mapper ".get_class($this), E_USER_ERROR);
        return $this->relationPrototypes[$relId];
    }

    /**
     * @return Ac_Model_Relation
     */
    function createRelation($relId) {
        $proto = $this->getRelationPrototype($relId);
        
        // Replace mapper classes with mapper instances, if possible
        if ($this->application) $proto['application'] = $this->application;
        if (isset($proto['srcMapperClass']) && $proto['srcMapperClass'] == $this->getId()) {
            $proto['srcMapper'] = $this;
        } elseif (isset($proto['destMapperClass']) && $proto['destMapperClass'] == $this->getId()) {
            $proto['destMapper'] = $this;
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

    protected function getRelationPrototypes() {
        return array();
    }

    // TODO: Add suport for records that are in random access collections (invoke methods listAssocFor(), loadAssocFor(), countAssocFor() for all records of collection)
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
    
    function canDelete ($record) {
        // TODO: canDelete()
    }

    function beforeDelete ($record) {
        // TODO: beforeDelete()
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
            $this->validator = new Ac_Model_Validator($this->getPrototype(), $this->getPrototype()->getOwnPropertiesInfo());
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
            foreach ($p->listOwnFields(true) as $f) {
                $this->dateFormats[$dataTypes] = array();
                $pi = $p->getPropertyInfo($f, true);
                if ($dataTypes) {
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
    function peLoad($primaryKey, & $error = null) {
        $data = $this->db->fetchRow('SELECT * FROM '.$this->db->n($this->tableName).' WHERE '.$this->pk.' = '.$this->db->q($primaryKey));
        return $data;
    }

    /**
     * @param mixed $hyData Persistence data
     * @return mixed persistence data on success, FALSE on failure
     */
    function peSave($hyData, $exists = null, & $error = null) {
        if (is_null($exists)) $exists = array_key_exists($this->pk, $hyData);
        if ($exists) {
            $query = $this->db->updateStatement($this->tableName, $hyData, $this->pk, false);
            if ($this->db->query($query)) $res = $hyData;
                else $res = false;
        } else {
            $query = $this->db->insertStatement($this->tableName, $hyData);
            if ($this->db->query($query)) {
                if (strlen($this->autoincFieldName)) {
                    $hyData[$this->autoincFieldName] = $this->db->getLastInsertId();
                }
                $res = $hyData;
            } else {
                $res = false;
            }
        }
        return $res;
    }
    
    /**
     * @param type $hyData 
     * @return bool
     */
    function peDelete($hyData, & $error = null) {
        $key = $hyData[$this->pk];
        $res = (bool) $this->db->query("DELETE FROM ".$this->db->n($this->tableName)." WHERE ".$this->db->n($this->pk)." ".$this->db->eqCriterion($key));
        return $res;
    }
    
    function peConvertForLoad($hyData) {
        $res = $hyData;
        $d = $this->db->getDialect();
        if ($d->hasToConvertDatesOnLoad()) {
            $res = $this->convertDates($oid, $this->getDateFormats()); 
        }
        return $res;
    }
    
    function peConvertForSave($hyData) {
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
    
    function peReplaceNNRecords($rowProto, $rows, $midTableName, & $errors = array()) {
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
    
    function getAllRecords($key = false) {
        if ($this->allRecords === false) {
            if (strlen($t = $this->getTitleFieldName())) $ord = $t.' ASC';
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
    
    function getSqlSelectPrototype($primaryAlias = 't') {
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
    
    function getManagerConfig() {
        return array();
    }
    
}
    
    

