<?php

/**
 * When $Ac_Sql_Select_Table->autoLoosenJoinType == AC_LOOSEN_JOIN_GLOBAL, loosening will be performing when $sqlSelect->autoLoosenJoins is TRUE. 
 */
define('AC_LOOSEN_JOIN_GLOBAL', -1);

/**
 * When $Ac_Sql_Select_Table->autoLoosenJoinType == AC_LOOSEN_JOIN_NEVER, no loosening will be performed. 
 */
define('AC_LOOSEN_JOIN_NEVER', 0);

/**
 * When $Ac_Sql_Select_Table->autoLoosenJoinType == AC_LOOSEN_JOIN_AS_PARENT, loosening will be applied (if necessary) disregarding to $sqlSelect->autoLosenJoins value. 
 */
define('AC_LOOSEN_JOIN_ALWAYS', 1);

/**
 * Ac_Sql_Select_Table should check $useUsing of sql statement
 */
define('AC_USE_USING_GLOBAL', -1);
define('AC_USE_USING_NEVER', 0);
define('AC_USE_USING_ALWAYS', 1);

/**
 * Represents table reference with JOIN in SQL SELECT statement family
 */
class Ac_Sql_Select_Table {
    
    /**
     * SQL statement family to which current table reference belongs
     * @var Ac_Sql_Select
     */
    var $_sqlSelect = false;
    
    /**
     * Name of source table or Ac_Sql_Expression with subselect (and proper parenthesis).
     * @var string|Ac_Sql_Expression
     */
    var $name = false;
    
    var $alias = false;
    
    var $joinsAlias = false;
    
    var $otherRequiredAliases = array();
    
    var $joinType = 'INNER JOIN';
    
    /**
     * Whether to automatically change joinType to 'LEFT JOIN' or 'RIGHT JOIN' if it's joined to a table that is joined by a LEFT or a RIGHT join
     * @var unknown_type
     */
    var $autoLoosenJoinType = AC_LOOSEN_JOIN_GLOBAL;
    
    var $useUsing = AC_USE_USING_GLOBAL;
    
    /**
     * Join condition.
     * 
     * Possible values are:
     * - array (myColumn => otherColumn) for traditional join clause 
     * 	 (will be translated to 'ON alias.myColumn = joinsAlias.otherColumn AND alias.myColumn2 = joinAlias.otherColumn2)...
     * - array (column1, column2) for USING(column1, column2)
     * - string 'someDifficultSqlExpression' to insert it immediately after 'ON' keyword. 'ON' keyword won't be inserted if string starts from 'USING' or 'ON'.
     *   Examples (let's consider joinType == INNER JOIN): 
     *   	- "myAlias.foo = '1' and myAlias.bar = otherAlias.bar" will result in "LEFT JOIN otherAlias ON myAlias.foo = '1' and myAlias.bar = otherAlias.bar";
     *   	- "USING(foo)" will result in "LEFT JOIN otherAlias USING(foo)"
     *   	- "ON myAlias.foo = otherAlias.bar" will result in "LEFT JOIN otherAlias ON myAlias.foo = otherAlias.bar"
     *    
     *  @var array|string
     */
    var $joinsOn = false;
    
    var $isDetail = false;
    
    var $useIndex = false;
    
    var $_allRequiredAliases = false;
    
    /**
     * @var Ac_Sql_Select_TableProvider
     */
    var $_tableProvider = false;
    
    var $omitInFromClause = false;
    
    /**
     * @param array $options
     * @param Ac_Sql_Select_TableProvider $tableProvider
     * @return Ac_Sql_Select_Table
     */
    function Ac_Sql_Select_Table($tableProvider, $options = array()) {
        if ($tableProvider) $options['tableProvider'] = $tableProvider;
        Ac_Util::bindAutoparams($this, $options, true);
    }
    
    /**
     * @param Ac_Sql_Select $sqlSelect
     */
    function getSqlSelect($required = false) {
        if ($this->_sqlSelect === false) {
            if ($this->_tableProvider) $this->_sqlSelect = $this->_tableProvider->getSqlSelect();
        }
        if ($required && !$this->_sqlSelect)
            trigger_error("Cannot retrieve an instance of Ac_Sql_Select (it isn't in any of table' parents)", 
                E_USER_ERROR);
    	return $this->_sqlSelect;
    }
    
    function getDb($required = false) {
    	$s = $this->getSqlSelect($required);
    	$res = null;
    	if ($s) $res = $s->getDb();
    	if ($required && !$res) trigger_error('Cannot retreive an instance of Ac_Sql_Db', E_USER_ERROR);
    	return $res;
    }
    
    function notifyParentChanged() {
        $this->_sqlSelect = false;
    }
    
    /**
     * @param Ac_Sql_Select $sqlSelect
     */
    function setSqlSelect(Ac_Sql_Select $sqlSelect) {
        $this->_tableProvider = $sqlSelect;
    }
    
    function setTableProvider(Ac_Sql_Select_TableProvider $tableProvider) {
    	$this->_tableProvider = $tableProvider;
        $this->notifyParentChanged();
        $this->_allRequiredAliases = false;
    }
    
