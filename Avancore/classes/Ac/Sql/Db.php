<?php

abstract class Ac_Sql_Db extends Ac_Prototyped {

    static $defaultIndent = 4;
    
    var $triggerErrorsOnSqlErrors = true;
    
    var $checkIfNameQuoted = false;
    
    protected $inspector = false;
    
    protected $nextQueryArgs = array();
    
    abstract function getDbPrefix();
    
    abstract function getDbName();
    
    /**
     * @return Ac_Sql_Dialect 
     */
    abstract function getDialect();
    
    function replacePrefix($string) {
        return $this->getDialect()->replacePrefix($this->getDbPrefix(), $string);
    }
    
    function quote($value, $asArray = false) {
        return $this->q($value);        
    }
    
    function q($value, $asArray = false) {
        if (is_object($value) && (is_a($value, 'Ac_I_Sql_Expression') || method_exists($value, 'getExpression'))) {
            $res = $value->getExpression($this);
        } elseif (is_array($value)) {
            $r = array();
            foreach ($value as $v) $r[] = $this->q($v, $asArray);
            return $asArray? $r: implode(", ", $r);
        } elseif (is_null($value)) {
            $res = 'NULL';
        } else {
            $res = $this->implValueQuote($value);
        }
        return $res;
    }
    
    function nameUnqote($name) {
    	return $this->implNameUnquote($name);
    }
    
    function nameQuoteBody($name) {
    	$res = trim($this->n($name), "`");
    	return $res;
    }
    
    function nameQuote($name, $many = false) {
        return $this->n($name, $many);
    }
    
    function n($name, $many = false) {
        if (is_array($name)) {
            $r = array();
            foreach ($name as $v) $r[] = $this->n($v);
            if ($many) return implode(", ", $r);
            else return $this->implConcatNames($r);
        } elseif (is_object($name) && $name instanceof Ac_I_Sql_Expression){
            return $name->nameQuote($this);
        } elseif ($this->checkIfNameQuoted && $this->implIsNameQuoted($name)) return $name;
        $name = $this->implNameQuote($name);
        return $name;
    }
    
    function eqCriterion($value) {
        if (is_array($value)) {
            if (count($value) === 1) {
                $vals = array_values($value);
                $res = '= '.$this->q($vals[0]);
            } else {
                $res = 'IN ('.$this->q($value).')';
            }
        }
        else $res = '= '.$this->q($value);
        return $res;
    }
    
    /**
     * Returns criterion like '(col1 = val1 AND col2 = val2 AND col3 = val3)'
     * 
     * @param array $colMap ('col1' => 'val1', 'col2' => 'val2', ...)
     * @param string|bool $tableAlias Optional table name or alias to add before each column's name
     * @param bool $asArray Don't concat 'colN = valN' pairs with AND and return them as array instead
     * @return string|array
     */
    function valueCriterion($colMap, $tableAlias = false, $asArray = false) {
        $r = array();
        foreach ($colMap as $col => $value) {
            $col = $this->n($col);
            if ($tableAlias) $col = $this->n($tableAlias).'.'.$col;
            $r[] = $col.' '.$this->eqCriterion($value);
        }
        if ($asArray) {
            $res = $r;
        } else {
            $res = implode(" AND ", $r);
            if (count($r) > 1) $res = "($res)";
        } 
        return $res;
    }
  
    /**
     * @param array|string $string
     * @param int|bool $size 
     * @return array|string
     */
    static function indent($string, $size = false) {
        if ($size === false) $size = self::$defaultIndent;
        if (is_array($string)) {
            $res = array();
            foreach ($string as $k => $v) $res[$k] = self::indent($v, $size);
        } else {
            $idt = str_repeat(" ", $size);
            $res = $idt.str_replace("\n", "\n".$idt, $string);
        }
        return $res;
    }
    
