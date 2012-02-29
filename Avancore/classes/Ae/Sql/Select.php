<?php

Ae_Dispatcher::loadClass('Ae_Sql_Select_TableProvider');

class Ae_Sql_Select extends Ae_Sql_Select_TableProvider {
    
    /**
     * @var Ae_Sql_Db
     */
    var $_db = false;
    
    var $_usedAliases = array();
    
    var $primaryAlias = false;

    var $distinct = false;
    var $columns = array();
    var $otherJoins = array();
    var $otherJoinsAfter = array();
    var $orderBy = array();
    var $where = array();
    var $groupBy = array();
    var $having = array();
    var $limitOffset = false;
    var $limitCount = false;
    
    var $_allDeps = false;
    
    /**
     * Enable auto-loosening of join type in sub-tables by default
     * @var bool|AE_LOOSEN_JOIN_NEVER|AE_LOOSEN_JOIN_ALWAYS
     */
    var $autoLoosenJoins = false;
    
    /**
     * Whether to use 'USING' clause in Tables where $joinOn is numerical array 
     * @var bool
     */
    var $useUsing = false;
    
    /**
     * @param array $options
     * @param Ae_Sql_Db $db
     * @return Ae_Sql_Select
     */
    function Ae_Sql_Select(& $db, $options) {
    	$options['db'] = & $db;
    	parent::Ae_Sql_Select_TableProvider($options);
    }

    function setDb(& $db) {
    	if (!is_a($db, 'Ae_Sql_Db')) trigger_error("\$db must be an instance of Ae_Sql_Db", E_USER_ERROR);;
    	$this->_db = & $db;
    }
    
    /**
     * @return Ae_Sql_Db
     */
    function getDb() {
        if ($this->_db === false) $this->_db = new Ae_Sql_Db_Ae();
        return $this->_db;
    }
    
    /**
     * Enter description here...
     *
     * @param string|array $alias
     */
    function useAlias($alias) {
        if (is_array($alias)) {
            $this->_usedAliases = array_unique(array_merge($this->_usedAliases, $alias));
        } else {
            if (!in_array($alias, $this->_usedAliases)) $this->_usedAliases[] = $alias;
        }
    }
    
    function removeAlias($alias) {
        $this->_usedAliases = array_diff($this->_usedAliases, array($alias));
    }
    
    function removeAllAliases() {
        $this->_usedAliases = array();
    }
    
    function getUsedAliases() {
        return array_unique(array_merge($this->_usedAliases, $this->_getAliasesFromColumns()));
    }
    
    function _getAliasesFromColumns() {
    	$cols = is_array($this->columns)? $this->columns : array($this->columns);
    	$res = array();
    	foreach ($cols as $c) {
    		if (is_a($c, 'Ae_Sql_Select_Expression')) $res = array_merge($res, $c->aliases);
    	} 
    	$res = array_unique($res);
    	return $res;
    }
    
    function setUsedAliases($aliases) {
    	//if (!is_array($aliases)) trigger_error ("\$aliases must be an array", E_USER_ERROR);
    	if (!is_array($aliases)) $aliases = strlen($aliases)? array($aliases) : array();
        $this->_usedAliases = array_unique($aliases); 
    }
    
    function listTables() {
        return array_keys($this->_tables);
    }
    
    function quote($str) {
        return $this->_db->quote($str);
    }
    
    function nameQuote($str) {
        return $this->_db->nameQuote($str);
    }
    
    function getStatement() {
        $parts[] = $this->getColumnsList(true, true);
        if (strlen($s = $this->getStatementTail(true))) $parts[] = $s;
        $res = implode("\n", $parts);
        return $res;
    }
    
    function getStatementTail($withFromKeyword = false) {
        if (strlen($s = $this->getFromClause($withFromKeyword))) $parts[] = $s;
        if (strlen($s = $this->getWhereClause(true))) $parts[] = $s;
        if (strlen($s = $this->getGroupByClause(true))) $parts[] = $s;
        if (strlen($s = $this->getHavingClause(true))) $parts[] = $s;
        if (strlen($s = $this->getOrderByClause(true))) $parts[] = $s;
        if (strlen($s = $this->getLimitClause(true))) $parts[] = $s;
        $res = implode("\n", $parts);
        return $res;
    }
    
