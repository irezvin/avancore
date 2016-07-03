<?php

class Ac_Model_Storage_MonoTable extends Ac_Model_Storage_Sql implements Ac_I_WithSqlSelectPrototype {

    /**
     * Special name of the criterion to provide the Ac_Sql_Select instance or its' "prototype override"
     * to the Ac_Model_Storage_MonoTable instead of using the built-in one
     */
    const QUERY_SQL_SELECT = '_query_sql_select_';
    
    /**
     * name of SQL table that contains records
     */
    protected $tableName = false;

    /**
     * class of the record
     */
    protected $recordClass = false;

    /**
     * name of auto-increment table field
     */
    protected $autoincFieldName = false;
    
    protected $identifierField = '_peIdentifier';

    protected $setRowIdentifierToPk = true;
    
    protected $map = array();
    
    /**
     * Sets name of SQL table that contains records
     */
    function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    /**
     * Returns name of SQL table that contains records
     */
    function getTableName() {
        return $this->tableName;
    }
    
    function getIdentifier($object) {
        return $object->_peIdentifier;
    }

    /**
     * Sets class of the record
     */
    function setRecordClass($recordClass) {
        $this->recordClass = $recordClass;
    }

    /**
     * Returns class of the record
     */
    function getRecordClass() {
        return $this->recordClass;
    }

    /**
     * Sets primary key of the table
     */
    function setPrimaryKey($primaryKey) {
        $this->primaryKey = $primaryKey;
    }

    /**
     * Returns primary key of the table
     */
    function getPrimaryKey() {
        return $this->primaryKey;
    }

    /**
     * Sets name of auto-increment table field
     */
    function setAutoincFieldName($autoincFieldName) {
        $this->autoincFieldName = $autoincFieldName;
    }

    /**
     * Returns name of auto-increment table field
     */
    function getAutoincFieldName() {
        return $this->autoincFieldName;
    }
    
    function listGeneratedFields() {
        return array($this->autoincFieldName);
    }
    
    
    function createRecord($typeId = false) {
        if ($typeId !== false)
            throw new Ac_E_InvalidCall("Only default \$typeId (FALSE) "
                . "is supported by ".__CLASS__."; '$typeId' given");
        $className = $this->recordClass;
        $res = new $className($this->mapper);
        return $res;
    }
    
    function listRecords() {
        $ord = $this->mapper->getDefaultOrdering();
        if (strlen($ord)) $orderClause = "\nORDER BY $ord";
        else $orderClause = "";
        $res = $this->db->fetchColumn(
            "SELECT ".$this->db->n($this->primaryKey)
            ."\nFROM ".$this->db->n($this->tableName)
            .$orderClause
        );
        return $res;
    }
    
    function recordExists($idOrIds) {
        $res = (int) $this->db->fetchValue("SELECT COUNT(".$this->db->n($this->primaryKey).") FROM ".$this->db->n($this->tableName)." WHERE $this->primaryKey ".$this->db->eqCriterion($idOrIds));
        return $res;
    }
    
    function loadRecord($id) {
        $sql = "SELECT * FROM ".$this->db->n($this->tableName)." WHERE ".$this->db->n($this->primaryKey)." = ".$this->db->q($id)." LIMIT 1";
        $rows = $this->db->fetchArray($sql);
        $res = null;
        if (count($rows)) {
            $objects = $this->loadFromRows($rows);
            $res = array_pop($objects);
        }
        return $res;
    }
    
