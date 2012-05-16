<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

ini_set('xdebug.var_display_max_data', 102400);
ini_set('xdebug.var_display_max_depth', 7);

/**
 * @author Nivzer
 */

/**
 * We have to test RefChecker for following functionality:
 * 
 * - criterion for records with all foreign records
 * - criterion for records with no foreign records
 * - correct stats
 * 
 * in these cases:
 * 
 * - missing references
 * - composite foreign keys
 * - special values indicating 'no foreign object'
 * - mixing NULLs and special values
 */
class Ac_Test_RefChecker extends Ac_Test_Base {
	
	/**
	 * @var Ac_Sql_Db
	 */
	var $_sqlDb = false;
	
	function getDbName() {
		return Ac_Dispatcher::getInstance()->config->getValue('db');
	}
	
	function getTablePrefix() {
		return Ac_Dispatcher::getInstance()->config->getValue('prefix');
	}
	
	/**
	 * @return Ac_Sql_Dbi_Database
	 */
	function & getDbi() {
		$sqlDb = & $this->getSqlDb();
		$inspector = new Ac_Sql_Dbi_Inspector_MySql5($sqlDb, $this->getDbName());
		$dbiDb = new Ac_Sql_Dbi_Database($inspector, $this->getDbName(), $this->getTablePrefix());
		return $dbiDb;
	}

	/**
	 * @return Ac_Sql_Db
	 */
	function & getSqlDb() {
		if ($this->_sqlDb === false) $this->_sqlDb = new Ac_Sql_Db_Ae(Ac_Dispatcher::getInstance()->database);
		return $this->_sqlDb;
	}

	/**
	 * @return Ac_Sql_RefChecker
	 */
	function & createRefChecker() {
		$dbi = & $this->getDbi();
		$sdb = & $this->getSqlDb();
		$rc = new Ac_Sql_RefChecker(array(
			'db' => & $sdb,
			'schema' => & $dbi
		));
		return $rc;
	}
	
