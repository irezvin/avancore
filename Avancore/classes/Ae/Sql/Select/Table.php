<?php

/**
 * When $Ae_Sql_Select_Table->autoLoosenJoinType == AE_LOOSEN_JOIN_GLOBAL, loosening will be performing when $sqlSelect->autoLoosenJoins is TRUE. 
 */
define('AE_LOOSEN_JOIN_GLOBAL', -1);

/**
 * When $Ae_Sql_Select_Table->autoLoosenJoinType == AE_LOOSEN_JOIN_NEVER, no loosening will be performed. 
 */
define('AE_LOOSEN_JOIN_NEVER', 0);

/**
 * When $Ae_Sql_Select_Table->autoLoosenJoinType == AE_LOOSEN_JOIN_AS_PARENT, loosening will be applied (if necessary) disregarding to $sqlSelect->autoLosenJoins value. 
 */
define('AE_LOOSEN_JOIN_ALWAYS', 1);

/**
 * Ae_Sql_Select_Table should check $useUsing of sql statement
 */
define('AE_USE_USING_GLOBAL', -1);
define('AE_USE_USING_NEVER', 0);
define('AE_USE_USING_ALWAYS', 1);

/**
 * Represents table reference with JOIN in SQL SELECT statement family
 */
class Ae_Sql_Select_Table {
    
    /**
     * SQL statement family to which current table reference belongs
     * @var Ae_Sql_Select
     */
    var $_sqlSelect = false;
    
    /**
     * Name of source table or Ae_Sql_Expression with subselect (and proper parenthesis).
     * @var string|Ae_Sql_Expression
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
    var $autoLoosenJoinType = AE_LOOSEN_JOIN_GLOBAL;
    
    var $useUsing = AE_USE_USING_GLOBAL;
    
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
    
    var $_deps = false;
    
    /**
     * @var Ae_Sql_Select_TableProvider
     */
    var $_tableProvider = false;
    
    /**
     * @param array $options
     * @param Ae_Sql_Select_TableProvider $tableProvider
     * @return Ae_Sql_Select_Table
     */
    function Ae_Sql_Select_Table(& $tableProvider, $options = array()) {
        if ($tableProvider) $options['tableProvider'] = & $tableProvider;
        Ae_Util::bindAutoparams($this, $options, true);
    }
    
    /**
     * @param Ae_Sql_Select $sqlSelect
     */
    function getSqlSelect($required = false) {
    	$res = & $this->_tableProvider;
    	while ($res && !is_a($res, 'Ae_Sql_Select')) {
    		$res = & $res->getParent();
    	}
    	if ($required && !$res) trigger_error("Cannot retrieve an instance of Ae_Sql_Select (it isn't in any of table' parents)", E_USER_ERROR);
    	return $res;
    }
    
    function getDb($required = false) {
    	$s = & $this->getSqlSelect($required);
    	$res = null;
    	if ($s) $res = & $s->getDb();
    	if ($required && !$res) trigger_error('Cannot retreive an instance of Ae_Sql_Db', E_USER_ERROR);
    	return $res;
    }
    
    /**
     * @param Ae_Sql_Select $sqlSelect
     */
    function setSqlSelect(& $sqlSelect) {
        if (!is_a($sqlSelect, 'Ae_Sql_Select'))  trigger_error("\$sqlSelect must be an instance of Ae_Sql_Select", E_USER_ERROR);
        $this->_tableProvider = & $sqlSelect;
    }
    
    function setTableProvider(& $tableProvider) {
    	if (!is_a($tableProvider, 'Ae_Sql_Select_TableProvider')) triggerError("\$tableProvider must be an instance of Ae_Sql_Select_TableProvider", E_USER_ERROR);
    	$this->_tableProvider = & $tableProvider;
    }
    
    /**
     * @return Ae_Sql_Select_TableProvider
     */
    function getTableProvider() {
    	return $this->_tableProvider;
    }
    