    /**
     * @return Ac_Sql_Select_TableProvider
     */
    function getTableProvider() {
    	return $this->_tableProvider;
    }
    
    /**
     * @return Ac_Sql_Table
     */
    function isReverseJoin() {
        $res = false;
        /*if (!$this->joinsAlias && !$this->isPrimary()) {
            $pa = $this->getSqlSelect()->getEffectivePrimaryAlias();
            $pt = $this->getSqlSelect()->getTable($pa);
            $aa = array_values($pt->getAllRequiredAliases(false));
            if (count($aa) > 1 && $this->getIdentifier() == $aa[0]) {
                $res = $this->getSqlSelect()->getTable($aa[1]);
            }
        }*/
        return $res;
    }
    
    function getEffectiveJoinsAlias($ignorePrimary = true) {
        if ($ignorePrimary && ($t = $this->isReverseJoin())) {
            return $t->getIdentifier();
        } else {
            return $this->joinsAlias;
        }
    }
    
    protected function getEffectiveJoinsOn() {
        if ($t = $this->isReverseJoin()) {
            $res = $t->getJoinsOn(false, true);
        } else {
            $res = $this->joinsOn;
        }
        return $res;
    }
    
    function getEffectiveJoinType() {
    	$res = $this->joinType;
        if (($t = $this->isReverseJoin())) {
            $res = $t->joinType;
        }
        if ($this->isPrimary()) return false;
    	if ($this->joinsAlias && preg_match('/^(inner|natural)\s+join\\b/i', $res)) { // this is INNER or NATURAL JOIN
    		$s = false;
	    	switch ($this->autoLoosenJoinType) {
	    		case AC_LOOSEN_JOIN_NEVER: $loosen = false; break;
	    		case AC_LOOSEN_JOIN_ALWAYS: $loosen = true; break;
	    		case AC_LOOSEN_JOIN_GLOBAL: $s = $this->getSqlSelect(); if ($s && $s->autoLoosenJoins) $loosen = true; else $loosen = false; break;
	    	}
	    	if ($loosen) {
	    		if ($s === false) $s = $this->getSqlSelect();
	    		if ($s) {
                    $joinTable = $s->getTable($this->getEffectiveJoinsAlias());
	    			$otherJoin = $joinTable->getEffectiveJoinType();
	    			if (preg_match('/^(left|right)\s+join$/i', trim($otherJoin), $matches)) {
	    				if (!strncasecmp($matches[1], 'left', 1)) $res = 'LEFT JOIN';
	    				else $res = 'RIGHT JOIN';
	    			}
	    		}
	    	}
    	}
    	return $res;
    }
    
    function _detectUsing($sqs) {
    	$res =
    		is_numeric(implode('', array_keys($this->joinsOn))) 
    			&& 
    		($this->useUsing == AC_USE_USING_ALWAYS || ($this->useUsing == AC_USE_USING_GLOBAL && $sqs->useUsing));
    	if ($res) 
    		foreach ($this->joinsOn as $v)
    			if (is_object($v) && $v instanceof Ac_I_Sql_Expression) {
    				$res = false; break;
    			}
    	return $res;
    }
    
    function getJoinsOn($alias = false, $forceReturn = false) {
        if ($alias === false) $alias = $this->alias;
        // get kind of join
        if ($this->isReverseJoin()) $joinsOn = $this->getEffectiveJoinsOn();
        elseif (is_array($this->joinsOn)) {
        	$sqs = $this->getSqlSelect(true);
        	$db = $sqs->getDb();
    		// it's USING-type join
        	if ($this->_detectUsing($sqs)) $joinsOn = ' USING('.$db->nameQuote($this->joinsOn, true).')';
        	else {
        		$c = array();
        		foreach ($this->joinsOn as $myColumn => $otherColumn) {
        			if (is_object($otherColumn) && $otherColumn instanceof Ac_I_Sql_Expression) {
        				if (!is_numeric($myColumn)) {
        					$expr = $db->nameQuote(array($alias, $myColumn)).' = '.$db->quote($otherColumn); 
        				} else {
        					$expr = $db->quote($otherColumn);
        				}
        				$c[] = $expr;
        			} else {
        				if (is_numeric($myColumn)) $myColumn = $otherColumn;
                		$c[] = $db->nameQuote(array($this->joinsAlias, $otherColumn))
                            .' = '.$db->nameQuote(array($alias, $myColumn));
        			}
            	}
            	$joinsOn = implode(' AND ', $c);
        	}
        } else $joinsOn = (string) $this->joinsOn;
        if ($this->isPrimary() && !$forceReturn) {
            $joinsOn = '';
            $tj = '';
        }
        if (strlen($joinsOn)) $joinsOn = ' '.trim($joinsOn);
        if (strlen($tj = trim($joinsOn)) && strncmp($tj, ',', 1) && strncasecmp($tj, 'on', 2) && strncasecmp($tj, 'using', 2)) $joinsOn = ' ON '.$joinsOn;
        return $joinsOn;
    }

