<?php

define('AC_RC_NUM_OUTGOING', 'numOutgoing');
define('AC_RC_NUM_NO_REF', 'numNoRef');
define('AC_RC_NUM_CORRECT', 'numCorrect');
define('AC_RC_NUM_MISSING', 'numMissing');

define('AC_RC_FIX_DELETE', 'delete');
define('AC_RC_FIX_SET_TO_NON_REF', 'setToNonRef');

class Ac_Sql_RefChecker extends Ac_Prototyped {
	
	/**
	 * @var Ac_Sql_Db
	 */
	var $_db = false;
	
	/**
	 * @var Ac_Sql_Dbi_Database
	 */
	var $_schema = false;
	
	/**
	 * @var array (tableName => array(otherTableName1 => (relName1 => & $rel, relName2 => & $rel), otherTableName2 => ...))
	 */
	var $_relationsToCheck = false;
	
	/**
	 * @var array(tableName => array(colName => val, colName => array(val1, val2))
	 */
	var $validNonRefValues = array();
	
	var $globalValidNonRefValues = array(null);
	
	var $suppressWarnings = false;
	
	var $extraRelations = false;
	
    function hasPublicVars() {
        return true;
    }    
    
	protected function setDb(Ac_Sql_Db $db) {
		$this->_db = $db;
	}
	
	protected function setSchema(Ac_Sql_Dbi_Database $schema) {
		$this->_schema = $schema;
	}
	
	function _getRelationsToCheck() {
		if ($this->_relationsToCheck === false) {
			$this->_relationsToCheck = array();
			foreach ($this->_schema->listTables() as $i) {
				$t = $this->_schema->getTable($i);
				foreach ($t->listRelations() as $j) {
					$rel = $t->getRelation($j);
					$this->_relationsToCheck[$i][$rel->table][] = $rel; 
				} 
			}
		}
		return $this->_relationsToCheck;
	}
	
	function listTablesWithRelations() {
		return array_keys($this->_getRelationsToCheck());
	}
	
	function getRelationsToCheck($forTable = false) {
		$res = $this->_getRelationsToCheck();
		if (strlen($forTable)) {
			if (isset($res[$forTable])) $res = $res[$forTable];
			else $res = array();
		}
		return $res;
	}
	
	function getTablePrototypes($tableName, $alias = false, $otherTableName = false, $otherRelName = false, $type = 'INNER JOIN', $obeyNonRefValues = false, $dbName = false) {
		if (!$alias) $alias = $tableName;
		$tablePrototypes = array(
			$alias => array(
				'name' => strlen($dbName)? new Ae_Sql_Expression($this->_db->n(array($dbName, $tableName))) : $tableName,
			), 
		);
		$rtc = $this->_getRelationsToCheck();
		if (isset($rtc[$tableName])) {
			foreach (array_keys($rtc[$tableName]) as $relTableName) {
				foreach (array_keys($rtc[$tableName][$relTableName]) as $i) {
					$rel = $rtc[$tableName][$relTableName][$i];
					$suffix = count($rtc[$tableName][$relTableName]) > 1? '_'.($i + 1) : '';
					if (($otherTableName !== false) && ($rel->table !== $otherTableName)) {
						continue;	
					}
					if (($otherRelName !== false) && ($relName !== $otherRelName)) continue;
					$joinsOn = array_flip($rel->columns);
					if ($obeyNonRefValues) {
						foreach ($rel->columns as $myColName => $otherColName) {
							$nrv = $this->getValidNonRefValues($tableName, $myColName, false);
							if ($nrv) {
								$joinsOn[] = new Ac_Sql_Expression($this->_db->n(array($alias, $myColName)).' NOT IN ('.$this->_db->q($nrv).')');
							}
						}
					}
					$tablePrototypes['rel_'.$relTableName.$suffix] = array(
						'name' => strlen($dbName)? new Ae_Sql_Expression($this->_db->n(array($dbName, $rel->table))) : $rel->table,
						'joinsAlias' => $alias,
						'joinType' => $type,
						'joinsOn' => $joinsOn,
					);
				}
			}
		} else {
			if (!$this->suppressWarnings) trigger_error("Table \'$tableName\' doesn't have outgoing references", E_USER_WARNING);
		}
		return $tablePrototypes;
        
    }
    
