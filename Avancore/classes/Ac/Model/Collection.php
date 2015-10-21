<?php

/**
 * Collection is used to retrieve objects from the database using given SQL statement.
 * - statements are executed during first-time access to collection records.
 * - support for different loading approaches is implemented, for example, collection may load only keys first, then load objects. 
 * - LIMIT clause is supported; to count records, separate SQL statement is used. 
 * - collections are specially optimized for sequential access (records are fetched from MySQL result resource)
 * - to minimize memory usage, cursor approach can be used (one or more, but limited number of records is used to traverse through the resultset)
 * - unbuffered queries are also supported (when used with Ac_Legacy_Database implementation that supports unbuffered queries) 
 */
class Ac_Model_Collection {
    
    /**
     * @var array
     * Contents of collection
     */
    var $_records = false;
    var $_keys = false;
    
    /**
     * @var string
     */
    var $_mapperClass = false;
    
    /**
     * @var Ac_Model_Mapper
     */
    var $_mapper = false;
    
    var $_alias = 't';
    
    var $_distinct = false;
    var $_distinctCount = null;
    var $_where = array();
    var $_joins = array();
    var $_order = array();
    var $_groupBy = false;
    var $_having = false;
    var $_extraColumns = array();
    var $_limitOffset = false;
    var $_limitCount = false;
    var $_count = false;
    
    var $_matchKeys = false;
    var $_separateKeys = false;
    
    /**
     * Whether collection is already open
     * @var bool
     */
    var $_open = false;
    
    var $_canSetLimits = true;
    
    /**
     * Tail of SQL statement
     */
    var $_sqlTail = false;
    
    /**
     * Cursor records. 
     * @var array (className => Ac_Model_Object)
     */
    var $_cursor = array();
    
    /**
     * Whether this collection uses (usually) the same object to access every row...
     * 
     * @var bool
     */
    var $_useCursor = false;
    
    /**
     * Whether this collection implements sequential access...
     *
     * @var bool
     */
    var $_sequential = false;
    
    /**
     * Whether to try to use unbuffered query
     * @var bool
     */
    var $_unbuffered = false;
    
    /**
     * Db result 
     * 
     * @var resource
     */
    var $_dbResult = false;
    
    /**
     * Used to emulate sequential access
     */
    var $_currPos = false;
    
    /**
     * Used to emulate sequential access
     */
    var $_currKey = false;
    
    /**
     * Name of database table
     * @var string
     */
    var $_tableName = false;
    
    /**
     * Database driver
     * @var Ac_Sql_Db
     */
    var $_db = false;
    
    /**
     * Primary key name for 'byKeys' collections
     * @var string
     */
    var $_pkName = false;
    
    /**
     * Callback function to get record class 
     */
    var $_rcFun = false;
    
    /**
     * Class of records for simple instance function
     */
    var $_recordClass = false;
    
    function Ac_Model_Collection ($mapperClass = false, $matchingKeys = false, $where = false, $order = false, $joins = false, $extraColumns = false, $limitOffset = false, $limitCount = false) {
        $this->_mapperClass = $mapperClass;
        $this->_matchingKeys = $matchingKeys;
        if (strlen($where)) $this->setWhere($where);
        if (strlen($order)) $this->setOrder($order);
        if ($joins || strlen($joins)) $this->setJoins($joins);
        if ((is_array($extraColumns)) || strlen($extraColumns)) $this->setExtraColumns($extraColumns);
        $this->_limitOffset = $limitOffset;
        $this->_limitCount = $limitCount;
    }
    