    /**
     * Makes all rows to have the same keys in same order
     * Replaces missing values with new Ac_Sql_Expression('DEFAULT')
     * 
     * @param array $rows Two-dimensional associative array with insert data
     */
    function unifyInsertData($rows) {
        $allKeys = array();
        foreach ($rows as $row) {
            $allKeys = array_unique(array_merge($allKeys, array_keys($row)));
        }
        $res = array();
        $def = new Ac_Sql_Expression('DEFAULT');
        foreach ($rows as $row) {
            $newRow = array();
            foreach ($allKeys as $key) {
                if (array_key_exists($key, $row)) $newRow[$key] = $row[$key];
                    else $newRow[$key] = $def;
            }
            $res[] = $newRow;
        }
        return $res;
    }
    
    function insertStatement($tableName, $fieldValues, $multipleInserts = false, $useReplace = false) {
        $res = false;
        $bigInserts = array();
        $rows = array();
        $keys = false;
        if (!$multipleInserts) $rows = array($fieldValues);
            else $rows = $fieldValues;
        if (is_array($multipleInserts)) $keys = $multipleInserts;
        $tableName = $this->n($tableName);
        foreach ($rows as $fieldValues) {
            if (count($fieldValues)) {
                $firstVal = current($fieldValues);
                $isMultiple = is_array($firstVal);
                if (!$isMultiple) $fieldValues = array($fieldValues);
                $inserts = array();
                foreach ($fieldValues as $row) {
                    if (!$keys) $keys = array_keys($row);
                    $inserts[] = $this->q($row);
                }
            }
            $bigInserts[] = '('.implode(", ", $inserts).')';
        }
        
        if (is_string($useReplace) && strlen($useReplace)) $action = $useReplace;
        else $action = $useReplace === true? "REPLACE" : "INSERT";
        
        if (count($bigInserts)) $res = "{$action} INTO {$tableName} (".$this->n($keys, true).") VALUES \n".implode(",\n", $bigInserts);
        return $res;
    }
    
    function updateStatement($tableName, $fieldValues, $keysList, $allowPartialKey = false) {
        $tableName = $this->n($tableName);
        $keys = array();
        $res = false;
        $keysList = Ac_Util::toArray($keysList);
        foreach ($keysList as $keyName) 
            if (isset($fieldValues[$keyName])) { 
                $keys[$keyName] = $fieldValues[$keyName];
                unset($fieldValues[$keyName]);
            }
        if (count($fieldValues) && ($allowPartialKey || (count($keys) == count($keysList)))) {
            $res = "UPDATE {$tableName} SET ".implode(", ", $this->valueCriterion($fieldValues, false, true));
            if (count($keys)) $res .= " WHERE ".$this->valueCriterion($keys); 
        }
        return $res;
    }
    
    function getLimitClause($count, $offset = false, $withLimitKeyword = true) {
        return $this->getDialect()->getLimitClause($count, $offset, $withLimitKeyword);
    }
    
    function applyLimits($statement, $count, $offset = false, $orderBy = false) {
        return $this->getDialect()->applyLimits($statement, $count, $offset, $orderBy);
    }
    
    abstract protected function implValueQuote($value);
    
    abstract protected function implNameQuote($name);
    
    abstract protected function implNameUnquote($name);
    
    abstract protected function implIsNameQuoted($name);
    
    abstract protected function implConcatNames($quotedNames);
    
    abstract function fetchArray($query, $keyColumn = false, $withNumericKeys = false);
    
    abstract function fetchObjects($query, $keyColumn = false);

    abstract function fetchRow($query, $key = false, $keyColumn = false, $withNumericKeys = false, $default = false);
    
    abstract function fetchColumn($query, $colNo = 0, $keyColumn = false);
    
    abstract function fetchValue($query, $colNo = 0, $default = null);
    
    abstract function query($query);
    
    abstract function getErrorCode();
    
    abstract function getErrorDescr();

    abstract function getLastInsertId();
    
    abstract function getIfnullFunction();
    
    abstract function getResultResource($query);
    
    abstract function resultGetFieldsInfo($resultResource);
    
    abstract function resultFetchAssocByTables($resultResource, array $fieldsInfo = array());
    
    abstract function resultFetchAssoc($resultResource);
    
    abstract function resultFreeResource($resultResource);
    
    function fetchObject($query, $default = null) {
        $res = $this->fetchObjects($query);
        if (count($res)) $res = $res[0];
            else $res = $default;
        return $res;
    }
    
