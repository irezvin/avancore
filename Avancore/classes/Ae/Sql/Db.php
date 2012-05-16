<?php

abstract class Ae_Sql_Db {

    var $defaultIndent = 4;
    
    var $triggerErrorsOnSqlErrors = true;
    
    var $checkIfNameQuoted = true;
    
    function quote($value, $asArray = false) {
        return $this->q($value);        
    }
    
    function q($value, $asArray = false) {
        if (is_object($value) && (is_a($value, 'Ae_Sql_Expression') || method_exists($value, 'getExpression'))) {
            $res = $value->getExpression($this);
        } elseif (is_array($value)) {
            $r = array();
            foreach ($value as $v) $r[] = $this->q($v, $asArray);
            return $asArray? $r: implode(", ", $r);
        } else {
            $res = $this->implValueQuote($value);
        }
        return $res;
    }
    
    function nameUnqote($name) {
    	$res = $this->implNameUnquote($name);
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
    function indent($string, $size = false) {
        if ($size === false) $size = $this->defaultIndent;
        if (is_array($string)) {
            $res = array();
            foreach ($string as $k => $v) $res[$k] = $this->indent($v, $size);
        } else {
            $idt = str_repeat(" ", $size);
            $res = $idt.str_replace("\n", "\n".$idt, $string);
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
        foreach ($rows as $fieldValues) {
            if (count($fieldValues)) {
                $tableName = $this->n($tableName);
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
        $action = $useReplace? "REPLACE" : "INSERT";
        if (count($bigInserts)) $res = "{$action} INTO {$tableName} (".$this->n($keys, true).") VALUES ".implode(", ", $bigInserts);
        return $res;
    }
    
    function updateStatement($tableName, $fieldValues, $keysList, $allowPartialKey = false) {
        $tableName = $this->n($tableName);
        $keys = array();
        $res = false;
        $keysList = Ae_Util::toArray($keysList);
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
        if (strlen($count)) {
            $res = $count;
            if (strlen($offset)) $res = $offset.', '.$res;
        }
        if ($withLimitKeyword && strlen($res)) $res = 'LIMIT '.$res;
        return $res;
    }
    
    function applyLimits($statement, $count, $offset = false, $orderBy = false) {
        return $statement.' '.$this->getLimitClause($count, $offset, $orderBy);
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
    
}

?>