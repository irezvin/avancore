<?php

/**
 * Generates "upsert" (insert...on duplicate key update) using SQL statement or array of records
 * as the source.
 * 
 * Allows to specify mapping between source and destination columns.
 * May fetch data from source and perform callback method on it before generating upserts.
 */
class Ac_Sql_Updater extends Ac_Prototyped implements Ac_I_Sql_Expression {
    
    /**
     * @var string|Ac_Sql_Statement|array
     * 
     * Source of records
     * 
     * - Ac_Sql_Statement - will be part of insert-select 
     * - string - usually has to be part of SQL SELECT statement starting with FROM... keyword,
     *   except the case when both $batchCallback is callback AND $batchCallbackOnMappedColumns
     *   is FALSE. In that case, string statement must include SELECT ... with list of columns.
     * - array - array of records. Select won't be performed.
     */
    protected $src = null;
    
    /**
     * @var string
     * Name of destination table. Required.
     */
    protected $destTableName = null;
    
    /**
     * Alias of source table in select expression. Optional.
     * @var string
     */
    protected $srcTableAlias = null;
    
    /**
     * @var array
     * 
     * Mapping between target and source columns.
     * 
     * Array key is name of taget column, and array value is string, Ac_Sql_Expression, or NULL.
     * When key is number and value is string, it is considered that target and source columns 
     * are same. If you really have column with numeric name (which is weird), just use
     * numeric key (name of column) and Ac_Sql_Expression with source table alias and name of
     * column again, so valid expression will be formed.
     * 
     * Entries with NULL value in $colMap are ignored.
     * 
     * When array item is string, it is interpreted as column name; alias will be added if 
     * $this->srcTableAlias is not empty.
     * 
     * When array item is Ac_Sql_Expression, it is added verbatim.
     */
    protected $colMap = array();
    
    /**
     * @var array
     * 
     * $updateMap allows to specify which target columns should be or not be updated, 
     * or should be updated in special way.
     * 
     * Key is name of target column, and value is string, Ac_Sql_Expression, boolean, or null.
     * 
     * String semantics is different from $colMap, and string is inserted vertbatim 
     * (it is considered to be an expression).
     * 
     * Entries with NULL value in $updateMap are ignored.
     * 
     * Ac_Sql_Expression is also inserted verbatim.
     * 
     * boolean value allows to change \$updateColumnsByDefault behaviour:
     * 
     * FALSE means particular target column in omitted from ...ON DUPLICATE KEY UPDATE clause.
     * TRUE means particular target column included into UPDATE clause in form foo=VALUES(foo).
     * 
     * @see Ac_Sql_Updater::$updateColumnsByDefault Specifies default update behaviour
     */
    protected $updateMap = array();
    
    /**
     * Specifies whether columns that are in \$colMap, but NOT in \$updateMap, 
     * should be updated in target table by default, or not
     * 
     * @see Ac_Sql_Updater::$updateMap Boolean values allow to override default behaviour
     *
     * @var bool
     */
    protected $updateColumnsByDefault = true;
    
    /**
     * @var bool|callable
     * 
     * Specifies function to be appilied to source data. In this case getExpression() will fetch
     * data from source (if $this->src is not not the array of records already), apply callback
     * to it, and generate one or several "upserts".
     * 
     * By default callback is called for every source row. Callback should return same row with 
     * possible modifications. If callback returns non-array value, or an empty array, this row
     * will be no appear in INSERT statement. 
     * 
     * To make callback receive groups of records (2-dimensional arrays), 
     * set $batchCallbackFroMany to TRUE.
     * 
     * Special value TRUE means callback workflow will be applied (inserts will be created using 
     * selected rows), but no method will be called. It is useful to overcome some limitations, like
     * the case when source and destination tables are the same.
     * 
     * It is important that batchCallback returns same number and order of values as specified 
     * in colMap, otherwise INSERT statement will be incorrect.
     * 
     * @TODO automatically determine colMap from batchCallback output
     */
    protected $batchCallback = false;
    