    /**
     * @return Ac_Sql_Select
     */
	function createSelect($tableName, $alias = false, $otherTableName = false, $otherRelName = false, $type = 'INNER JOIN', $obeyNonRefValues = false, $dbName = false) {
        $tablePrototypes = $this->getTablePrototypes($tableName, $alias, $otherTableName, $otherRelName, $type, $obeyNonRefValues, $dbName);
        $ak = array_keys($tablePrototypes);
        $s = new Ac_Sql_Select($this->_db, array(
			'primaryAlias' => $ak[0],
			'tables' => $tablePrototypes,
		));
		$s->setUsedAliases(array_keys($tablePrototypes));
        return $s;
	}

	/**
	 * @param Ac_Sql_Select $selectForRelatedTables
	 * @param $whereKey
	 * @param $concatFunction
	 * @return unknown_type
	 */
	function applyOuterWhere($selectForRelatedTables, $whereKey = 'outerWhere', $forMissingRecords = true, $concatFunction = "\n    AND", $obeyNonRefValues = false) {
		$s = $selectForRelatedTables;
		$where = array();
		foreach ($s->getUsedAliases() as $i) {
			$t = $s->getTable($i);
			if (is_array($t->joinsOn))
				$where[] = $this->_getForeignRecordMissingCriterion($s->getTable($i), $obeyNonRefValues);
		}
		if (count($where)) {
			$s->where[$whereKey] = implode(' '.$concatFunction.' ', $where);
			if (!$forMissingRecords) $s->where[$whereKey] = "NOT ({$s->where[$whereKey]})";
		}
		return $s;
	}
	
	/**
	 * @param Ac_Sql_Select_Table $selTable
	 * @return unknown_type
	 */
	function _getForeignRecordMissingCriterion($selTable, $obeyNonRefValues = false) {
			$t = $selTable;
			$tInfo = $this->_schema->getTable($t->name);
			$pkf = $tInfo->listPkFields();
			foreach ($pkf as $f) {
				$crit[] = 'ISNULL('.$this->_db->n(array($t->getIdentifier(), $f)).')';
			}
			if ($obeyNonRefValues) {
				$myFields = array();
				$sqs = $selTable->getSqlSelect(true);
				$epa = $sqs->getEffectivePrimaryAlias();
				$prim = $sqs->getTable($epa); 
				$crit[] = $this->_getNonRefCriterion($selTable, $prim->name, $epa);
			}
			if (count($crit) > 1) {
				$crit = '('.implode(' AND ', $crit).')';
			} else {
				$crit = $crit[0]; 
			}
			return $crit;
	}

	function getValidNonRefValues($tableName, $colName, $withNulls = true) {
		$res = $this->globalValidNonRefValues;
		if (isset($this->validNonRefValues[$tableName]) && isset($this->validNonRefValues[$tableName][$colName])) {
			$res = array_unique(array_merge($res, $this->validNonRefValues[$tableName][$colName]));
		}
		if (!$withNulls) $res = array_diff($res, array(null));
		return $res;
		
	}
	