	function testRefChecker() {
		$rc = & $this->createRefChecker();
		$this->assertEqual(array_diff($rc->listTablesWithRelations(), array('#__people', '#__people_tags', '#__relations')), array());
		$select = & $rc->createSelect('#__relations');
		
		$rightStatement = "
			`#__relations` AS `#__relations`
    			INNER JOIN `#__people` AS `rel_#__people_1` ON `#__relations`.`personId` = `rel_#__people_1`.`personId`
    			INNER JOIN `#__people` AS `rel_#__people_2` ON `#__relations`.`otherPersonId` = `rel_#__people_2`.`personId`
    			INNER JOIN `#__relation_types` AS `rel_#__relation_types` ON  `#__relations`.`relationTypeId` = `rel_#__relation_types`.`relationTypeId`
     	";
		$this->assertEqual($this->normalizeStatement($select->getFromClause()), $this->normalizeStatement($rightStatement, true));
		
		$select2 = & $rc->createSelect('#__relations', 't', '#__people', false, 'LEFT JOIN');
		$rightStatement2 = "
			`#__relations` AS `t`
    			LEFT JOIN `#__people` AS `rel_#__people_1` ON `t`.`personId` = `rel_#__people_1`.`personId`
    			LEFT JOIN `#__people` AS `rel_#__people_2` ON `t`.`otherPersonId` = `rel_#__people_2`.`personId`
		";
		$this->assertEqual($this->normalizeStatement($select2->getFromClause()), $this->normalizeStatement($rightStatement2, true));
		
		$select3 = & $rc->createSelect('#__relations', 't', false, false, 'LEFT JOIN');
		$sdb = & $this->getSqlDb();
		$select3->columns = $rc->getRelStatColumns($select3);
		$statement3 = "\n".$select3->getStatement()."\n";
		$stats3 = $sdb->fetchRow($statement3);
		//var_dump($sdb->fetchRow($statement3));
		$vals = $sdb->fetchColumn('
				SELECT DISTINCT(personId) FROM #__relations 
			UNION
				SELECT DISTINCT(otherPersonId) FROM #__relations
			UNION
				SELECT DISTINCT(relationTypeId) FROM #__relations
		');
		$vals[] = null;
		$rc->globalValidNonRefValues = array_unique($vals);
		$select4 = & $rc->createSelect('#__relations', 't', false, false, 'LEFT JOIN', true);
		$select4->columns = $rc->getRelStatColumns($select4);
		$statement4 = "\n".$select4->getStatement()."\n";
		$stats4 = $sdb->fetchRow($statement4);
		
		$pairs = array('Outgoing' => 'NoRef', 'Correct' => 'Missing');
		$tables = array('rel_ac_people_1_num', 'rel_ac_people_2_num', 'rel_ac_relation_types_num');
		$allCorrect = true;
		foreach ($tables as $t) {
			foreach($pairs as $p1 => $p2) {
				if (!$this->assertEqual($stats3[$t.$p1], $stats4[$t.$p2], "\$stats3[$t$p1] should be equal to \$stats4[$t$p2]")) $allCorrect = false;
				if (!$this->assertEqual($stats3[$t.$p2], $stats4[$t.$p1], "\$stats3[$t$p2] should be equal to \$stats4[$t$p1]")) $allCorrect = false;
			}
		}
		if (!$allCorrect) {
			$this->_reporter->paintFormattedMessage("These two statements should produce opposite results:");
			$this->_reporter->paintFormattedMessage("-- 1)\n".$statement3);
			$hr = ini_get('html_errors'); ini_set('html_errors', 0);
			//ob_start(); var_dump($stats3); $dump3 = ob_get_clean(); $this->_reporter->paintFormattedMessage($dump3);
			$this->_reporter->paintFormattedMessage("-- 2)\n".$statement4);
			//ob_start(); var_dump($stats4); $dump4 = ob_get_clean();
			ini_set('html_errors', 1);
		}

		$peopleName = $sdb->nameQuoteBody('#__people');
		$relationTypesName = $sdb->nameQuoteBody('#__relation_types');
		
		$this->assertEqual($a = $rc->listStatColumns($select3), $b = array(
			($k = '#__people') => array(
				(($m = 1) - 1) => array(
					($l = AC_RC_NUM_OUTGOING) => "rel_{$peopleName}_{$m}_{$l}",
					($l = AC_RC_NUM_NO_REF) => "rel_{$peopleName}_{$m}_{$l}",
					($l = AC_RC_NUM_CORRECT) => "rel_{$peopleName}_{$m}_{$l}",
					($l = AC_RC_NUM_MISSING) => "rel_{$peopleName}_{$m}_{$l}",
				),
				(($m = 2) - 1) => array(
					($l = AC_RC_NUM_OUTGOING) => "rel_{$peopleName}_{$m}_{$l}",
					($l = AC_RC_NUM_NO_REF) => "rel_{$peopleName}_{$m}_{$l}",
					($l = AC_RC_NUM_CORRECT) => "rel_{$peopleName}_{$m}_{$l}",
					($l = AC_RC_NUM_MISSING) => "rel_{$peopleName}_{$m}_{$l}",
				),
			),
			($k = '#__relation_types') => array(
				0 => array(
					($l = AC_RC_NUM_OUTGOING) => "rel_{$relationTypesName}_{$l}",
					($l = AC_RC_NUM_NO_REF) => "rel_{$relationTypesName}_{$l}",
					($l = AC_RC_NUM_CORRECT) => "rel_{$relationTypesName}_{$l}",
					($l = AC_RC_NUM_MISSING) => "rel_{$relationTypesName}_{$l}",
				),
			),
		), 'Correct naming of stat columns fails (listStatColumns() method): %s');
		
		$this->assertEqual($a = $rc->listStatColumns($select3, ($k = '#__people')), $b = array(
			(($m = 1) - 1) => array(
				($l = AC_RC_NUM_OUTGOING) => "rel_{$peopleName}_{$m}_{$l}",
				($l = AC_RC_NUM_NO_REF) => "rel_{$peopleName}_{$m}_{$l}",
				($l = AC_RC_NUM_CORRECT) => "rel_{$peopleName}_{$m}_{$l}",
				($l = AC_RC_NUM_MISSING) => "rel_{$peopleName}_{$m}_{$l}",
			),
			(($m = 2) - 1) => array(
				($l = AC_RC_NUM_OUTGOING) => "rel_{$peopleName}_{$m}_{$l}",
				($l = AC_RC_NUM_NO_REF) => "rel_{$peopleName}_{$m}_{$l}",
				($l = AC_RC_NUM_CORRECT) => "rel_{$peopleName}_{$m}_{$l}",
				($l = AC_RC_NUM_MISSING) => "rel_{$peopleName}_{$m}_{$l}",
			),
		), 'Correct naming of stat columns for one table fails (listStatColumns() method): %s');		
	}
	
}