    /**
     * @var bool
     * 
     * If TRUE, $batchCallback will be called with argument of batches of rows-to-be-insert
     * (not once per row). It should return updated batch. If empty batch is returned, 
     * it will be skipped.
     * 
     * Max number of records in batch is controlled by $batchSize property.
     */
    protected $batchCallbackForMany = false;
    
    /**
     * @var int
     * 
     * Max size of batch when callback workflow is applied. 
     * When 0, all records will be fetched and added to INSERT (not good for large datasets).
     */
    protected $batchSize = 2000;
    
    /**
     * Specifies whether $batchCallback callback should receive mapped columns from source
     * statement. When \$src is array, this has no effect. 
     * 
     * When $batchCallbackOnMappedColumns is FALSE, it is responsibility of callback to return
     * only valid columns for dest table (and they should still be listed in \$colMap).
     * 
     * @var bool
     */
    protected $batchCallbackOnMappedColumns = true;

    /**
     * @var bool
     * 
     * Whether to add "IGNORE" to the insert clause
     */
    protected $insertIgnore = false;
    
    function hasPublicVars() {
        return true;
    }
    
    protected function processBatch($insClause, $select, $strUpd, Ac_Sql_Db $db) {
        if ($this->batchCallback !== true && !is_callable($this->batchCallback)) 
            throw Ac_E_InvalidCall::wrongType('Ac_Sql_Updater->$batchCallback', $this->batchCallback, "callable");
        if (is_array($select)) {
            $rows = $select;
        } else {
            $rows = $db->fetchArray($select);
        }
        $s = $this->batchSize > 0? $this->batchSize : count($rows);
        $res = array();
        while ($slice = array_splice($rows, 0, $s)) {
            $write = array();
            if ($this->batchCallback === true) {
                $write = $slice;
            } else {
                if ($this->batchCallbackForMany) {
                    $write = call_user_func($this->batchCallback, $slice);
                } else {
                    foreach ($slice as $row) {
                        $newRow = call_user_func($this->batchCallback, $row);
                        if (isset($newRow[0]) && is_array($newRow[0])) {
                            foreach ($newRow as $item) if ($item) $write[] = $item;
                        } else {
                            if (!$newRow) continue;
                            $write[] = $newRow;
                        }
                    }
                }
            }
            if (!$write) continue;
            $write = $db->unifyInsertData($write);
            $insertStatement = $insClause."\nVALUES";
            foreach ($write as $writeRow) {
                $insertStatement .= "\n    (".$db->q($writeRow)."),";
            }
            $res[] = rtrim($insertStatement, ",").$strUpd;
        }
        return $res;
    }
    
