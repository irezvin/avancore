<?php

class Ac_Model_Storage_MonoTable extends Ac_Model_Storage_Sql {
    
    /**
     * name of SQL table that contains records
     */
    protected $tableName = false;

    /**
     * class of the record
     */
    protected $recordClass = false;

    /**
     * primary key of the table
     */
    protected $primaryKey = false;

    /**
     * name of auto-increment table field
     */
    protected $autoincFieldName = false;
    
    /**
     * @var array
     */
    protected $sqlColumns = false;
    
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
        $sql = "SELECT ".$this->db->n($this->tableName).".* FROM ".$this->db->n($this->tableName)." $joins  ";
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
    
    function peReplaceNNRecords($object, $rowProto, $rows, $midTableName, & $errors = array()) {
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
        
        $dataToSave = array_intersect_key($hyData, array_flip($this->listSqlColumns()));
        
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

    function setSqlColumns(array $sqlColumns) {
        $this->sqlColumns = $sqlColumns;
    }

    /**
     * @return array
     */
    function getSqlColumns() {
        return $this->sqlColumns;
    }    
    
    function listSqlColumns() {
        return $this->getSqlColumns();
    }
    
    function setMapper(Ac_Model_Mapper $mapper = null) {
        if (($res = parent::setMapper($mapper)) && $this->identifierField) {
            $mapper->setIdentifierField($this->identifierField);
            $mapper->setIdentifierPublicField($this->primaryKey);
            if (($this->setRowIdentifierToPk) && strlen($this->primaryKey)) {
                $mapper->setRowIdentifierField($this->primaryKey);
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
        $cc = $this->getSqlColumns();
        foreach ($query as $k => $v) {
            if (is_scalar($v) || is_array($v)) $byValues[$k] = $v;
        }
        if ($byValues && ($mapped = array_intersect_key($byValues, $cc))) {
            $unmapped = array_diff_key($byValues, $cc);
            $db = $this->getDb();
            $r = array();
            foreach ($mapped as $k => $v) {
                $r[] = $db->n(array('t', $k)).$db->eqCriterion($v);
            }
            if (count($r) > 1) $res = "(".implode("), (", $r).")";
                else $res = $r[0];
        } else {
            $unmapped = $byValues;
            $res = false;
        }
        return $res;
    }
    
    protected function canSimpleSort($sort) {
        $res = false;
        
        // TODO: map
        $cc = array_flip($this->getSqlColumns());
        
        $db = $this->getDb();
        if (is_scalar($sort) && isset($cc[$sort])) {
            $res = $cc;
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
                    $select['where']['crit_'.$k] = $v;
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

    function partialSearch(array $query = array(), $sort = false, $limit = false, $offset = false, & $remainingQuery = array(), & $sorted = false) {
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
            $res = $this->loadRecordsByCriteria($where, $strSort, '', $offset, $limit); 
        } else {
            // must apply the Sql Select
            
            $selectQ = $remainingQuery;
            $select = $this->createBlankSqlSelect();
            $this->applyCriteriaToSelect($selectQ, $select, $remainingQuery);
            if ($where) $select->where['_search'] = $where;
            if ($strSort) $select->orderBy['_sort'] = $strSort;
            elseif ($sort) $sorted = $this->applySortToSelect ($sort, $select);
            if ($limit) $select->limitCount = $limit;
            if ($offset) $select->limitOffset = $offset;
            $res = $this->loadFromRows($select->getDb()->fetchArray($select, $this->primaryKey), true);
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
        $res = $this->createBlankSqlSelect($prototypeExtra);
        $this->applyCriteriaToSelect($selectQ, $res, $remainingQuery);
        if ($where) $res->where['_search'] = $where;
        if ($strSort) $res->orderBy['_sort'] = $strSort;
        elseif ($sort) $sorted = $this->applySortToSelect ($sort, $res);
        if ($limit) $res->limitCount = $limit;
        if ($offset) $res->limitOffset = $offset;
        return $res;
    }
    
}