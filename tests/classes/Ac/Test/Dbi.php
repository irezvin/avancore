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
		$inspector = $sqlDb->getInspector();
		$dbiDb = new Ac_Sql_Dbi_Database([
            'inspector' => $inspector, 
            'name' => $this->getDbName(), 
            'tablePrefix' => $this->getTablePrefix()
        ]);
		return $dbiDb;
	}
	
	// --------------------------------+ tests +-----------------------------------
	
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
	
}