    /**
     * @param Ac_Sql_Db $db
     */
    function getExpression($db, $asArray = false) {
        $this->check();
        $insWhat = array();
        $selWhat = array();
        $upd = array();
        $alias = $this->srcTableAlias;
        if ($alias === null && is_object($this->src) && $this->src instanceof Ac_Sql_Select) {
            $alias = $this->src->getEffectivePrimaryAlias();
        }
        $fullUpdateMap = array();
        foreach ($this->colMap as $k => $v) {
            if (is_null($v)) continue;
            if (is_numeric($k) && !(is_object($v) && $v instanceof Ac_Sql_Expression)) {
                $k = $v;
            }
            if ($this->updateColumnsByDefault) {
                $fullUpdateMap[$k] = "VALUES(".$db->n($k).")";
            }
            $insWhat[] = $db->nameQuote($k);
            $sel = $v;
            if (!(is_object($sel) && $sel instanceof Ac_I_Sql_Expression)) {
                $sel = $alias? $db->nameQuote (array($alias, $sel)) : $db->nameQuote($sel);
            }
            $selWhat[] = $db->quote(new Ac_Sql_Expression($sel)) . " AS ".$db->nameQuote($k);;
        }
        $upd = array();
        foreach ($this->updateMap as $k => $v) {
            if (is_null($v)) continue;
            if ($v === true) $v = "VALUES(".$db->n($k).")";
            $fullUpdateMap[$k] = $v;
        }
        foreach ($fullUpdateMap as $k => $v) {
            if ($v === false) continue;
            $upd[] = $db->nameQuote($k).' = '.$db->quote(new Ac_Sql_Expression($v));
        }
        $strInsWhat = "\n    ".implode(",\n    ", $insWhat)."\n";
        $strSelWhat = implode(",\n    ", $selWhat);
        $strUpd = implode(",\n    ", $upd);
        if (is_object($this->src) && $this->src instanceof Ac_Sql_Select) {
            $src = clone $this->src;
            $src->setDb($db);
            if ($this->batchCallback && !$this->batchCallbackOnMappedColumns) {
                $src = ''.$src;
            } else {
                $src = $src->getStatementTail(true);
            }
        } elseif (is_array($this->src)) {
            $src = $this->src;
            if (!$this->batchCallback) {
                $this->batchCallback = true;
            }
        } else {
            $src = $this->src;
        }
        if ($this->batchCallback && !$this->batchCallbackOnMappedColumns || is_array($src)) {
            $select = $src;
        } else {
            $select = "\nSELECT\n    {$strSelWhat}\n{$src}";
        }
        
        if ($strUpd) $strUpd = "\nON DUPLICATE KEY UPDATE\n    {$strUpd}\n";
        
        $stmt = "INSERT INTO ";
        
        if ($this->insertIgnore) $stmt = "INSERT IGNORE INTO ";
            
        $insClause = $stmt.$db->n($this->destTableName)." ({$strInsWhat})";
        
        if ($this->batchCallback) $res = $this->processBatch($insClause, $select, $strUpd, $db);
        else $res = $insClause.$select.$strUpd;
        
        if (!$asArray && is_array($res)) $res = implode("\n;\n", $res);
        else if ($asArray && !is_array($res)) $res = Ac_Util::toArray ($res);
        return $res;
    }

    function nameQuote($db) {
        throw new Ac_E_InvalidUsage("nameQuote isn't supported by ".__CLASS__);
    }
    
    function execute(Ac_Sql_Db $db = null) {
        if (is_null($db)) {
            if (is_object($this->src) && ($this->src instanceof Ac_Sql_Select)) {
                $db = $this->src->getDb();
            }
            if (!$db) $db = Ac_Sql_Db::getDefaultInstance();
        }
        if (!$db) throw new Ac_E_InvalidUsage("Cannot obtain Ac_Sql_Db instance during execute(); please provide \$db argument");
        foreach ($this->getExpression($db, true) as $stmt) {
            $db->query($stmt);
        }
    }
    
    protected function check() {
        if (!$this->src) {
            throw new Ac_E_InvalidUsage("\$src is required; setSrc() first");
        }
        if (!$this->destTableName) {
            throw new Ac_E_InvalidUsage("\$destTableName is required; setDestTableName() first");
        }
    }
    
    /** 
     * @param string|Ac_Sql_Select|array
     * @see Ac_Sql_Updater::$src
     * 
     * @TODO support Traversable 
     */
    function setSrc($src) {
        if (!(is_array($src) || is_string($src) || is_object($src) && $src instanceof Ac_Sql_Select)) {
            throw Ac_E_InvalidCall::wrongType('src', $src, ['Ac_Sql_Select', 'string', 'array']);
        }
        if (!$src) {
            throw new Ac_E_InvalidCall("\$src must be non-empty array, non-empty string, or Ac_Sql_Select");
        }
        $this->src = $src;
    }

    function getSrc() {
        return $this->src;
    }

    /**
     * @param string $destTableName
     * @throws type
     */
    function setDestTableName($destTableName) {
        if (!is_string($destTableName)) {
            throw Ac_E_InvalidCall::wrongType('destTableName', $destTableName, 'string');
        }
        if (!$destTableName) throw new Ac_E_InvalidCall("\$destTableName must not be empty");
        $this->destTableName = $destTableName;
    }

