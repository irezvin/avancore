<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

ini_set('xdebug.var_display_max_data', 102400);
ini_set('xdebug.var_display_max_depth', 7);


class Ac_Test_Dbi extends Ac_Test_Base {

	// --------------------------------+ supplementary functions +-----------------------------------
	
	/**
	 * @param Ac_Sql_Dbi_Database $dbiDatabase
	 * @return unknown_type
	 */
	function collectDbInfo($dbiDatabase) {
		$res = array();
		foreach ($dbiDatabase->listTables() as $tableName) {
			$table = $dbiDatabase->getTable($tableName);
			$columns = array();
			$indexes = array();
			$relations = array();
			foreach ($table->listColumns() as $colName) {
				$col = $table->getColumn($colName);
				$columns[$colName] = $col->getKnownProperties();
			}
			foreach ($table->listRelations() as $relName) {
				$rel = $table->getRelation($relName);
				$relations[$relName] = $rel->getKnownProperties();
			}
			foreach ($table->listIndices() as $idxName) {
				$idx = $table->getIndex($idxName);
				$indexes[$idxName] = $idx->getKnownProperties();
			}
			$res['tables'][$tableName]['columns'] = $columns;
			if ($relations) $res['tables'][$tableName]['relations'] = $relations;
			if ($indexes) $res['tables'][$tableName]['indexes'] = $indexes;
		}
		//var_dump ($this->exportArray($res, 0, false, false, true));	
        //var_dump($res);
		return $res;
	}
	
	function getSampleDbInfo() {
		include(dirname(__FILE__).'/assets/properDbi.php');
		return $properDbi;
	}
	
	/**
	 * @return Ac_Sql_Dbi_Database
	 */
	function createDbiDatabaseWithMySql5Inspector() {
		$sqlDb = $this->getAeDb();
		$inspector = new Ac_Sql_Dbi_Inspector_MySql5($sqlDb, $this->getDbName());
		$dbiDb = new Ac_Sql_Dbi_Database($inspector, $this->getDbName(), $this->getTablePrefix());
		return $dbiDb;
	}
	
	/**
	 * @return Ac_Sql_Dbi_Database
	 */
	function createDbiDatabaseWithDefaultInspector() {
		$sqlDb = $this->getAeDb();
		$inspector = new Ac_Sql_Dbi_Inspector($sqlDb, $this->getDbName());
		$dbiDb = new Ac_Sql_Dbi_Database($inspector, $this->getDbName(), $this->getTablePrefix());
		return $dbiDb;
	}
	
	/**
	 * @return Ac_Sql_Dbi_Database
	 */
	function createDbiDatabaseWithLegacyInspector() {
		
		$lcl = new Ac_Util_LegacyAvancoreLoader(array('avancorePath' => dirname(__FILE__).'/../../../legacy'));
		$lcl->load();
		$inspector = new Ac_Sql_Dbi_Inspector_Legacy('ac_'); 
		$dbiDb = new Ac_Sql_Dbi_Database($inspector, $this->getDbName(), $this->getTablePrefix());
		return $dbiDb;
	}
	
	
	// --------------------------------+ tests +-----------------------------------
	
	function testDbiWithDefaultInspector() {
		$dbi = $this->createDbiDatabaseWithDefaultInspector();
		$tested = $this->collectDbInfo($dbi);
		$standard = $this->getSampleDbInfo();
        foreach (array_keys($standard['tables']) as $t) unset($standard['tables'][$t]['relations']);
		$this->assertTrue(isset($tested['tables']));
        $this->assertArraysMatch($standard, $tested, 'Default DBI info does not match: %s');
	}
	
	function testDbiWithMySql5Inspector() {
		$dbi = $this->createDbiDatabaseWithMySql5Inspector();
		$tested = $this->collectDbInfo($dbi);
		$standard = $this->getSampleDbInfo();
		$this->assertTrue(isset($tested['tables']));
        if (!$this->assertArraysMatch($standard, $tested, 'Default DBI info does not match: %s')) {
            var_dump($tested);
            var_dump($proper);
        }
	}
	
    function testAvancoreLegacy() {
        // TODO: add Legacy Avancore Loader?
        return;
        
		$dbi = $this->createDbiDatabaseWithLegacyInspector();
		$tested = $this->collectDbInfo($dbi);
		$standard = $this->getSampleDbInfo();
		
		foreach (array_keys($standard['tables']) as $tableName) {
			// legacy proder doesn't return relation names; we know about that and we have to strip them from $standard to pass the test
			if (isset($standard['tables'][$tableName]['relations'])) {
				$standard['tables'][$tableName]['relations'] = array_values($standard['tables'][$tableName]['relations']);
				foreach ($standard['tables'][$tableName]['relations'] as $i => & $j) $j['name'] = $i;
			}
			
			// it also don't provide non-primary non-unique indexes 
			if (isset($standard['tables'][$tableName]['indexes'])) {
				foreach ($standard['tables'][$tableName]['indexes'] as $idxName => & $idx) {
					if (!$idx['primary'] && !$idx['unique']) unset($standard['tables'][$tableName]['indexes'][$idxName]);
				}
			}
		}
		
		$this->assertTrue(isset($standard['tables']));
		$this->assertEqual(count($standard['tables']), count($tested['tables']), 'Found table counts don\'t match: %s');
		$this->assertArraysMatch($tested, $standard, 'Default DBI info does not match: %s', '~^/tables(/[^/]+)?$~');
	}
	
}