    function loadRecordsArray(array $ids) {
        $where = $this->db->n($this->primaryKey)." ".$this->db->eqCriterion($ids);
        $res = $this->loadRecordsByCriteria($where, true);
        return $res;
    }
    
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        $arr = $this->loadRecordsByCriteria($where, $order, $joins, $limitOffset, 1, $tableAlias);
        if (count($arr)) $res = array_shift($arr);
            else $res = null;
        return $res;
    }
    
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        $arr = $this->loadRecordsByCriteria($where, $order, $joins, $limitOffset, 2, $tableAlias);
        if (count($arr) == 1) $res = array_shift($arr);
            else $res = null;
        return $res;
    }
    
    function loadRecordsByCriteria($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        $tableName = $this->db->n($this->tableName);
        if (strlen($tableAlias)) {
            $tableAlias = $this->db->n($tableAlias);
            $tn = $tableAlias;
        } else {
            $tn = $tableName;
        }
        $sql = "SELECT {$tn}.* FROM {$tableName} {$tableAlias} $joins";
        if ($where) {
            if (is_array($where)) $where = $this->db->valueCriterion($where);
            $sql .= " WHERE ".$where;
        }
        if ($order) $sql .= " ORDER BY ".$order;
        if (is_numeric($limitCount) && !is_numeric($limitOffset)) $limitOffset = false;
        if (is_numeric($limitCount)) {
            $sql = $this->db->applyLimits($sql, $limitCount, $limitOffset, strlen($order)? $order : false);
        }
        $res = $this->loadFromRows($this->db->fetchArray($sql));
        return $res;
    }
    
    function groupRowsByIdentifiers(array $rows) {
        $pkData = array();
        $uniqueRows = array();
        foreach ($rows as $i => $row) {
            $pk = $row[$this->primaryKey];
            $pkData[$i] = $pk;
            if (!isset($uniqueRows[$pk])) {
                $uniqueRows[$pk] = $row;
            }
        }
        return array($pkData, $uniqueRows);
    }
    
    protected function ungroupRowsByIdentifiers(array $pkData, array $records) {
        $res = array();
        foreach ($pkData as $id => $pk) {
            $res[$id] = $records[$pk];
        }
        return $res;
    }
    
    function peConvertForLoad($object, $hyData) {
        $res = $hyData;
        $d = $this->db->getDialect();
        if ($d->hasToConvertDatesOnLoad()) {
            $res = $this->convertDates($oid, $this->getDateFormats()); 
        }
        $res['_peIdentifier'] = $hyData[$this->primaryKey];
        return $res;
    }
    
    function peConvertForSave($object, $hyData) {
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
    
    function peReplaceNNRecords($object, $rowProto, $rows, $midTableName, & $errors = array(), Ac_Model_Association_Abstract $association = null) {
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
    
    function peLoad($object, $identifier, & $error = null) {
        $sql = 'SELECT * FROM '.$this->db->n($this->tableName).' WHERE '.$this->primaryKey.' = '.$this->db->q($identifier);
        $res = $this->db->fetchRow($sql);
        return $res;
    }
    
    function peSave($object, & $hyData, & $exists = null, & $error = null, & $newData = array()) {
        // leave only existing columns
        
        if (isset($hyData['_peIdentifier'])) {
            $exists = true;
            $pk = $hyData['_peIdentifier'];
        } else {
            $exists = false;
            $pk = null;
        }
        
        $dataToSave = array_intersect_key($hyData, array_flip($this->getColumns()));
        
        if ($exists) {
            $query = $this->db->updateWithKeys($this->tableName, $dataToSave, array($this->primaryKey  => $pk));
            if ($this->db->query($query) !== false) {
                $res = $dataToSave;
                if (isset($dataToSave[$this->primaryKey]))
                    $pk = $dataToSave[$this->primaryKey];
            } else {
                $descr = $this->db->getErrorDescr();
                if (is_array($descr)) $descr = implode("; ", $descr);
                $error = $this->db->getErrorCode().': '.$descr;
                $res = false;
            }
        } else {
            $query = $this->db->insertStatement($this->tableName, $dataToSave);
            if ($this->db->query($query)) {
                if (strlen($ai = $this->getAutoincFieldName()) && !isset($dataToSave[$ai])) {
                    if (!is_array($newData)) $newData = array();
                    $pk = $newData[$ai] = $this->getLastGeneratedId();
                } else {
                    $pk = $dataToSave[$this->primaryKey];
                }
                $res = true;
            } else {
                $descr = $this->db->getErrorDescr();
                if (is_array($descr)) $descr = implode("; ", $descr);
                $error = $this->db->getErrorCode().':'.$descr;
                $res = false;
            }
        }
        $newData['_peIdentifier'] = $pk;
        return $res;
    }
    
    protected function getLastGeneratedId() {
        return $this->db->getLastInsertId();
    }
    
    /**
     * @param type $hyData 
     * @return bool
     */
    function peDelete($object, $hyData, & $error = null) {
        if (isset($hyData['_peIdentifier'])) {
            $key = $hyData['_peIdentifier'];
        } else {
            $key = $hyData[$this->primaryKey];
        }
        $res = (bool) $this->db->query($sql = "DELETE FROM ".$this->db->n($this->tableName)." WHERE "
            .$this->db->n($this->primaryKey)." ".$this->db->eqCriterion($key));
        return $res;
    }

    function setMapper(Ac_Model_Mapper $mapper = null) {
        if (($res = parent::setMapper($mapper)) && strlen($this->identifierField)) {
            if ($mapper->getIdentifierField() === false) {
                $mapper->setIdentifierField($this->identifierField);
            }
            if ($mapper->getIdentifierPublicField() === false) {
                $mapper->setIdentifierPublicField($this->primaryKey);
            }
            if ($mapper->getRowIdentifierField() === false) {
                if (($this->setRowIdentifierToPk) && strlen($this->primaryKey)) {
                    $mapper->setRowIdentifierField($this->primaryKey);
                }
            }
        }
        return $res;
    }
    
    function checkRecordPresence($object, $indices = array(), $ignoreIndicesWithNullValues = true) {
        $db = $this->getDb();
        $columns = array();
        $colIdxMap = array();
        $crits = array();
        foreach ($indices as $idxId => $fields) {
            $pattern = Ac_Accessor::getObjectProperty($object, Ac_Util::toArray($fields));
            if ($ignoreIndicesWithNullValues) {
                foreach ($pattern as $k => $v) 
                    if (is_null($v)) unset($pattern[$k]);
                if (!$pattern) continue;
            }
            $criterion = $db->valueCriterion($vals = $this->mapFieldToColumnValues($pattern), 't');
            $columns["_rp_idx_".$idxId] = "\nIF({$criterion}, 1, 0) AS ".$db->n("_rp_idx_".$idxId);
            $colIdxMap[$idxId] = "_rp_idx_".$idxId;
            $crits[] = $criterion;
        }
        $res = array();
        if ($columns) {
            $strCols = implode(", ", $columns);
            $strCrits = "(".implode(") OR (", $crits).")";
            $sql = "
                SELECT t.*, {$strCols}
                    FROM {$this->tableName} t 
                    WHERE {$strCrits}
            ";
            list($pkData, $uniqueRows) = $this->groupRowsByIdentifiers($this->db->fetchArray($sql));
            foreach ($uniqueRows as $pk => $row) {
                foreach ($colIdxMap as $idx => $col) {
                    if ($row[$col]) $res[$idx][] = $pk;
                }
            }
        }
        return $res;
    }
    
    protected function mapFieldToColumnValues($fieldsAndValues) {
        // TODO: real mapping
        return $fieldsAndValues;
    }
    
    protected function getWhereFromCriteria(array $query, & $unmapped) {
        // TODO: real mapping
        $cc = array_flip($this->getColumns());
        $byValues = array();
        if (isset($query[Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION])) {
            $query[$this->primaryKey] = $query[Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION];
            unset($query[Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION]);
        }
        foreach ($query as $k => $v) {
            if (is_scalar($v) || is_array($v)) $byValues[$k] = $v;
        }
        if ($byValues && ($mapped = array_intersect_key($byValues, $cc))) {
            $unmapped = array_diff_key($query, $cc);
            $db = $this->getDb();
            $r = array();
            foreach ($mapped as $k => $v) {
                $r[] = $db->n(array('t', $k)).$db->eqCriterion($v);
            }
            if (count($r) > 1) $res = "(".implode(") AND (", $r).")";
                else $res = $r[0];
        } else {
            $unmapped = $query;
            $res = false;
        }
        return $res;
    }
    
    protected function canSimpleSort($sort) {
        $res = false;
        
        // TODO: map
        $cc = array_flip($this->getColumns());
        
        $db = $this->getDb();
        if (is_scalar($sort) && isset($cc[$sort])) {
            $res = $sort;
        } elseif (is_array($sort)) {
            $r = array();
            foreach ($sort as $k => $v) {
                if (is_numeric($k)) {
                    $k = $v;
                    $v = true;
                } else {
                    $v = (bool) $v;
                }
                if (isset($cc[$k])) {
                    $r[] = $db->n(array('t', $k)).($v? '' : ' DESC');
                } else {
                    break;
                }
            }
            if (count($r) === count($sort)) {
                $res = implode(", ", $r);
            }
        }
        return $res;
    }
    
    protected function applyCriteriaToSelect(array $query, Ac_Sql_Select $select, & $remainingQuery = array()) {
        $remainingQuery = $query;
        $db = null;
        
        // support for direct SQL parts
        foreach (array_intersect_key($query, array_flip($select->listParts())) as $k => $v) {
            $select->getPart($k)->bind($v);
            unset($remainingQuery[$k]);
        }
        
        foreach ($remainingQuery as $k => $v) {
            if (is_object($v)) { 
                // support for Ac_Sql_Expression
                if ($v instanceof Ac_Sql_Expression) {
                    $select->where['crit_'.$k] = $v;
                    unset($remainingQuery[$k]);
                // support for Ad-hoc Sql parts
                } elseif ($v instanceof Ac_Sql_Part) {
                    $v->applyToSelect($select);
                    unset($remainingQuery[$k]);
                }
            } else {
                // support for path-like equals
                if (strpos($k, '[') && (is_scalar($v) || is_array($v))) {
                    $path = Ac_Util::pathToArray($k);
                    $tail = array_pop($path);
                    if ($path && $tail && $select->hasTable($alias = Ac_Util::arrayToPath($path))) {
                        if (!$db) $db = $select->getDb();
                        $select->useAlias($alias);
                        $select->where[$k] = $db->n(array($alias, $tail)).$db->eqCriterion($v);
                        unset($remainingQuery[$k]);
                    }
                }
            }
        }
    }
    
    protected function applySortToSelect($sort, Ac_Sql_Select $select) {
        $res = false;
        if (is_object($sort) && $sort instanceof Ac_Sql_Part) {
            $res = true;
            $sort->applyToSelect($select);
        } 
        if (is_scalar($sort) && in_array($sort, $select->listParts())) {
            $part = $select->getPart($sort);
            if ($part instanceof Ac_Sql_Order) {
                $part->bind(true);
                $res = true;
            }
        } elseif (is_array($sort) && count($sort) === 1) {
            $kk = array_keys($sort);
            $vv = array_values($sort);
            $pp = $select->listParts();
            if (isset($pp[$kk[0]]) && ($part = $select->getPart($kk[0])) instanceof Ac_Sql_Order) {
                $pp[$kk[0]] = $vv;
                $part->bind($vv[0]);
                $res = true;
            }
        }
        return $res;
    }

    /**
     * Returns array($where, $sort) or Ac_Sql_Select with partially applied criteria.
     * Sets $remainingQuery and $sorted output params.
     */
    protected function whereSortOrSelect(array $query = array(), $sort = false, & $remainingQuery = array(), & $sorted = false) {
        if (!$query) {
            $where = '';
            $remainingQuery = array();
        } else {
            $where = $this->getWhereFromCriteria($query, $remainingQuery);
        }
        if ($sort) {
            $strSort = $this->canSimpleSort($sort);
            if ($strSort) $sorted = true;
        } else {
            $strSort = '';
            $sorted = true;
        }
        if (!$remainingQuery && $sorted) {
            // simple case -- query the DB directly
            $res = array($where, $strSort);
        } else {
            // must apply the Sql Select
            $selectQ = $remainingQuery;
            $select = $this->getAppliedSelect($selectQ);
            $this->applyCriteriaToSelect($selectQ, $select, $remainingQuery);
            if ($where) $select->where['_search'] = $where;
            if ($strSort) $select->orderBy['_sort'] = $strSort;
            elseif ($sort) $sorted = $this->applySortToSelect ($sort, $select);
            $res = $select;
        }
        return $res;
    }
    
    /**
     * Extracts Ac_Sql_Select or its' prototype from $query[Ac_Model_Storage_MonoTable::QUERY_SQL_SELECT]
     * and returns instance that will be used (returns Storage own search if none provided).
     * Unsets the "criterion" from the query.
     * 
     * @param array $query
     * @return Ac_Sql_Select
     * @throws Ac_E_InvalidCall
     */
    protected function getAppliedSelect(array & $query, array $prototypeExtra = array()) {
        if (isset($query[self::QUERY_SQL_SELECT]) && $query[self::QUERY_SQL_SELECT]) {
            $sel = $query[self::QUERY_SQL_SELECT];
            if (is_array($sel)) {
                if ($prototypeExtra) $proto = Ac_Util::m($prototypeExtra, $sel);
                    else $proto = $sel;
                $res = $this->createBlankSqlSelect($proto);
            } elseif (is_object($sel)) {
                if ($sel instanceof Ac_Sql_Select) {
                    if ($prototypeExtra) {
                        throw new Ac_E_InvalidUsage("Cannot use \$prototypeExtra along with instance provided "
                            . "in \$query[Ac_Model_Search::QUERY_SQL_SELECT]");
                    } else {
                        $res = clone $sel;
                    }
                } else 
                    throw Ac_E_InvalidCall::wrongType("\$query[Ac_Model_Search::QUERY_SQL_SELECT]", 
                        $sel, array('array', 'Ac_Sql_Select'));
            }
            unset($query[self::QUERY_SQL_SELECT]);
        } else {
            $res = $this->createBlankSqlSelect($prototypeExtra);
        }
        // TODO: fix this ugly hack with t.*
        $alias = $res->getEffectivePrimaryAlias();
        if (!in_array($allCol = $alias.'.*', $res->columns)) {
            array_unshift($res->columns, $allCol);
        }
        return $res;
    }
    
    function find(array $query = array(), $keysToList = false, $sort = false, $limit = false, $offset = false, & $remainingQuery = array(), & $sorted = false) {
        $strict = func_num_args() <= 5 || $remainingQuery === true;
        $wss = $this->whereSortOrSelect($query, $sort, $remainingQuery, $sorted);
        if (is_array($wss)) { // use $where and $sort
            list($where, $strSort) = $wss;
            $res = $this->loadRecordsByCriteria($where, $strSort, '', $offset, $limit, 't'); 
        } else {
            $select = $wss;
            // must apply the Sql Select
            if ($limit) $select->limitCount = $limit;
            if ($offset) $select->limitOffset = $offset;
            $res = $this->loadFromRows($select->getDb()->fetchArray($select, $this->primaryKey), true);
        }
        if ($strict) {
            if ($remainingQuery) 
                throw new Ac_E_InvalidUsage("Criterion ".implode(" / ", array_keys($remainingQuery))." is unknown to {$this}");
            if (!$sorted) {
                throw new Ac_E_InvalidUsage("Sort mode ".Ac_Model_Mapper::describeSort($sort)." is unknown to {$this}");
            }
        }
        return $res;
    }
    
    /**
     * @return Ac_Sql_Select
     */
    protected function createBlankSqlSelect(array $prototypeExtra = array()) {
        return $this->getMapper()->createSqlSelect($prototypeExtra);
    }
    
    /**
     * @return Ac_Sql_Select
     * 
     * @param array $query
     * @param type $sort
     * @param type $limit
     * @param type $offset
     * @param type $remainingQuery
     * @param type $sorted
     */
    function createSqlSelect(array $prototypeExtra = array(), array $query = array(), $sort = false, $limit = false, $offset = false, & $remainingQuery = array(), & $sorted = false) {
        $strict = func_num_args() <= 5 || $remainingQuery === true;
        if (!$query) {
            $where = '';
            $remainingQuery = array();
        } else {
            $where = $this->getWhereFromCriteria($query, $remainingQuery);
        }
        if ($sort) {
            $strSort = $this->canSimpleSort($sort);
            if ($strSort) $sorted = true;
        } else {
            $strSort = '';
            $sorted = true;
        }
        $selectQ = $remainingQuery;
        $res = $this->getAppliedSelect($selectQ, $prototypeExtra);
        $this->applyCriteriaToSelect($selectQ, $res, $remainingQuery);
        if ($where) $res->where['_search'] = $where;
        if ($strSort) $res->orderBy['_sort'] = $strSort;
        elseif ($sort) $sorted = $this->applySortToSelect ($sort, $res);
        if ($limit) $res->limitCount = $limit;
        if ($offset) $res->limitOffset = $offset;
        if ($strict) {
            if ($remainingQuery) 
                throw new Ac_E_InvalidUsage("Criterion ".implode(" / ", array_keys($remainingQuery))." is unknown to {$this}");
            if (!$sorted) {
                throw new Ac_E_InvalidUsage("Sort mode ".Ac_Model_Mapper::describeSort($sort)." is unknown to {$this}");
            }
        }
        return $res;
    }
    
    function fetchTitlesIfPossible($titleProperty, $valueProperty, $sort, array $query = array()) {
        $cc = $this->getColumns();
        $res = false;
        if ($titleProperty === false) $titleProperty = $this->primaryKey;
        if ($valueProperty === false) $valueProperty = $this->primaryKey;
        if (in_array($titleProperty, $cc) && in_array($valueProperty, $cc)) {
            // both 'properties' are columns
            $wss = $this->whereSortOrSelect($query, $sort, $remainingQuery, $sorted);
            if (!$remainingQuery && $sorted) {
                $db = $this->getDb();
                $qTitle = $db->n($titleProperty);
                $qValue = $db->n($valueProperty);
                $col = "t.{$qTitle} AS title, t.{$qValue} AS value";
                if (is_array($wss)) {
                    list ($where, $sort) = $wss;
                    $tn = $db->n($this->tableName);
                    if (strlen($sort)) 
                        $o = "ORDER BY 
                            {$sort}";
                        else $o = "";
                    if (strlen($where)) $w = "WHERE 
                            {$where}";
                        else $w = "";
                    $q = "
                        SELECT $col 
                        FROM {$tn} AS t
                        {$w}
                        {$o}
                    ";
                } else {
                    $q = $wss;
                    $q->columns = $col;
                }
                $res = $db->fetchColumn($q, 'title', 'value');
            }
        }
        return $res;
    }
    
    function countIfPossible(array $query = array()) {
        $stmt = $this->createSqlSelect(array(), $query, false, false, false, $remainingQuery);
        if (!$remainingQuery) { // it's possible
            $stmt->columns = "COUNT(DISTINCT t.".$this->getDb()->n($this->primaryKey).")";
            $stmt->groupBy = array();
            $stmt->limitCount = false;
            $stmt->limitOffset = false;
            $res = $this->getDb()->fetchValue($stmt);
        } else {
            $res = false;
        }
        return $res;
    }
    
    protected function implCountWithValuesIfPossible($fieldNames, $fieldValues, $groupByValues, $query, $useQueryOnly) {
        if ($groupByValues === Ac_Model_Mapper::GROUP_NONE && (count($fieldNames) == 1 && !$query || $useQueryOnly)) {
            if (!$query) $res = $this->countIfPossible(array($fieldNames[0] => $fieldValues));
            else /* $useQueryOnly */ $res = $this->countIfPossible(array($query));
        } elseif (!array_diff($fieldNames, $this->getColumns())) { // only the direct mappable field. Todo: allow mapping
            $combined = $query;
            if (!$useQueryOnly) {
                if (count($fieldNames) > 1) {
                    // make multi-criterion/
                    foreach ($fieldValues as $row) $crit[] = array_combine($fieldNames, $row);
                    $combined['_multi_'.(implode('_', $fieldNames))] = new Ac_Sql_Expression($this->db->valueCriterion($crit, false, false, true));
                } else {
                    $combined[$fieldNames[0]] = $fieldValues;
                }
            }
            $stmt = $this->createSqlSelect(array(), $combined, false, false, false, $remainingQuery);
            if (!$remainingQuery) { // it's possible
                if (count($fieldNames) == 1 && $groupByValues !== Ac_Model_Mapper::GROUP_NONE) {
                    $stmt->columns = array(
                        'fld' => $this->getDb()->n($fieldNames[0]), 
                        'cnt' => "COUNT(DISTINCT t.".$this->getDb()->n($this->primaryKey).")"
                    );
                    $stmt->groupBy = $fieldNames;
                    $stmt->limitCount = false;
                    $stmt->limitOffset = false;
                    $counts = $this->getDb()->fetchColumn($stmt, 'cnt', 'fld');
                    $res = array();
                    foreach ($fieldValues as $i => $val) {
                        $k = $groupByValues === Ac_Model_Mapper::GROUP_VALUES? $val : $i;
                        $res[$k] = isset($counts[$val])? $counts[$val] : 0;
                    }
                    if ($useQueryOnly) {
                        // limit the result only to the values that we are interested in
                        $stmt->having['__values__'] = 'fld '.$this->db->eqCriterion($value);
                    }
                } else {
                    // a bit more difficult task
                    if ($groupByValues !== Ac_Model_Mapper::GROUP_NONE) {
                        $stmt->columns = $fieldNames;
                        $stmt->groupBy = $fieldNames;
                    } else {
                        $stmt->columns = array();
                        $stmt->groupBy = array();
                    }
                    $stmt->columns['__cnt__'] = "COUNT(DISTINCT t.".$this->getDb()->n($this->primaryKey).")";
                    $stmt->limitCount = false;
                    $stmt->limitOffset = false;
                    if ($groupByValues !== Ac_Model_Mapper::GROUP_NONE && $useQueryOnly) {
                        $crit = array();
                        // limit the result only to the values that we are interested in
                        foreach ($fieldValues as $row) $crit[] = array_combine($fieldNames, $row);
                        $stmt->having['__values__'] = new Ac_Sql_Expression($this->db->valueCriterion($crit, false, false, true));
                    }
                    if ($groupByValues == Ac_Model_Mapper::GROUP_NONE) {
                        $res = $this->getDb()->fetchValue($stmt);
                    } else {
                        $arr = Ac_Util::indexArray($this->getDb()->fetchArray($stmt), $fieldNames, true, '__cnt__');
                        if ($groupByValues == Ac_Model_Mapper::GROUP_ORDER) {
                            // now have to find keys of proper fieldValues
                            $res = array();
                            if (count($fieldNames) === 3) {
                                foreach ($fieldValues as $i => $row)
                                    if (isset($arr[$row[0]]) && isset($arr[$row[0]][$row[1]]) 
                                        && isset($arr[$row[0]][$row[1]][$row[2]])) $res[$i] = $arr[$row[0]][$row[1]][$row[2]];
                                    else $res[$i] = 0;
                            } elseif (count($fieldNames) === 2) {
                                foreach ($fieldValues as $i => $row)
                                    if (isset($arr[$row[0]]) && isset($arr[$row[0]][$row[1]]))
                                        $res[$i] = $arr[$row[0]][$row[1]];
                                    else $res[$i] = 0;
                            } else {
                                foreach ($fieldValues as $i => $row)
                                    $res[$i] = Ac_Util::simpleGetArrayByPath ($arr, $row, 0);
                            }
                        } else {
                            $res = $arr;
                        }
                    }
                }
            } else {
                $res = false;
            }
        } else {
            $res = false;
        }
        return $res;
    }
    
    protected function inspect() {
        $dbi = $this->db->getInspector();
        
        // Detect column names
        $cols = $this->db->getInspector()->getColumnsForTable($this->db->replacePrefix($this->tableName));
        
        $columns = array();
        $uniqueIndices = array();
        $nullableColumns = array();
        $primaryKey = null;
        $defaults = array();
        
        foreach ($cols as $name => $col) {
            $defaults[$name] = $col['default'];
            if ($col['nullable']) $nullableColumns[] = $name;
            if (isset($col['autoInc']) && $col['autoInc'] && ($this->autoincFieldName === false)) {
                $this->autoincFieldName = $name;
            }
        }
        
        $columns = array_keys($defaults);
        
        // Detect unique indices inc. primary key
        $idxs = $dbi->getIndicesForTable($this->db->replacePrefix($this->tableName));
        if ($this->primaryKey === false || $this->uniqueIndices === false) {
            foreach ($idxs as $name => $idx) {
                if (isset($idx['primary']) && $idx['primary']) {
                    if (count($idx['columns']) == 1) {
                        $cVals = array_values($idx['columns']);
                        $primaryKey = $cVals[0];
                    } else {
                        // TODO: But composite PK isn't supported yet!
                        $primaryKey = $idx['columns'];
                    }
                }
                if (isset($idx['unique']) && $idx['unique'] || isset($idx['primary']) && $idx['primary']) {
                    if (isset($idx['primary']) && $idx['primary']) $name = 'PRIMARY';
                    $uniqueIndices[$name] = array_values($idx['columns']);
                }
            }
        }
        
        if ($this->columns === false) $this->columns = $columns;
        if ($this->uniqueIndices === false) $this->uniqueIndices = $uniqueIndices;
        if ($this->nullableColumns === false) $this->nullableColumns = $nullableColumns;
        if ($this->primaryKey === false) $this->primaryKey = $primaryKey;
        if ($this->defaults === false) $this->defaults = $defaults;
    }
    
    function getSqlSelectPrototype($primaryAlias = 't') {
        $res = $this->doGetSqlSelectPrototype($primaryAlias);
        return $res;
    }
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = array(
            'class' => 'Ac_Sql_Select',
			'tables' => array(
				$primaryAlias => array(
					'name' => $this->tableName, 
				),
			),
        );
        if ($this->mapper) {
			$res['tableProviders']['model'] = array(
                'class' => 'Ac_Model_Sql_TableProvider',
                'mapperAlias' => $primaryAlias,
                'mapper' => $this->mapper,
			);
        }
        return $res;
    }
    
}