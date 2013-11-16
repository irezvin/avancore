<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

ini_set('xdebug.var_display_max_data', 102400);
ini_set('xdebug.var_display_max_depth', 7);

class Ac_Test_Mssql extends Ac_Test_Base {

	// --------------------------------+ supplementary functions +-----------------------------------
	
	/**
	 * @var Ac_Ms_Database
	 */
	var $_db = false;
	
	/**
	 * @return Ac_Ms_Database
	 */
	function getMssqlDb() {
		if ($this->_db === false) {
			$this->_db = new Ac_Ms_Database(array(
				'user' => 'irezvin',
				'password' => 'pvdgKV8',
				'host' => 'work',
				'port' =>  1561,
				'db' => 'worktime'
			));
		}
		return $this->_db;
	}
	
	function _testBasic() {
        // TODO: fixme
		$db = $this->getMssqlDb();
		$db->setQuery('SELECT * FROM table_sts ORDER BY code_id');
		var_dump($db->loadAssocList());
		$offset = 2;
		$limit = 2;
		$db->setQuery("SELECT * FROM (SELECT row_number() OVER (ORDER BY code_id) AS num, * FROM table_sts) as WithRowNum WHERE num BETWEEN $offset + 1  AND ($offset + $limit)");
		var_dump($db->loadAssocList());
	}
	
}

