<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

ini_set('xdebug.var_display_max_data', 102400);
ini_set('xdebug.var_display_max_depth', 7);

class Ac_Test_ModelSql extends Ac_Test_Base {

    protected $bootSampleApp = true;
    
	/**
	 * @return Ac_Sql_Select
	 */
	function createSelect() {
		$m = Sample::getInstance()->getSamplePersonMapper();
		$sel = new Ac_Sql_Select($this->getAeDb(), array(
			'tables' => array(
				't' => array(
					'name' => $m->tableName,
				),
			),
			'tableProviders' => array(
				'modelSql' => array(
					'class' => 'Ac_Model_Sql_TableProvider',
					'mapperClass' => Ac_Util::fixClassName(get_class($m)),
				),
			),
		));
		return $sel;
	}
	
	function testAutoTables() {
		$sel = $this->createSelect();
		$sel->useAlias(array('outgoingRelations[otherPerson]', 'outgoingRelations[relationType]'));
		$sel->columns = array('t.name', '`outgoingRelations[otherPerson]`.`name` AS `otherName`', '`outgoingRelations[relationType]`.`title`');
		$sqlDb = $this->getAeDb();
		
		$rightStatement = "
			SELECT t.name, `outgoingRelations[otherPerson]`.`name` AS `otherName`, `outgoingRelations[relationType]`.`title`
				FROM `ac_people` AS `t`
    			LEFT JOIN `ac_relations` AS `outgoingRelations` ON  `t`.`personId` = `outgoingRelations`.`personId`
    			LEFT JOIN `ac_people` AS `outgoingRelations[otherPerson]` ON  `outgoingRelations`.`otherPersonId` = `outgoingRelations[otherPerson]`.`personId`
    			LEFT JOIN `ac_relation_types` AS `outgoingRelations[relationType]` ON  `outgoingRelations`.`relationTypeId` = `outgoingRelations[relationType]`.`relationTypeId`
    	";
		if (!$this->assertEqual($foo = $this->normalizeStatement($sel->getStatement()), $bar = $this->normalizeStatement($rightStatement))) {
			var_dump($sel->getStatement(), $rightStatement, $foo, $bar);
		}
		$this->assertTrue(count($sqlDb->fetchArray($sel)));
		
		$sel2 = Ac_Model_Sql_TableProvider::createSelect('Sample_Person_Mapper', $sqlDb);
		$sel2->columns = array(
			't.name', 
			'otherName' => new Ac_Sql_Select_Expression('outgoingRelations[otherPerson].name', true),
			new Ac_Sql_Select_Expression('outgoingRelations[relationType].title', true),
		);
		if (!$this->assertEqual($this->normalizeStatement($sel2->getStatement()), $this->normalizeStatement($rightStatement))) {
			var_dump($sel2->getStatement(), $rightStatement);
		}
	}
    
    function testSaveError() {
        $person = Sample::getInstance()->getSamplePersonMapper()->createRecord();
        $person->setTagIds(array(-1, -2, -3));
        $db = Sample::getInstance()->getDb();
        if ($db instanceof Ac_Sql_Db_Pdo) {
            $pdo = $db->getPdo();
            $tmp = $pdo->getAttribute(PDO::ATTR_ERRMODE);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $this->assertTrue(!$person->store());
            $pdo->setAttribute(PDO::ATTR_ERRMODE, $tmp);
        }
        if (!$this->assertTrue(is_array(Ac_Util::getArrayByPath($person->getErrors(), array('_store'))))) {
            var_dump($person->getErrors());
        }
    }
    
    function testSaveRecordNotChanged() {
        $people = Sample::getInstance()->getSamplePersonMapper()->getAllRecords();
        $person = array_pop($people);
        $storeResult = $person->store();
        $this->assertTrue($storeResult);
        $this->assertTrue(!$person->getErrors());
    }
		
}