    function getColumnsList($withSelectKeyword = false, $addAsteriskIfNoColumns = false, $asArray = false) {
    	if (!is_array($this->columns)) $cols = array($this->columns);
    		else $cols = $this->columns;
    	$columns = array();
    	foreach ($cols as $k => $c) {
    		if (is_a($c, 'Ae_Sql_Expression')) $cl = $c->getExpression($this->_db);
    			else $cl = $c;
    		if (!is_numeric($k)) $cl = $cl.' AS '.$this->_db->nameQuote($k);
    		$columns[] = $cl;
    	}
    	if ($asArray) {
    	    $res = $columns;
    	} else {
	        $res = implode(", ", $columns);
	        if ($addAsteriskIfNoColumns && !strlen($res)) $res = '*';
	        if ($this->distinct) $res = 'DISTINCT '.$res;
	        if ($withSelectKeyword && strlen($res)) $res = "SELECT ".$res;
    	}
        return $res;
    }
    
    function getFromClause($withFromKeyword = false, $skipAliases = array(), $withFirstAlias = true) {
        $orderedAliases = $this->_getOrderedAliases($this->getUsedAliases());
        $res = '';
        if ($this->otherJoins) $res .= implode("\n", $this->otherJoins);
        if (!$withFirstAlias) $skipAliases[] = $orderedAliases[0];
        foreach (array_diff($orderedAliases, $skipAliases) as $a) {
            $tbl = & $this->getTable($a);
            $jcp = $tbl->getJoinClausePart();
            if (strlen($jcp)) {
            	if ($jcp{0} == ',') $res = $res.",\n".substr($jcp, 1);
            	else {
            		if (strlen($res)) $res .= "\n";
            		$res .= $jcp;
            	}
            }
        }
        $res = trim($this->_db->indent($res));
        if ($withFromKeyword && strlen($res)) $res = 'FROM '.$res;
        if ($this->otherJoinsAfter) $res .= implode("\n", $this->otherJoinsAfter);
        return $res;
    }
    
    /**
     * @param $withWhereKeyword
     * @param $asArray Can be FALSE, TRUE or 'plain'
     */
    function getWhereClause($withWhereKeyword = false, $asArray = false) {
        $res = '';
        if ($asArray === true) $res = is_array($this->where)? $this->where : array($this->where);
        else {
            if ($this->where) 
                if (!is_array($this->where)) $res = $this->where;
                else {
                    $wheres = array();
                    foreach ($this->where as $w) {
                        if (is_array($w)) {
                            if (($c = count($w)) == 1) {
                                $wheres[] = implode("", $w);
                            } elseif ($c > 1) {
                                $wheres[] = "(".implode(" ) OR ( ", $w).")";
                            }
                        } else $wheres[] = $w; 
                    }
                    if ($asArray === 'plain') $res = $wheres;
                        else $res = count($wheres) > 1? $this->_db->indent("\n(".implode("\n) AND (", $wheres).")") : implode("", $wheres);                    
                }
            if ($withWhereKeyword && is_string($res) && strlen($res)) $res = 'WHERE '.$res;
        }
        return $res;   
    }
    
    function getGroupByClause($withGroupByKeyword = false) {
        $res = '';
        if ($this->groupBy) $res = implode(', ', $this->groupBy);
        if ($withGroupByKeyword && strlen($res)) $res = 'GROUP BY '.$res;
        return $res;
    }
    
    function getHavingClause($withHavingKeyword = false, $asArray = false) {
        $res = '';
        if ($asArray) $res = is_array($this->having)? $this->having : array($this->having);
        else {
            if ($this->having) 
                $res = count($this->having) > 1? '('.implode(') AND (', $this->having).')' : implode('', $this->having);
            if ($withHavingKeyword && strlen($res)) $res = 'HAVING '.$res;
        }
        return $res;   
    }
    
    function getOrderByClause($withOrderByKeyword = false, $asArray = false) {
        $ob = Ae_Util::toArray($this->orderBy);
        if ($asArray) $res = $ob;
        else {
            $res = implode(", ", $ob);
            if ($withOrderByKeyword && strlen($res)) $res = 'ORDER BY '.$res;
        }
        return $res;
    }
    
    function getLimitClause($withLimitKeyword = false) {
        $res = '';
//        if (strlen($this->limitCount)) {
//            $res = $this->limitCount;
//            if (strlen($this->limitOffset)) $res = $this->limitOffset.', '.$res;
//        }
//        if ($withLimitKeyword && strlen($res)) $res = 'LIMIT '.$res;
        if (strlen($this->limitCount)) {
            $res = $this->_db->getLimitClause($this->limitCount, $this->limitOffset, $withLimitKeyword);
        }
        return $res;
    }
    
