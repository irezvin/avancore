<?php

class Ac_Sql_Select extends Ac_Sql_Select_TableProvider implements Ac_I_Sql_Expression {
    
    /**
     * @var Ac_Sql_Db
     */
    var $_db = false;
    
    var $_usedAliases = array();
    
    protected $primaryAlias = false;
    
    protected $effectivePrimaryAlias = false;

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
    
    protected $parts = array();
    
    protected $state = array();
    
    var $_allDeps = false;
    
    /**
     * Enable auto-loosening of join type in sub-tables by default
     * @var bool|AC_LOOSEN_JOIN_NEVER|AC_LOOSEN_JOIN_ALWAYS
     */
    var $autoLoosenJoins = false;
    
    /**
     * Whether to use 'USING' clause in Tables where $joinOn is numerical array 
     * @var bool
     */
    var $useUsing = false;
    
    /**
     * @param array $options
     * @param Ac_Sql_Db $db
     * @return Ac_Sql_Select
     */
    function __construct($db, array $options = array()) {
        if (is_array($db) && func_num_args() == 1) {
            $options = $db;
        } else {
            $options['db'] = $db;
        }
    	parent::__construct($options);
    }

    function setDb(Ac_Sql_Db $db) {
    	$this->_db = $db;
    }
    
    function hasDb() {
        return $this->_db !== false;
    }
    
    function nameQuote($db) {
        return $this->getExpression($db);
    }
    
    function __toString() {
    	return $this->getStatement();
    }
    
    function getExpression($db) {
        if (!$this->_db && is_object($db) && $db instanceof Ac_Sql_Db) $this->setDb($db);
    	return $this->__toString();
    }
    
    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        if ($this->_db === false) return new Ac_Sql_Db_Ae();
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
    
    /**
     * Returns all aliases including ones used by the parts
     */
    function getAllUsedAliases() {
        $this->beginCalc();
        $res = $this->_getOrderedAliases($this->getUsedAliases());
        $this->endCalc();
        return $res;
    }
    