    /**
     * Allows to directly set records that would be in the collection
     * @param array of Ac_Model_Data $records
     */
    function setRecords($records = array()) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        $this->setSequential(false);
        $this->setUnbuffered(false);
        $this->_records = $records;
    }
    
    function setKeys($keys = array(), $mapperClass = false, $pkName = false) {
        if (!strlen($mapperClass) && !$pkName) trigger_error ("Either \$mapperClass or \$pkName must be provided");
        $this->_mapper = false;
        $this->useMapper($mapperClass);
        if (!$pkName) {
            $mapper = Ac_Model_Mapper::getMapper($mapperClass);
            $pkName = $mapper->getStorage()->getPrimaryKey();
        }
        if (!$this->_db) {
            throw new Exception("Database not provided, call setDatabase() first");
        }
        $al = $pkName;
        if (strlen($this->_alias)) $al = array($this->_alias, $pkName);
        $this->setWhere($this->_db->nameQuote($al).' '.$this->_db->eqCriterion($keys));
    }
    
    /**
     * Makes collection to work without mapper by providing necessary parameters
     * 
     * @param string $tableName Name of database table to take records from
     * @param string|array $pkName Name of primary key field (if any) or key fields (are used when $this->matchKeys === true)
     * @param string $recordClass Class of records (if FALSE and no $recordClassCallback is provided, arrays will be returned instead of records)
     * @param null|callback $recordClassCallback Function to provide record class if no $recordClass is provided. If $recordClass === false and $recordClassCallback === null, arrays will be returned instead of records
     * 
     * $recordClassCallback function should accept parameter $row, and return corresponding record class (false to create an array)
     */
    function useNoMapper($tableName, $pkName, $recordClass = false, $recordClassCallback = null, Ac_Sql_Db $database = null) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        $this->_tableName = $tableName;
        $this->_pkName = $pkName;
        $this->_recordClass = $recordClass;
        $this->_mapperClass = false;
        $this->_mapper = false;
        $this->_rcFun = $recordClassCallback;
        if ($database) $this->_db = $database;
    }
    
    function _getRecordClass($row) {
        if ($this->_mapper) return $this->_mapper->getRecordClass($row);
        elseif($this->_recordClass) return $this->_recordClass;
        elseif($this->_rcFun) return call_user_func($this->_rcFun);
        else return false;
    }
    
    function setMapper(Ac_Model_Mapper $mapper) {
        $this->useMapper($mapper);
    }
    
    /**
     * Makes collection to take primary parameters such as table name, record class etc. from mapper
     * @param string $mapperClass Name of mapper class
     */
    function useMapper($mapperClass) {
        if (!$mapperClass) trigger_error ('No mapper class provided. Provide mapper class or call useNoMapper() instead');
        if (is_object($mapperClass) && $mapperClass instanceof Ac_Model_Mapper) {
            $this->_mapper = $mapperClass;
            $this->_mapperClass = $mapperClass->getId();
        } else {
            $this->_mapperClass = $mapperClass;
            $this->_mapper = Ac_Model_Mapper::getMapper($mapperClass);
        }
        $this->_tableName = $this->_mapper->tableName;
        $this->_recordClass = false;
        $this->_rcFun = false;
        $this->_pkName = $this->_mapper->getStorage()->getPrimaryKey();
        $this->_db = $this->_mapper->getDb();
    }
    
    function areMatchingKeys() {
        return $this->matchingKeys;
    }
    
    function setSequential($sequential = true) {
        return; // not supported yet
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        $this->_sequential = $sequential;
    }
    
    function isSequential() { return $this->_sequential; }
    
    function setUnbuffered($unbuffered = true) {
        return; // not supported yet
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        $this->_unbuffered = $unbuffered;
    }
    
    function isUnbuffered() { return $this->_unbuffered; }
    
    function useCursor($useCursor = true) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        $this->_useCursor = $useCursor;
    }
    
    function usesCursor() { return $this->_useCursor; }
    
    function setWhere($where = false) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        if (is_array($where)) $this->_where = $where;
        elseif (strlen($where)) $this->_where = array($where);
            else $this->_where = array();
    }
    
    function setGroupBy($groupBy = false) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        $this->_groupBy = $groupBy;
    }
    
    function getGroupBy() {
        return $this->_groupBy;
    }
    
    function setHaving($having = false) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        $this->_having = $having;
    }
    
    function getHaving() {
        return $this->_having;
    }
    
    function getWhere() { return $this->_where; }
    
    function addWhere($where) {
        $this->_where[] = $where;
        $this->_sqlTail = false;
    }
    
    /**
     * @param string|array $order
     */
    function setOrder($order = false) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        $this->_sqlTail = false;
        if (is_array($order)) $this->_order = $order;
        elseif (strlen($order)) $this->_order = array($order);
            else $this->_order = array(); 
    }
    
    function getOrder() { return $this->_order; }
    
    function addOrder($order) {
        $this->_order[] = $order;
    }
    
    function setDistinct($distinct = true) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        else $this->_distinct = $distinct; 
    }
    
    function setDistinctCount($distinctCount = true) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        else $this->_distinctCount = $distinctCount; 
    }
    
    function setJoins($joins = false) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        if (is_array($joins)) $this->_joins = $joins;
        elseif (strlen($joins)) $this->_joins = array($joins);
            else $this->_joins = array(); 
    }
    
    function getJoins() { return $this->_joins; }
    
    function addJoin($join) {
        $this->_joins[] = $join;
        $this->_sqlTail = false;
    }
    
    function setExtraColumns($extraColumns = false) {
        if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        if (is_array($extraColumns)) $this->_extraColumns = $extraColumns;
        elseif (strlen($extraColumns)) $this->_extraColumns = array($extraColumns);
            else $this->_extraColumns = array(); 
    }
    
    function getExtraColumns() { return $this->_extraColumns; }
    
    function addExtraColumn($extraColumn) {
        $this->_extraColumns[] = $extraColumn;
    }
    
    function setAlias($alias) {
         if ($this->_open !== false) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
         $this->_alias = $alias;
    }
    
    function getAlias() {
        return $this->_alias;
    }
    
    function setLimits($offset = false, $count = false) {
        if (!$this->_canSetLimits) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        $this->_limitOffset = $offset;
        $this->_limitCount = $count;
    }
    
    function _getStatementTail($withLimits = false, $withOrder = true, $withGroupBy = true, $withSelect = false) {
        if ($this->_sqlTail === false) $this->_sqlTail = array();
        $hash = "$withLimits.$withOrder.$withGroupBy.$withSelect"; 
        if (!isset($this->_sqlTail[$hash])) {
            if (!$this->_tableName) $this->useMapper($this->_mapperClass);
            if ($this->_matchKeys && !(is_scalar($this->_pkName) && $this->_pkName)) 
                trigger_error ("matchKeys option can be used only for collections with single-column primary keys", E_USER_ERROR);
            
            $res = "FROM ".$this->_tableName." AS {$this->_alias}";
            if ($this->_joins) $res.= " ".implode(" ", $this->_joins);
            if ($this->_where) $res.= " WHERE (".implode(") AND (", $this->_where).")";
            if ($this->_groupBy && $withGroupBy) $res .= " GROUP BY ".(is_array($this->_groupBy)? implode(", ", $this->_groupBy) : $this->_groupBy);
            $h = $this->_having;
            if ($this->_having) $res .= " HAVING ".(is_array($this->_having)? ("(".implode(") AND (", $this->_having).")") : $this->_having);
            if ($withOrder && $this->_order) $res .= " ORDER BY ".implode(", ", $this->_order);
            if ($withSelect !== false) {
                if (is_string($withSelect)) {
                    if ($this->_distinct) $dist = "DISTINCT ";
                        else $dist = "";
                    $res = "SELECT $dist $withSelect ".$res;
                }
                else {
                    if ($this->_extraColumns) $xc = implode(", ", $this->_extraColumns).", ";
                        else $xc = "";
                    if ($this->_distinct) $dist = "DISTINCT ";
                        else $dist = "";
                    $res = "SELECT {$dist}{$xc}{$this->_alias}.* ".$res;
                }
            } 
            if ($withLimits && $this->_limitCount) /*$res .= $this->_db->getLimitsClause($this->_limitCount, $this->_limitOffset);*/
                $res = $this->_db->applyLimits($res, $this->_limitCount, $this->_limitOffset, true);
            
            $this->_sqlTail[$hash] = $res;
            
            $this->_open = true;
        } else {
            $res = $this->_sqlTail[$hash];
        }
        return $res;
    }
    
    function getStatementTail($withLimits = false, $withOrder = true, $withGroupBy = true, $withSelect = false) {
        $tmp = $this->_sqlTail; $o = $this->_open;
        $res = $this->_getStatementTail($withLimits, $withOrder, $withGroupBy, $withSelect);
        
        //  We don't want to alter uninitialized _sqlTail property since otherwise closed collection will be considered 'open'
        $this->_sqlTail = $tmp; $this->_open = $o;
         
        return $res;
    }
    
    function countRecords() {
        if ($this->_count === false) {
            if ($this->_groupBy && $this->_having) {
                $countStmt = "SELECT COUNT(*) FROM (".$this->getStatementTail(false, false, true, true).") AS Stmt";
            } else {
                $tail = $this->getStatementTail(false, false, false);
                $countStmt = "SELECT COUNT(*) ".$tail;
                $distinct = $this->_distinctCount || ($this->_distinct && ($this->_distinctCount !== false)); 
                if ($this->_groupBy && !$distinct) {
                    trigger_error("Using countRecords() without setDistinct() || setDistinctCount() of a collection that has groupBy clause may lead to inaccurate results", 
                        E_USER_NOTICE);
                }
                if (($distinct) && $this->_pkName) {
                    $what = $this->_db->n($this->_alias).'.'.$this->_db->n($this->_pkName);
                    $countStmt = "SELECT COUNT(DISTINCT {$what}) ".$tail;
                }
            } 
            $this->_count = $this->_db->fetchValue($countStmt);
            //var_dump($this->_count.' '.$countStmt); 
        }
        return $this->_count;
    }
    
    function _countWithLimits() {
        $this->_canSetLimits = false;
        if (!$this->_limitOffset && !$this->_limitCount) return $this->countRecords();
        $c = $this->countRecords;
        if ($this->_limitOffset) $c = max(0, $c - $this->_limitOffset);
        if ($this->_limitCount) $c = max ($c, $this->_limitCount);
        return $c;
    }
    
    function listRecords() {
        if ($this->_sequential) trigger_error ("Cannot use ".__FUNCTION__."() on a collection with sequential access", E_USER_ERROR);
        if ($this->_records === false) {
            if ($this->_matchKeys) {
                if ($this->_separateKeys) $this->_loadKeys(); 
                    else $this->_loadAllRecords();
                return array_keys($this->_records);
            } else {
                if ($cnt = $this->_countWithLimits()) return range(0, $cnt - 1);
                    else return array();
            }
        } else return array_keys($this->_records);
    }
    
    function getRecord($intKey) {
        if ($this->_sequential) trigger_error ("Cannot use ".__FUNCTION__."() on a collection with sequential access", E_USER_ERROR);
        if ($this->_records === false) $this->_loadAllRecords();
        if (!isset($this->_records[$intKey])) trigger_error ("Non-existing record key specified", E_USER_ERROR);
        if ($this->_records[$intKey] === false) $this->_loadAllRecords();
        $res = $this->_records[$intKey];
        $this->_currPos = false;
        $this->_currKey = $intKey;
        return $res;
    }
    
    function _getSql() {
        return $this->_getStatementTail(true, true, true, true);
    }
    
    function _loadAllRecords() {
        $this->_canSetLimits = false;
        $stmt = $this->_getStatementTail(true, true, true, true);
        $pk = $this->_pkName;
        $rows = $this->_db->fetchArray($stmt, $this->_matchKeys? $pk : false);
        if ($this->_mapper) $this->_records = $this->_mapper->loadFromRows($rows);
        else $this->_records = $rows;
        $this->_keys = array_keys($this->_records);
    }
    
    function _loadKeys() {
        $this->_canSetLimits = false;
        $keysStmt = $this->_getStatementTail(true, true, true, $this->_alias.'.'.$this->_pkName);
        $this->_records = array();
        foreach (($this->_keys = $this->_db->fetchColumn($keysStmt)) as $key) $this->_records[$key] = false;
        $_currKey = false;
    }
    
    function rewind() {
        if ($this->_sequential) {
            $this->_canSetLimits = false;
            if ($this->_dbResult !== false) {
                //$this->_db->freeResultResource($this->_dbResult);
            }
            $this->_sqRewind();
        } else {
            $this->_currKey = $this->_currPos = false;
        }
    }
    
    function _sqRewind() {
        if ($this->_extraColumns) $xc = implode(", ", $this->_extraColumns).", ";
            else $xc = "";
        $stmt = "SELECT {$xc}{$this->_alias}.* ".$this->_getStatementTail(true);
        $this->_db->setQuery($stmt);
        $this->_dbResult = $this->_db->getResultResource($this->_unbuffered);
        $this->_currPos = 0;
    }
    
    function getPos() {
        if ($this->_currPos === false) {
            if ($this->_currKey !== false) {
                if (!$this->_matchKeys) $this->_currPos = $this->_currKey;
                else {
                    $this->_currPos = array_search($this->_currKey, $this->listRecords());
                }
            } else {
                $this->_currPos = 0;
            }
        }
        return $this->_currPos;
    }
    
    /**
     * @return Ac_Model_Object
     */
    function getNext() {
        if ($this->_sequential) {
            if ($this->_dbResult === false) $this->_sqRewind();
            $row = $this->_db->fetchAssoc($this->_dbResult);
            $this->_currPos++;
            if ($row) {
                if ($this->_useCursor) {
                    if ($cls = $this->_getRecordClass($row)) {
                        if (!isset($this->_cursor[$cls])) $this->_cursor[$cls] = ($this->_mapper? new $cls($this->_mapper) : new $cls);
                        $this->_cursor[$cls]->load($row, true);
                        $res = $this->_cursor[$cls];
                    } else {
                        $res = $row;
                    }
                    return $res;
                } else {
                    if ($rc = $this->_getRecordClass($row)) {
                        $res = $this->_mapper?  new $rc($this->_mapper) : new $rc();
                        $res->load($row, true);
                    } else $res = $row;
                    return $res;
                }
            } else {
                //$this->_db->freeResultResource($this->_dbResult);
                
                $res = null;
                $this->_currPos++;
                return $res;
            }
        } else {
            if ($this->_currPos === false) $this->getPos();
            if ($this->_records === false) {
                if ($this->_separateKeys) $this->_loadKeys();
                    else $this->_loadAllRecords();
            }
            if ($this->_currPos >= count($this->_records)) {
                $res = false;
                return $res;
            } else {
                $cp = $this->_currPos;
                $ck = $this->_currKey = $this->_keys[$cp];
                if (!is_object($this->_records[$this->_currKey])) $res = $this->getRecord($this->_currKey);
                    else $res = $this->_records[$this->_currKey];
                $this->_currPos = $cp;
                $this->_currPos++;
                $this->_currKey = $ck;
                return $res;
            }
        }
    }
    
    /**
     * @param Ac_Sql_Db $database
     */
    function setDatabase(Ac_Sql_Db $database) {
        if (!$this->_canSetLimits) trigger_error ("Can't change params of collection that is already open", E_USER_ERROR);
        $this->_db = $database;
    }

    function getRecords() {
        $res = array();
        while ($curr = $this->getNext()) $res[] = $curr;
        return $res;
    }
    
    function getPkName() {
        return $this->_pkName;
    }
        
}