	function listStatColumns($select, $tableName = false) {
		$res = array();
		$this->_getRelationsToCheck();
		$primaryAlias = $select->getEffectivePrimaryAlias();
		$primaryTable = $select->getTable($primaryAlias);
		foreach ($select->listTables() as $i) {
			$t = $select->getTable($i);
			if (!is_array($t->joinsOn)) continue;
			if ($tableName !== false && ($t->name !== $tableName)) continue;
			$refsToTable = $this->_relationsToCheck[$primaryTable->name][$t->name];
			$sfx = trim(substr($t->alias, strlen('rel_'.$t->name)), '_');
			if (!is_numeric($sfx)) $sfx = 0; else $sfx = intval($sfx) - 1;
			$refNames = array_keys($refsToTable);
			$refName = $refNames[$sfx];
			$c = array(
				AC_RC_NUM_OUTGOING => $this->_db->nameQuoteBody($t->alias."_".AC_RC_NUM_OUTGOING),
				AC_RC_NUM_NO_REF => $this->_db->nameQuoteBody($t->alias."_".AC_RC_NUM_NO_REF),
				AC_RC_NUM_CORRECT => $this->_db->nameQuoteBody($t->alias."_".AC_RC_NUM_CORRECT),
				AC_RC_NUM_MISSING => $this->_db->nameQuoteBody($t->alias."_".AC_RC_NUM_MISSING),
			);
			$res[$t->name][$refName] = $c;
		}
		if ($tableName && count($res)) {
			$res = array_values($res);
			$res = $res[0];
		}
		return $res;
	}

	function _getNonRefCriterion($t, $tableName, $primaryAlias) {
		foreach($t->joinsOn as $otherColName => $myColName) {
			if (is_object($myColName) && $myColName instanceof Ac_I_Sql_Expression) continue;
			if (is_numeric($otherColName)) $otherColName = $myColName;
			$refSrc = array();
			$validNonRef = $this->getValidNonRefValues($tableName, $myColName);
			$hasNull = false;
			if (in_array(null, $validNonRef)) {
				$hasNull = true;
				$validNonRef = array_diff($validNonRef, array(null));
			}
			$c = $this->_db->n(array($primaryAlias, $myColName));
			if ($validNonRef) {
				$crit = "IF($c IN (".$this->_db->q($validNonRef)."),0,1)";
				if ($hasNull) $crit = "IF(ISNULL($c),0,$crit)";
			} else {
				if ($hasNull) $crit = "IF(ISNULL($c),0,1)";
				else $crit = "1";
			}
			$refSrc[] = $crit;
		}
		$refSrc = count($refSrc) > 1? implode(" AND ", $refSrc) : $refSrc[0];
		return $refSrc;
	}
	
	/**
	 * @param Ac_Sql_Select $select
	 * @param false|true|'flat' $asArray 
	 * @return array
	 */
	function getRelStatColumns($select, $asArray = false) {
		$res = array();
		$primaryTable = $select->getTable($select->getEffectivePrimaryAlias());
		$primaryAlias = $select->getEffectivePrimaryAlias();
		foreach ($select->listTables() as $i) {
			$t = $select->getTable($i);
			if (!is_array($t->joinsOn)) continue;
			$refSrc = $this->_getNonRefCriterion($t, $primaryTable->name, $primaryAlias);
			$refDest = $this->_getForeignRecordMissingCriterion($t);
			$cols = array(
				"SUM($refSrc) AS ".$this->_db->n($t->alias."_".AC_RC_NUM_OUTGOING),
				"SUM(IF($refSrc,0,1)) AS ".$this->_db->n($t->alias."_".AC_RC_NUM_NO_REF),
				"SUM(IF($refDest,0,1)) AS ".$this->_db->n($t->alias."_".AC_RC_NUM_CORRECT),
				"SUM(IF($refDest,1,0))".$this->_db->n($t->alias."_".AC_RC_NUM_MISSING)
			);
			$res[] = $cols;  			
		}
		if (!$asArray || ($asArray === 'flat')) $res = Ac_Util::flattenArray($res);
		if (!$asArray) $res = implode(",\n	", $res);
		return $res; 
	}
	
	function _getRelName(& $select, $selectTable) {
		$primaryAlias = $select->getEffectivePrimaryAlias();
		$primaryTable = $select->getTable($primaryAlias);
		$rtc = $this->_getRelationsToCheck();
		$n = $selectTable->name;
		$refsToTable = $this->_relationsToCheck[$primaryTable->name][$selectTable->name];
		$sfx = trim(substr($selectTable->alias, strlen('rel_'.$selectTable->name)), '_');
		if (!is_numeric($sfx)) $sfx = 0; else $sfx = intval($sfx) - 1;
		$refNames = array_keys($refsToTable);
		$refName = $refNames[$sfx];
		return $refName;
	}
	