    function getDestTableName() {
        return $this->destTableName;
    }

    function setSrcTableAlias($srcTableAlias) {
        if (!is_string($srcTableAlias) && !is_null($srcTableAlias)) 
            throw Ac_E_InvalidCall::wrongType ('srcTableAlias', $srcTableAlias, ['string'|'null']);
        if (!$srcTableAlias) $srcTableAlias = null;
        $this->srcTableAlias = $srcTableAlias;
    }

    function getSrcTableAlias() {
        return $this->srcTableAlias;
    }

    protected function expandColMap(array $colMap) {
        $res = [];
        foreach ($colMap as $k => $v) {
            if (is_numeric($k) && is_string($v)) $k = $v;
            $res[$k] = $v;
        }
        return $res;
    }
    
    /**
     * @see Ac_Sql_Updater::$colMap
     * 
     * @param array $colMap colMap value (see property description)
     * @param boolan $merge Update matching keys of $colMap instead of overwriting it completely
     * 
     * Since numeric keys in $colMap are expanded during the setting phase, following is possible:
     * $updater->setColMap(['id', 'title']); // implies ['id' => 'id', 'title' => 'title']
     * $updater->setColMap(['id' => new Ac_Sql_Expression('id + 10')], true);
     * // colMap will be changed to ['id' => new Ac_Sql_Expression('id + 10'), 'title' => 'title']
     * 
     * 
     */
    function setColMap(array $colMap, $merge = false) {
        if ($merge) {
            foreach ($this->expandColMap($colMap) as $k => $v) {
                $this->colMap[$k] = $v;
            }
            return;
        }
        $this->colMap = $this->expandColMap($colMap);
    }

    /**
     * @return array
     */
    function getColMap() {
        return $this->colMap;
    }

    function setUpdateMap(array $updateMap, $override = false) {
        if ($override) {
            $this->updateMap = array_merge($this->updateMap, $updateMap);
            return;
        }
        $this->updateMap = $updateMap;
    }

    /**
     * @return array
     */
    function getUpdateMap() {
        return $this->updateMap;
    }

    function setUpdateColumnsByDefault($updateColumnsByDefault) {
        $this->updateColumnsByDefault = (bool) $updateColumnsByDefault;
    }

    /**
     * @return bool
     */
    function getUpdateColumnsByDefault() {
        return $this->updateColumnsByDefault;
    }
    
    function setBatchCallback($batchCallback) {
        if (is_numeric($batchCallback) || is_bool($batchCallback)) 
            $batchCallback = (bool) $batchCallback;
        else if (!is_callable ($batchCallback)) {
            throw Ac_E_InvalidCall::wrongType('batchCallback', $batchCallback, 
                ['numeric', 'bool', 'callable']);
        }
        $this->batchCallback = $batchCallback;
    }

    function getBatchCallback() {
        return $this->batchCallback;
    }

    function setInsertIgnore($insertIgnore) {
        $this->insertIgnore = (bool) $insertIgnore;
    }

    function getInsertIgnore() {
        return $this->insertIgnore;
    }

    function setBatchCallbackForMany($batchCallbackForMany) {
        $this->batchCallbackForMany = (bool) $batchCallbackForMany;
    }

    function getBatchCallbackForMany() {
        return $this->batchCallbackForMany;
    }

    function setBatchCallbackOnMappedColumns($batchCallbackOnMappedColumns) {
        $this->batchCallbackOnMappedColumns = (bool) $batchCallbackOnMappedColumns;
    }

    function getBatchCallbackOnMappedColumns() {
        return $this->batchCallbackOnMappedColumns;
    }

    function setBatchSize($batchSize) {
        if (!is_numeric($batchSize))
            throw Ac_E_InvalidCall::wrongType ('batchSize', $batchSize, ['numeric']);
        $batchSize = (int) $batchSize;
        if ($batchSize < 0) $batchSize = 0;
        $this->batchSize = $batchSize;
    }

    function getBatchSize() {
        return $this->batchSize;
    }    

}