    function _getOrderedAliases($usedAliases) {
        $allRequiredAliases = array();
        if (!count($usedAliases)) {
            $aliases = array_keys($this->_tables);
            $usedAliases = array($this->getEffectivePrimaryAlias());
        }
        foreach ($usedAliases as $alias) {
            $t = & $this->getTable($alias);
            if (is_object($t)) $allRequiredAliases = array_merge($allRequiredAliases, $t->getAllRequiredAliases());
        }
        $allRequiredAliases = array_unique($allRequiredAliases);
        $deps = $this->_getDeps($allRequiredAliases);
        $this->_allDeps = $deps;
        $res = array();
//        foreach (array_keys($this->_tables) as $k) {
//            if (in_array($k, $allRequiredAliases)) $res[] = $k;
//        }
		$res = array_values($allRequiredAliases);
        return $res;
    }
    
    function _getDeps($tableAliases) {
        $res = array();
        foreach ($tableAliases as $ta) {
            $t = & $this->getTable($ta);
            $res[$ta] = $t->getDirectRequiredAliases();
        }
        return $res;
    }
    
    function _compareByDeps($alias1, $alias2) {
        // A. table 1 depends on table 2: alias1 less then alias 2
        if (isset($this->_allDeps[$alias1]) && in_array($alias2, $this->_allDeps[$alias1])) {
            $res = 1;
            $sgn = '>';
        // B. table 2 depends on table 1: alias1 greater then alias 2
        } elseif (isset($this->_allDeps[$alias2]) && in_array($alias1, $this->_allDeps[$alias2])) {
            $res = -1;
            $sgn = '<';
        } else {
            $res = 0;
            $sgn = '=';
        }
        
        return $res; 
    }
    
    function getEffectivePrimaryAlias() {
        if ($this->primaryAlias !== false) $res = $this->primaryAlias;
        else {
            $l = $this->listTables();
            $t = & $this->getTable($l[0]);
            $res = $t->getIdentifier(); 
        }
        return $res;
    }
    
    /**
     * @return Ae_Model_Collection
     */
    function & createCollection($mapperClass = false, $pkName = false) {
        if (!strlen($mapperClass) && !strlen($pkName)) trigger_error("Even mapper class or pk name must be provided", E_USER_ERROR);
        $res = new Ae_Model_Collection();
        $orderedAliases = $this->_getOrderedAliases($this->getUsedAliases());
        if (!count($orderedAliases)) {
            $orderedAliases = array($this->getEffectivePrimaryAlias());
        }
        $t = & $this->getTable($this->getEffectivePrimaryAlias());
            
        if ($mapperClass) {
            $mapper = & Ae_Dispatcher::getMapper($mapperClass);
            if ($mapper->tableName !== $t->name) 
                trigger_error("Table of '{$mapperClass}' is '{$mapper->tableName}' and does not match name of primary table '{$t->name}'", E_USER_WARNING);
            $res->useMapper($mapperClass);
        }
        else {
            $res->useNoMapper($t->name, $pkName);
        }
        $res->setAlias($orderedAliases[0]);
        foreach ($this->otherJoins as $j) $res->addJoin($j);
        for ($i = 1; $i < count($orderedAliases); $i++) {
            $tbl = & $this->getTable($orderedAliases[$i]);
            $res->addJoin($tbl->getJoinClausePart());
        }
        foreach ($this->otherJoinsAfter as $j) $res->addJoin($j);
        if ($this->where) {
            if (is_array($this->where)) foreach ($this->getWhereClause(false, 'plain') as $w) $res->addWhere($w);
            else $res->addWhere($this->where);
        }
        if ($this->orderBy) foreach ($this->orderBy as $o) $res->addOrder($o);
        if ($this->groupBy) $res->setGroupBy($this->getGroupByClause(false));
        if ($this->having) $res->setHaving($this->having);
        if ($this->limitCount) {
            $res->setLimits($this->limitOffset, $this->limitCount);
        }
        
        return $res;
    }
    
    /**
     * Detect aliases within the expression from $possibleAliases list.
     */
    function findAliases($expression, $possibleAliases = false) {
        if ($possibleAliases === false) {
            if (isset($this) && is_a($this, 'Ae_Sql_Select')) $possibleAliases = $this->listTables();
                else $possibleAliases = array();
        }
        $aa = '(?P<alias>'.implode("|", $possibleAliases).')';
        preg_match_all("/\\b{$aa}\\b/", $expression, $matches);
        if ($matches && isset($matches['alias'])) $res = array_unique($matches['alias']);
        return $res;  
    }
    
    function __toString() {
    	return $this->getStatement();
    }
    
    function getExpression() {
    	return $this->__toString();
    }
    
    function cleanupReferences() {
        $this->_db = null;
        foreach ($this->_tables as $t) {
            $t->_sqlSelect = null;
        }
        parent::cleanupReferences();
    }
    
}

?>