    function hasUsingKeyword() {
    	$res = false;
        $joinsOn = $this->getEffectiveJoinsOn();
    	if (!$this->_empty($joinsOn)) {
    		if (is_array($joinsOn)) $res = is_numeric(implode('', array_keys($joinsOn)));
    		else $res = preg_match('/^USING\\b/i', trim($joinsOn));
    	}
    	return $res;
    }
    
    function _joinNeedsCondition($joinType) {
    	$joinType = trim($joinType);
    	return strlen($joinType) && !(preg_match('/^(((CROSS|NATURAL)\b)|,)/i', $joinType));
    }
    
    function _empty($something) {
    	return is_array($something)? !count($something) : !strlen($something);
    }
    
    protected function getSqlSrc() {
        if ($this->name === false) trigger_error ("\$name must be provided for table '{$this->alias}'", E_USER_ERROR);
        $sqlSelect = $this->getSqlSelect();
        $res = $sqlSelect->n($this->name);
        return $res;
    }
    
    function getJoinClausePart($alias = false, $isFirst = false) {
        $sqlSelect = $this->getSqlSelect();
        if ($this->omitInFromClause) $res = '';
        elseif ($this->isPrimary()) {
            $res = $this->getSqlSrc();
            $alias = $this->alias;
            if (strlen($alias)) $res .= ' AS '.$sqlSelect->n($alias);
        }
        else {
            
            if ($alias === false) $alias = $this->alias;
            
            $sqlSelect = $this->getSqlSelect(true);

            $joinsAlias = $this->getEffectiveJoinsAlias();
            if (strlen($joinsAlias)) {

                $joinType = $this->getEffectiveJoinType();
                
                if (!strlen($joinType) && $isFirst) $joinType = ", ";

                $needsCondition = $this->_joinNeedsCondition($joinType);
                
                $joinsOn = $this->getEffectiveJoinsOn();
                
                if ($needsCondition){
                    if ($this->_empty($joinsOn)) {
                        trigger_error ("\$joinsOn property not provided for '{$joinType}' type join (neither CROSS nor NATURAL)", E_USER_WARNING);
                    }
                } else {
                    if (!$this->_empty($joinsOn)) {
                        trigger_error ("'{$joinType}' type join don't needs \$joinsOn, but it's provided", E_USER_WARNING);
                    }
                }

                if (!strlen($joinType) || (trim($joinType) == ',')) {
                    $joinType = ',';
                }
                $res = $joinType.' '.$this->getSqlSrc();
                if (strlen($alias)) $res .= ' AS '.$sqlSelect->n($alias);

                if ($this->useIndex !== false) {
                    $res .= ' USE INDEX('.(is_array($this->useIndex)? implode(", ", $this->useIndex) : $this->useIndex).')'; 
                }

                $res .= $this->getJoinsOn($alias);

            } else {
                if ($isFirst) $res = "";
                else $res = ", ";
                $res .= $sqlSelect->n($this->name);
                if (strlen($alias)) $res .= ' AS '.$sqlSelect->n($alias);
                if ($this->useIndex !== false) {
                    $res .= ' USE INDEX('.(is_array($this->useIndex)? implode(", ", $this->useIndex) : $this->useIndex).')'; 
                }

            }
        }
        return $res;
    }
    
    function getIdentifier() {
        return strlen($this->alias)? $this->alias : $this->name;
    }
    
    function getDirectRequiredAliases($ignorePrimary = true) {
        $sel = $this->getSqlSelect(true);
        if ($this->isPrimary() && $ignorePrimary) return array();
        $joinsAlias = $this->getEffectiveJoinsAlias($ignorePrimary);
        $ja = strlen($joinsAlias)? array($joinsAlias) : array();
        $res = array_unique(array_merge(array($this->getIdentifier()), $this->otherRequiredAliases, $ja));
        return $res;
    }
    
    function getAllRequiredAliases($ignorePrimary = true) {
        $sqlSelect = $this->getSqlSelect(true);
        if ($this->isPrimary() && $ignorePrimary) {
            return array();
        }
        if ($this->_allRequiredAliases === false) {
        	$sqlSelect = $this->getSqlSelect(true);
            $aliasList = array();
            $deps = array();
            
            $checkAliases = $this->getDirectRequiredAliases($ignorePrimary);
            $checkedAliases = array();
            while (count($checkAliases)) {
                $c = $checkAliases[0];
                $checkedAliases[] = $c;
                $checkAliases = array_slice($checkAliases, 1);
                $t = $sqlSelect->getTable($c);
                $aliasList = array_unique(array_merge($dra = $t->getDirectRequiredAliases($ignorePrimary), $aliasList));
                $deps[$c] = $dra;
                $checkAliases = array_merge($checkAliases, array_diff($dra, $checkedAliases));
            }
            $this->_allRequiredAliases = $aliasList;
            //$this->_allRequiredAliases = array_unique(array_diff($aliasList, array($this->getIdentifier())));
            //var_dump($this->name.' AS '.$this->alias.': '.implode(', ', $this->_allRequiredAliases));
        }
        return $this->_allRequiredAliases;
    }
    
    function __clone() {
    }
    
    function isPrimary() {
        return $this->getSqlSelect(true)->getEffectivePrimaryAlias() == $this->getIdentifier();
    }
    
}

