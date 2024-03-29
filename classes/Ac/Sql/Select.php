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
    
    /**
     * Strings to put before joined aliases. If key of array matches alias of the table, the table won't be added
     * along with 'regular joins' (useful when parts need to build custom joins)
     */
    var $otherJoins = array();
    
    /**
     * Strings to put after joined aliases. If key of array matches alias of the table, the table won't be added
     * along with 'regular joins' (useful when parts need to build custom joins)
     */
    var $otherJoinsAfter = array();
    
    /**
     * Strings to put INSTEAD OF joined tables with matched keys (useful when parts need to build custom joins).
     * Keys that don't match ones in $_usedAliases will be ignored.
     * @var array 
     */
    var $joinOverrides = array();
    
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
        if ($this->_db === false) return Ac_Sql_Db::getDefaultInstance();
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
	        $res = implode(",\n    ", $columns);
	        if ($addAsteriskIfNoColumns && !strlen(trim($res))) $res = '*';
            $res = "\n    ".$res;
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
        
        // Ignore aliases overridden by otherJoins and otherJoinsAfter
        if ($this->otherJoins) {
            foreach (array_keys($this->otherJoins) as $j) {
                if (!is_numeric($j)) $skipAliases[] = $j;
            }
        }
        if ($this->otherJoinsAfter) {
            foreach (array_keys($this->otherJoinsAfter) as $j) {
                if (!is_numeric($j)) $skipAliases[] = $j;
            }
        }
        
        $oj = $this->otherJoins;
        $na = array();
        if (!$withFirstAlias) {
            $skipAliases[] = $orderedAliases[0];
            if ($oj !== false) $res .= implode("\n", $this->otherJoins);
            $oj = false;
        }
        $first = true;
        foreach (array_diff($orderedAliases, $skipAliases) as $a) {
            if (in_array($a, $na)) continue;
            if (isset($this->joinOverrides[$a])) {
                $jcp = $this->joinOverrides[$a];
            } else {
                $jcp = $this->getTable($a)->getJoinClausePart($na, false, $first);
            }
            if (strlen($jcp)) {
            	if ($jcp[0] == ',') $res = $res.",\n".substr($jcp, 1);
            	else {
            		if (strlen($res)) $res .= "\n";
            		$res .= $jcp;
            	}
            }
            if ($oj !== false) {
                 $res .= implode("\n", $this->otherJoins);
                 $oj = false;
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
    
    function getAllAliases() {
        return $this->_getOrderedAliases($this->getUsedAliases());
    }
    
    function _getOrderedAliases($usedAliases) {
        $allRequiredAliases = array($this->getEffectivePrimaryAlias());
        if (!count($usedAliases)) {
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

    function getMapper() {
        foreach ($this->listTableProviders() as $tp) {
            $prov = $this->getTableProvider($tp);
            if (method_exists($prov, 'getMapper')) return $prov->getMapper();
        }
        return null;
    }
    
    /**
     * @return Ac_Model_Collection_Mapper
     */
    function createCollection(Ac_Model_Mapper $mapper = null) {
        
        if (is_null($mapper)) $mapper = $this->getMapper();
        if (!$mapper) {
            throw Ac_E_InvalidUsage(__METHOD__.
                ": cannot find associated Mapper; plase provide \$mapper argument"
            );
        }
        return new Ac_Model_Collection_SqlMapper([
            'mapper' => $mapper,
            'sqlSelect' => $this
        ]);
        
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
    
    function setPartValues(array $partValues, $resetOthers = false) {
        foreach ($partValues as $k => $v) $this->getPart($k)->setValue($v);
        if ($resetOthers) {
            foreach (array_diff($this->listParts(), array_keys($partValues)) as $k) {
                $this->getPart($k)->setValue();
            }
        }
    }
    
    /**
     * @return array
     */
    function getPartValues() {
        $res = [];
        foreach ($this->listParts() as $k) {
            $part = $this->getPart($k);
            if ($part->doesApply()) $res[$k] = $part->getValue();
        }
        return $res;
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
        return array('_usedAliases', 'distinct', 'columns', 'otherJoins', 'otherJoinsAfter', 'joinOverrides', 'orderBy', 'where', 'groupBy', 'having', 'limitOffset', 'limitCount');
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
    
    /**
     * @return array
     */
    function fetchArray($keyColumn = false, $withNumericKeys = false) {
        return $this->getDb()->fetchArray($this, $keyColumn, $withNumericKeys);
    }
    
    /**
     * @return object[]
     */
    function fetchObjects($keyColumn = false) {
        return $this->getDb()->fetchObjects($this, $keyColumn, $withNumericKeys);
    }

    /**
     * @return Ac_Model_Object[]
     */
    function fetchInstances($keysToList = false, Ac_Model_Mapper $mapper = null) {
        
        if (is_null($mapper)) $mapper = $this->getMapper();
        if (!$mapper) {
            throw Ac_E_InvalidUsage(__METHOD__.
                ": cannot find associated Mapper; plase provide \$mapper argument"
            );
        }
        return $mapper->loadFromRows($this->fetchArray(), $keysToList);
        
    }

    /**
     * @return array[]
     */
    function fetchRow($key = false, $keyColumn = false, $withNumericKeys = false, $default = false) {
        return $this->getDb()->fetchRow($this, $key, $keyColumn, $withNumericKeys, $default);
    }
    
    /**
     * @return array[]
     */
    function fetchColumn($colNo = 0, $keyColumn = false) {
        return $this->getDb()->fetchColumn($this, $colNo, $keyColumn);
    }
    
    /**
     * @return mixed
     */
    function fetchValue($query, $colNo = 0, $default = null) {
        return $this->getDb()->fetchValue($this, $colNo, $default);
    }    
    
}