    function getEffectiveJoinType() {
    	$res = $this->joinType;
    	if ($this->joinsAlias && preg_match('/^(inner|natural)\s+join\\b/i', $res)) { // this is INNER or NATURAL JOIN
    		$s = false;
	    	switch ($this->autoLoosenJoinType) {
	    		case AE_LOOSEN_JOIN_NEVER: $loosen = false; break;
	    		case AE_LOOSEN_JOIN_ALWAYS: $loosen = true; break;
	    		case AE_LOOSEN_JOIN_GLOBAL: $s = & $this->getSqlSelect(); if ($s && $s->autoLoosenJoins) $loosen = true; else $loosen = false; break;
	    	}
	    	if ($loosen) {
	    		if ($s === false) $s = & $this->getSqlSelect();
	    		if ($s) {
	    			$joinTable = & $s->getTable($this->joinsAlias);
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
    
    function _detectUsing(& $sqs) {
    	$res =
    		is_numeric(implode('', array_keys($this->joinsOn))) 
    			&& 
    		($this->useUsing == AE_USE_USING_ALWAYS || ($this->useUsing == AE_USE_USING_GLOBAL && $sqs->useUsing));
    	if ($res) 
    		foreach ($this->joinsOn as $v)
    			if (is_a($v, 'Ae_Sql_Expression')) {
    				$res = false; break;
    			}
    	return $res;
    }
    
    function getJoinsOn() {
        // get kind of join
        if (is_array($this->joinsOn)) {
        	$sqs = & $this->getSqlSelect(true);
        	$db = & $sqs->getDb();
    		// it's USING-type join
        	if ($this->_detectUsing($sqs)) $joinsOn = ' USING('.$db->nameQuote($this->joinsOn, true).')';
        	else {
        		$c = array();
        		foreach ($this->joinsOn as $myColumn => $otherColumn) {
        			if (is_a($otherColumn, 'Ae_Sql_Expression')) {
        				if (!is_numeric($myColumn)) {
        					$expr = $db->nameQuote(array($this->alias, $myColumn)).' = '.$db->quote($otherColumn); 
        				} else {
        					$expr = $db->quote($otherColumn);
        				}
        				$c[] = $expr;
        			} else {
        				if (is_numeric($myColumn)) $myColumn = $otherColumn;
                		$c[] = $db->nameQuote(array($this->joinsAlias, $otherColumn)).' = '.$db->nameQuote(array($this->alias, $myColumn));
        			}
            	}
            	$joinsOn = implode(' AND ', $c);
        	}
        } else $joinsOn = (string) $this->joinsOn;
        if (strlen($joinsOn)) $joinsOn = ' '.trim($joinsOn);
        if (strlen($tj = trim($joinsOn)) && strncmp($tj, ',', 1) && strncasecmp($tj, 'on', 2) && strncasecmp($tj, 'using', 2)) $joinsOn = ' ON '.$joinsOn;
        return $joinsOn;
    }

    function hasUsingKeyword() {
    	$res = false;
    	if (!$this->_empty($this->joinsOn)) {
    		if (is_array($this->joinsOn)) $res = is_numeric(implode('', array_keys($this->joinsOn)));
    		else $res = preg_match('/^USING\\b/i', trim($this->joinsOn));
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
    
    function getJoinClausePart() {
        $sqlSelect = & $this->getSqlSelect(true);
    	//if (!$this->_sqlSelect) trigger_error("\'sqlSelect' property not set - call setSqlSelect() first", E_USER_ERROR);
        if (!strlen($this->name)) trigger_error ("\$name must be provided for table '{$this->alias}'", E_USER_ERROR);
        //if (!strlen($this->alias)) trigger_error ("\$alias must be provided", E_USER_ERROR);
        
        if (strlen($this->joinsAlias)) {
        	
        	$joinType = $this->getEffectiveJoinType();
            
            $needsCondition = $this->_joinNeedsCondition($joinType);
            if ($needsCondition){
            	if ($this->_empty($this->joinsOn)) {
            		trigger_error ("\$joinsOn property not provided for '{$joinType}' type join (neither CROSS nor NATURAL)", E_USER_WARNING);
            	}
            } else {
            	if (!$this->_empty($this->joinsOn)) {
            		trigger_error ("'{$joinType}' type join don't needs \$joinsOn, but it's provided", E_USER_WARNING);
            	}
            }
            
            if (!strlen($joinType) || (trim($joinType) == ',')) {
            	$joinType = ',';
            }
            $res = $joinType.' '.$sqlSelect->nameQuote($this->name);
            if (strlen($this->alias)) $res .= ' AS '.$sqlSelect->nameQuote($this->alias);
            
            if ($this->useIndex !== false) {
                $res .= ' USE INDEX('.(is_array($this->useIndex)? implode(", ", $this->useIndex) : $this->useIndex).')'; 
            }
            
            $res .= $this->getJoinsOn();
        
        } else {
        	
            $res = $sqlSelect->nameQuote($this->name);
            if (strlen($this->alias)) $res .= ' AS '.$sqlSelect->nameQuote($this->alias);
            if ($this->useIndex !== false) {
                $res .= ' USE INDEX('.(is_array($this->useIndex)? implode(", ", $this->useIndex) : $this->useIndex).')'; 
            }
            
        }
        return $res;
    }
    
    function getIdentifier() {
        return strlen($this->alias)? $this->alias : $this->name;
    }
    
    function getDirectRequiredAliases() {
        $ja = $this->joinsAlias? array($this->joinsAlias) : array();
        $res = array_unique(array_merge(array($this->getIdentifier()), $this->otherRequiredAliases, $ja));
        return $res;
    }
    
    function getAllRequiredAliases() {
        if ($this->_allRequiredAliases === false) {
        	$sqlSelect = & $this->getSqlSelect(true);
            
            $aliasList = array();
            $deps = array();
            
            $checkAliases = $this->getDirectRequiredAliases();
            $checkedAliases = array();
            while (count($checkAliases)) {
                $c = $checkAliases[0];
                $checkedAliases[] = $c;
                $checkAliases = array_slice($checkAliases, 1);
                $t = & $sqlSelect->getTable($c);
                $aliasList = array_unique(array_merge($dra = $t->getDirectRequiredAliases(), $aliasList));
                $deps[$c] = $dra;
                $checkAliases = array_merge($checkAliases, array_diff($dra, $checkedAliases));
            }
            $this->_allRequiredAliases = $aliasList;
            //$this->_allRequiredAliases = array_unique(array_diff($aliasList, array($this->getIdentifier())));
            //var_dump($this->name.' AS '.$this->alias.': '.implode(', ', $this->_allRequiredAliases));
        }
        return $this->_allRequiredAliases;
    }
    
}

?>