    function _getAliasesFromColumns() {
    	$cols = is_array($this->columns)? $this->columns : array($this->columns);
    	$res = array();
    	foreach ($cols as $c) {
    		if (is_object($c) && $c instanceof Ac_Sql_Select_Expression) {
                $res = array_merge($res, $c->aliases);
            }
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
    
    function q($str) {
        return $this->_db->q($str);
    }
    
    function n($str) {
        return $this->_db->n($str);
    }
    
    function getStatement() { /** @TODO: fix issue when otherJoins are put BEFORE primary alias! */
        $this->beginCalc();
        $parts[] = $this->getColumnsList(true, true);
        if (strlen($s = $this->getStatementTail(true))) $parts[] = $s;
        $res = implode("\n", $parts);
        $this->endCalc();
        return $res;
    }
    
    protected $calc = 0;
    
    protected function beginCalc() {
        if (!$this->calc) {
            $this->calc++;
            $this->pushState();
            $this->applyParts();
        }
    }
    
    protected function endCalc() {
        if ($this->calc) {
            $this->calc--;
            $this->popState();
        }
    }
    
    function getStatementTail($withFromKeyword = false) {
        $this->beginCalc();
        if (strlen($s = $this->getFromClause($withFromKeyword))) $parts[] = $s;
        if (strlen($s = $this->getWhereClause(true))) $parts[] = $s;
        if (strlen($s = $this->getGroupByClause(true))) $parts[] = $s;
        if (strlen($s = $this->getHavingClause(true))) $parts[] = $s;
        if (strlen($s = $this->getOrderByClause(true))) $parts[] = $s;
        if (strlen($s = $this->getLimitClause(true))) $parts[] = $s;
        $res = implode("\n", $parts);
        $this->endCalc();
        return $res;
    }
    
    function getColumnsList($withSelectKeyword = false, $addAsteriskIfNoColumns = false, $asArray = false) {
        $this->beginCalc();
    	if (!is_array($this->columns)) $cols = array($this->columns);
    		else $cols = $this->columns;
    	$columns = array();
    	foreach ($cols as $k => $c) {
    		if (is_object($c) && $c instanceof Ac_I_Sql_Expression) $cl = $c->getExpression($this->_db);
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
        $this->endCalc();
        return $res;
    }
    
    function getFromClause($withFromKeyword = false, $skipAliases = array(), $withFirstAlias = true) {
        $this->beginCalc();
        $orderedAliases = $this->_getOrderedAliases($this->getUsedAliases());
        $res = '';
        if ($this->otherJoins) $res .= implode("\n", $this->otherJoins);
        if (!$withFirstAlias) $skipAliases[] = $orderedAliases[0];
        $first = true;
        foreach (array_diff($orderedAliases, $skipAliases) as $a) {
            $tbl = $this->getTable($a);
            $jcp = $tbl->getJoinClausePart(false, $first);
            if (strlen($jcp)) {
            	if ($jcp{0} == ',') $res = $res.",\n".substr($jcp, 1);
            	else {
            		if (strlen($res)) $res .= "\n";
            		$res .= $jcp;
            	}
            }
            $first = false;
        }
        $res = trim($this->_db->indent($res));
        if ($withFromKeyword && strlen($res)) $res = 'FROM '.$res;
        if ($this->otherJoinsAfter) $res .= ' '.implode("\n", $this->otherJoinsAfter);
        $this->endCalc();
        return $res;
    }
    
    /**
     * @param $withWhereKeyword
     * @param $asArray Can be FALSE, TRUE or 'plain'
     */
    function getWhereClause($withWhereKeyword = false, $asArray = false) {
        $this->beginCalc();
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
        $this->endCalc();
        return $res;   
    }
    
    function getGroupByClause($withGroupByKeyword = false) {
        $this->beginCalc();
        $res = '';
        if ($this->groupBy) $res = is_array($this->groupBy)? 
            implode(', ', $this->groupBy) : $this->groupBy;
        if ($withGroupByKeyword && strlen($res)) $res = 'GROUP BY '.$res;
        $this->endCalc();
        return $res;
    }
    
    function getHavingClause($withHavingKeyword = false, $asArray = false) {
        $this->beginCalc();
        $res = '';
        if ($asArray) $res = is_array($this->having)? $this->having : array($this->having);
        else {
            if ($this->having) 
                $res = count($this->having) > 1? '('.implode(') AND (', $this->having).')' : implode('', $this->having);
            if ($withHavingKeyword && strlen($res)) $res = 'HAVING '.$res;
        }
        $this->endCalc();
        return $res;   
    }
    
    function getOrderByClause($withOrderByKeyword = false, $asArray = false) {
        $this->beginCalc();
        $ob = Ac_Util::toArray($this->orderBy);
        // remove empty strings
        foreach ($ob as $k => $v) if (is_array($v)? !count($v) : !strlen($v)) unset($ob[$k]);
        if ($asArray) $res = $ob;
        else {
            $res = Ac_Util::implode_r(", ", $ob);
            if ($withOrderByKeyword && strlen($res)) $res = 'ORDER BY '.$res;
        }
        $this->endCalc();
        return $res;
    }
    
    function getLimitClause($withLimitKeyword = false) {
        if ($this->getDb()->getSupportsLimitClause()) {
            $this->beginCalc();
            $res = '';
            if (strlen($this->limitCount)) {
                $res = $this->_db->getLimitClause($this->limitCount, $this->limitOffset, $withLimitKeyword);
            }
            $this->endCalc();
        } else {
            $res = '';
        }
        return $res;
    }
    
    function _getOrderedAliases($usedAliases) {
        $allRequiredAliases = array($this->getEffectivePrimaryAlias());
        if (!count($usedAliases)) {
            $aliases = array_keys($this->_tables);
            $usedAliases = array($this->getEffectivePrimaryAlias());
        }
        foreach ($usedAliases as $alias) {
            $t = $this->getTable($alias);
            if ($t->alias !== $this->getEffectivePrimaryAlias()) {
                if (is_object($t)) $allRequiredAliases = array_merge($allRequiredAliases, $t->getAllRequiredAliases());
            }
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
            $t = $this->getTable($ta);
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
        if ($this->effectivePrimaryAlias === false) {
            if ($this->primaryAlias !== false) $this->effectivePrimaryAlias = $this->primaryAlias;
            else {
                $l = $this->listTables();
                $t = $this->getTable($l[0]);
                $this->effectivePrimaryAlias = $t->getIdentifier(); 
            }
        }
        return $this->effectivePrimaryAlias;
    }
    
    /**
     * @return Ac_Model_Collection
     */
    function createCollection($mapperClass = false, $pkName = false, $ignorePrimaryAlias = false) {
        if (!strlen($mapperClass) && !strlen($pkName)) trigger_error("Even mapper class or pk name must be provided", E_USER_ERROR);
        $res = new Ac_Model_Collection();
        
        $this->beginCalc();
        
        $orderedAliases = $this->_getOrderedAliases($this->getUsedAliases());
        if (!count($orderedAliases)) {
            $orderedAliases = array($this->getEffectivePrimaryAlias());
        }
        $t = $this->getTable($this->getEffectivePrimaryAlias());
            
        if ($mapperClass) {
            $mapper = Ac_Model_Mapper::getMapper($mapperClass);
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
            $tbl = $this->getTable($orderedAliases[$i]);
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
        
        $this->endCalc();
        
        return $res;
    }
    
    /**
     * Detect aliases within the expression from $possibleAliases list.
     */
    function findAliases($expression, $possibleAliases = false) {
        if ($possibleAliases === false) {
            if (isset($this) && is_a($this, 'Ac_Sql_Select')) $possibleAliases = $this->listTables();
                else $possibleAliases = array();
        }
        $aa = '(?P<alias>'.implode("|", $possibleAliases).')';
        preg_match_all("/\\b{$aa}\\b/", $expression, $matches);
        if ($matches && isset($matches['alias'])) $res = array_unique($matches['alias']);
        return $res;  
    }
    
    function cleanupReferences() {
        $this->_db = null;
        parent::cleanupReferences();
    }
    
    function setParts(array $parts) {
        $this->parts = Ac_Prototyped::factoryCollection($parts, 'Ac_Sql_Part', array(), 'id', true);
    }
    
    function addParts(array $parts) {
        $this->parts = array_merge($this->parts, Ac_Prototyped::factoryCollection($parts, 'Ac_Sql_Part', array(), 'id', true));
    }
    
    function listParts() {
        return array_keys($this->parts);
    }
    
    /**
     * @return Ac_Sql_Part
     */
    function getPart($id, $dontThrow = false) {
        $res = null;
        if (isset($this->parts[$id])) $res = $this->parts[$id];
            elseif (!$dontThrow) throw new Exception("No such part: '$id'");
        return $res;
    }
    
    protected function applyParts() {
        foreach ($this->parts as $part)  {
            $part->applyToSelect($this);
        }
    }
    
    protected function listStateVars() {
        return array('_usedAliases', 'distinct', 'columns', 'otherJoins', 'otherJoinsAfter', 'orderBy', 'where', 'groupBy', 'having', 'limitOffset', 'limitCount');
    }
    
    protected function pushState() {
        $s = array();
        foreach ($this->listStateVars() as $v) $s[$v] = $this->$v;
        array_push($this->state, $s);
    }
    
    protected function popState() {
        foreach ( array_pop($this->state) as $k => $v) $this->$k = $v;
    }
    
    function __clone() {
        parent::__clone();
        foreach ($this->parts as $i => $p) {
            $this->parts[$i] = clone $p;
        }
        if (is_array($this->columns)) {
            foreach ($this->columns as $i => $c) {
                if ($c instanceof Ac_I_Sql_Expression) 
                    $this->columns[$i] = clone $c;
            }
        }
        $this->_allDeps = false;
    }
    
    function __get($var) {
        if (method_exists($this, $m = 'get'.$var)) return $this->$m();
        else Ac_E_InvalidCall::noSuchProperty ($this, $var);
    }

    function __set($var, $value) {
        if (method_exists($this, $m = 'set'.$var)) $this->$m($value);
        else throw Ac_E_InvalidCall::noSuchProperty ($this, $var);
    }
    
    function setPrimaryAlias($primaryAlias) {
        if ($primaryAlias !== ($oldPrimaryAlias = $this->primaryAlias)) {
            $this->primaryAlias = $primaryAlias;
            $this->effectivePrimaryAlias = false;
        }
    }

    function getPrimaryAlias() {
        return $this->primaryAlias;
    }
    
    /**
     * @return Ac_Sql_Select
     */
    function cloneWithAppliedParts() {
        $res = clone $this;
        $res->applyParts();
        foreach ($res->listParts() as $partId) {
            $res->getPart($partId)->applied = false;
        }
        return $res;
    }
    
}