	/**
	 * @param Ac_Sql_Select $select
	 * @param $tableName
	 * @param $refKey
	 * @return array
	 */
	function listBadRefValues($tableName, $otherTableName = false, $relName = false) {
		$select = $this->createSelect($tableName, 't', $otherTableName, $relName, 'LEFT JOIN', true);
		$primaryAlias = $select->getEffectivePrimaryAlias();
		$res = array(); 
		foreach ($select->listTables() as $i) {
			$t = $select->getTable($i);
			if (!is_array($t->joinsOn)) continue;
			$cols = array();
			foreach (array_values($t->joinsOn) as $colName) {
				if (is_string($colName)) $cols[] = $this->_db->n(array($primaryAlias, $colName));
			}
			$select->setUsedAliases(array($t->alias));
			$select->where['foreign'] = $this->_getForeignRecordMissingCriterion($t, true);
			$select->columns = $cols;
			$select->distinct = true;
			$vals = $this->_db->fetchArray($select);
			if (count($vals)) {
				$res[$t->name][$rn = $this->_getRelName($select, $t)] = $vals;
				if (($relName === $rn) && ($otherTableName === false)) $otherTableName = $t->name;
			} 
		}
		if (($otherTableName !== false) && count($res)) {
			$res = $res[$otherTableName];
			if ($relName !== false) $res = $res[$relName];
		} 
		return $res;
	}
	
	function fixRecords($tableName, $otherTableName = false, $relName = false, $action = AC_RC_FIX_DELETE, $getSql = false) {
		$sql = array();
		$select = $this->createSelect($tableName, 't', $otherTableName, $relName, 'LEFT JOIN', true);
		$primaryAlias = $select->getEffectivePrimaryAlias();
		foreach ($select->listTables() as $i) {
			$t = $select->getTable($i);
			if (!is_array($t->joinsOn)) continue;
			$cols = array();
			$colsToFix = array();
			foreach (array_values($t->joinsOn) as $colName) {
				if (is_string($colName)) {
					$cols[] = $this->_db->n(array($primaryAlias, $colName));	
					if ($action === AC_RC_FIX_SET_TO_NON_REF) {
						$colValues = $this->getValidNonRefValues($t->name, $colName, true);
						$val = count($colValues)? $colValues[0] : null;
						if (!count($colValues)) {
							if (!$this->suppressWarnings) trigger_error("No valid non-ref value found for column '$colName'; assuming NULL", E_USER_WARNING); 
						}
						$colsToFix[] = $this->_db->n(array($primaryAlias, $colName)).' = '.$this->_db->q($val);
					}
				}
			}
			$select->setUsedAliases(array($t->alias));
			$select->where['foreign'] = $this->_getForeignRecordMissingCriterion($t, true);
			if ($action === AC_RC_FIX_SET_TO_NON_REF) {
				$sql[] = "UPDATE ".$select->getFromClause(false)." SET ".implode(", ", $colsToFix)." WHERE ".$select->getWhereClause(false);
			} elseif ($action === AC_RC_FIX_DELETE) {
				$sql[] = "DELETE ".($this->_db->n($select->getEffectivePrimaryAlias())).".* FROM ".$select->getFromClause(false)." WHERE ".$select->getWhereClause(false);
			} else {
				trigger_error("Unknown \$action value: '".$action."', valid are AC_RC_FIX_SET_TO_NON_REF, AC_RC_FIX_DELETE", E_USER_ERROR);
			}
		}
		if ($getSql) $res = implode(";\n", $sql);
		else {
			$res = true;
			foreach ($sql as $s) if (!$this->_db->query($s)) $res = false;
		}
		return $res;
	}
	
}