    function getOrderDirection($orderPart) {
        if (is_array($orderPart)) {
            foreach ($orderPart as $k => $v) $res[$k] = $this->getOrderDirection($v);
        } else {
            $res = $this->doGetOrderDirection($orderPart);
        }
        return $res;
    }
    
    function reverseOrderDirection($orderPart) {
        if (is_array($orderPart)) {
            foreach ($orderPart as $k => $v) $res[$k] = $this->reverseOrderDirection($v);
        } else {
            $res = $this->doReverseOrderDirection($orderPart);
        }
        return $res;
    }
    
    
    protected function doGetOrderDirection($strSqlOrderPart) {
        $res = !preg_match('#\sDESC\s*$#i', $strSqlOrderPart);
        return $res;
    }
    
    protected function doReverseOrderDirection($strSqlOrderPart) {
        if (preg_match($pat = '#\sASC\s*$#i', $strSqlOrderPart)) $d = preg_replace($pat, ' DESC', $strSqlOrderPart);
        elseif (preg_match($pat = '#\sDESC\s*$#i', $strSqlOrderPart)) $d = preg_replace($pat, ' ASC', $strSqlOrderPart);
        else $d = $strSqlOrderPart.' DESC';
        return $d;
    }
    
    abstract function getAffectedRows();
    
    /**
     * @return Ac_Sql_Dbi_Inspector
     */
    function getInspector() {
        if ($this->inspector === false) {
            $this->inspector = $this->getDialect()->createInspector($this);
        }
        return $this->inspector;
    }
    
    function startTransaction() {
        // TODO
    }
    
    function rollback() {
        // TODO
    }
    
    function commit() {
        // TODO
    }

    protected function intPreProcessQuery($query) {
        if (is_array($query) && $this->nextQueryArgs) {
            throw new Ac_E_InvalidUsage("Cannot mix Ac_Sql_Db::args() with array-style "
                . "\$query parameter passing");
        }
        if ($this->nextQueryArgs) {
            $q = $this->nextQueryArgs;
            array_unshift($q, $query);
            $res = $this->preProcessQuery($q);
            $this->nextQueryArgs = array();
        } else {
            $res = $this->preProcessQuery($query);
        }
        return $res;
    }
    
    /** 
     * @param array|string|object $query
     * 
     * Function to prepare queries before execution
     * Strings queries have their prefixes replaced.
     * Arrays are treated in a such way: 
     * [0 => $strQueryTemplate, 1 => $posParam1, 2 => $posParam2, 'foo' => $namedParamFoo]
     * 
     * All queries passed to methods of Ac_Sql_Db must be processed with this method.
     */
    function preProcessQuery($query) {
        
        if (is_string($query)) $res = $this->replacePrefix($query);
        elseif (is_array($query)) {
            if (!isset($query[0])) 
                throw new Ac_E_InvalidCall("array \$query must have element #0 (query-template string)");
            if (!is_string($query[0]) && !is_object($query[0])) {
                throw new Ac_E_InvalidCall("\$query[0] must be a string or an object");
            }
            $template = ''.$query[0];
            unset($query[0]);
            $quotedPosArgs = array();
            $quotedNamedArgs = array();
            foreach ($query as $key => $arg) {
                if (is_numeric($key)) $quotedPosArgs[] = $this->quote($arg);
                else $quotedNamedArgs[$key] = $this->quote($arg);
            }
            $res = $this->getDialect()->prepareSql($template, $this->getDbPrefix(), $quotedPosArgs, $quotedNamedArgs);
        } elseif (is_object($query) && $query instanceof Ac_I_Sql_Expression) {
            $res = $query->getExpression($this);
        } else {
            throw new Ac_E_InvalidCall("\$query must be either string, an Ac_I_Sql_Expression instance, or an array");
        }
        return $res;
    }
    
    function getSupportsLimitClause() {
        return $this->getDialect()->getSupportsLimitClause();
    }
    
    /**
     * @param mixed $args
     * @return Ac_Sql_Db
     */
    function args($args = array()) {
        if (!is_array($args) || func_num_args() > 1) {
            $args = func_get_args();
        }
        $this->nextQueryArgs = $args;
        return $this;
    }
    
}

