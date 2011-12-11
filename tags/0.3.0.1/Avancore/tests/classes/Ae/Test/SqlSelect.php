<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ae_Test_SqlSelect extends Ae_Test_Base {
	
	/**
	 * @return Ae_Sql_Select
	 */
	function & createMySelect() {
		$res = new Ae_Sql_Select($this->getAeDb(), array(
			'tables' => array(
				'people' => array(
					'name' => '#__people',
				),
				'relations' => array(
					'name' => '#__relations',
					'joinsAlias' => 'people',
					'joinsOn' => array('personId'),
				),
				'relationTypes' => array(
					'name' => '#__relation_types',
					'joinsAlias' => 'relations',
					'joinsOn' => 'USING(`relationTypeId`)'
				),
				'otherPeople' => array(
					'name' => '#__people',
					'joinsAlias' => 'relations',
					'joinsOn' => array('personId' => 'otherPersonId'),
					'joinType' => 'INNER JOIN',
				),
				'peopleTagsLink' => array(
					'name' => '#__people_tags',
					'joinsAlias' => 'people',
					'joinsOn' => array('personId'),
					'joinType' => 'LEFT JOIN',
				),
			),
			'tableProviders' => array(
				'foo' => array(
					'tables' => array(
						'peopleTags' => array(
							'name' => '#__tags',
							'joinsAlias' => 'peopleTagsLink',
							'joinsOn' => array('tagId'),
							'joinType' => 'LEFT JOIN',
						),
						'otherPeopleTagsLink' => array(
							'name' => '#__people_tags',
							'joinsAlias' => 'otherPeople',
							'joinsOn' => array('personId'),
							'joinType' => 'LEFT JOIN',
						),
						'otherPeopleTags' => array(
							'name' => '#__tags',
							'joinsAlias' => 'otherPeopleTagsLink',
							'joinsOn' => array('tagId'),
							'joinType' => 'LEFT JOIN',
						),
					),
				),
			),
			
		));
		return $res;
	}

	function testBasics() {
		
		$db = & $this->getAeDb();
		
		$select = & $this->createMySelect();
		$select->setUsedAliases('otherPeople');
		$this->assertEqual($this->normalizeStatement($from = $select->getFromClause()), $this->normalizeStatement($rightStatement1 = "
			`#__people` AS `people` 
				INNER JOIN `#__relations` AS `relations`  ON `people`.`personId` = `relations`.`personId` 
				INNER JOIN `#__people` AS `otherPeople` ON `relations`.`otherPersonId` = `otherPeople`.`personId`
		", true));
		
		$this->assertTrue(is_array($db->fetchArray("SELECT * FROM ".$from)), "valid query");
		
		$select->setUsedAliases(array('peopleTags', 'otherPeopleTags', 'relationTypes'));
		$rightStatement2 = "
				`#__people` AS `people`
    			LEFT JOIN `#__people_tags` AS `peopleTagsLink` ON  `people`.`personId` = `peopleTagsLink`.`personId`
    			LEFT JOIN `#__tags` AS `peopleTags` ON  `peopleTagsLink`.`tagId` = `peopleTags`.`tagId`
    			INNER JOIN `#__relations` AS `relations` ON  `people`.`personId` = `relations`.`personId`
    			INNER JOIN `#__people` AS `otherPeople` ON  `relations`.`otherPersonId` = `otherPeople`.`personId`
    			LEFT JOIN `#__people_tags` AS `otherPeopleTagsLink` ON  `otherPeople`.`personId` = `otherPeopleTagsLink`.`personId`
    			LEFT JOIN `#__tags` AS `otherPeopleTags` ON  `otherPeopleTagsLink`.`tagId` = `otherPeopleTags`.`tagId`
    			INNER JOIN `#__relation_types` AS `relationTypes` USING(`relationTypeId`)
    	";
		$this->assertEqual($this->normalizeStatement($from = $select->getFromClause()), $this->normalizeStatement($rightStatement2, true));
		$this->assertTrue(is_array($db->fetchArray("SELECT * FROM ".$from)), "valid query");
		
		$t = & $select->getTable('otherPeople');
		$t->joinsOn = "ON `relations`.`otherPersonId` = `otherPeople`.`personId`";
		$this->assertEqual($this->normalizeStatement($select->getFromClause()), $this->normalizeStatement($rightStatement2, true));
		
		$t = & $select->getTable('otherPeople');
		$t->joinsOn = "`relations`.`otherPersonId` = `otherPeople`.`personId`";
		$this->assertEqual($this->normalizeStatement($select->getFromClause()), $this->normalizeStatement($rightStatement2, true));
		
		$select->setUsedAliases(array('people', 'otherPeople'));
		$t = & $select->getTable('relations');
		$t->joinType = 'LEFT JOIN';
		$select->autoLoosenJoins = true;
		$rightStatement3 = "
			`#__people` AS `people`
    			LEFT JOIN `#__relations` AS `relations` ON `people`.`personId` = `relations`.`personId`
    			LEFT JOIN `#__people` AS `otherPeople` ON  `relations`.`otherPersonId` = `otherPeople`.`personId`
    		";
		$this->assertEqual($this->normalizeStatement($select->getFromClause()), $this->normalizeStatement($rightStatement3, true));
		
		$t->joinType = 'RIGHT JOIN';
		$select->autoLoosenJoins = true;
		$rightStatement4 = "
			`#__people` AS `people`
    			RIGHT JOIN `#__relations` AS `relations` ON `people`.`personId` = `relations`.`personId`
    			RIGHT JOIN `#__people` AS `otherPeople` ON  `relations`.`otherPersonId` = `otherPeople`.`personId`
    		";
		$this->assertEqual($this->normalizeStatement($select->getFromClause()), $this->normalizeStatement($rightStatement4, true));
		
		$select->setUsedAliases(array('people', 'relations'));
		$t = & $select->getTable('relations');
		$t->joinType = '';
		$t->joinsOn = '';
		
		$rightStatement5 = "
			`#__people` AS `people`,
     		`#__relations` AS `relations`
     	";
		$this->assertEqual($this->normalizeStatement($select->getFromClause()), $this->normalizeStatement($rightStatement5, true));
	}
	
	function testSupplementaryFunctions() {
		$sqlSelect = & $this->createMySelect();
		//$this->assertTrue($sqlSelect->hasTable('people'));
		$this->assertIsA($sqlSelect->getTable('people'), 'Ae_Sql_Select_Table');
		$this->expectError(new PatternExpectation('/is already in tables collection/i'));
		$sqlSelect->addTable($options  = array(
			'alias' => 'people'
		));
		//$this->assertFalse($sqlSelect->hasTable('foobar'));
		$this->expectError(new PatternExpectation('/no such table/i'));
		$sqlSelect->getTable('foobar');
		
		unset($sqlSelect);
		$sqlSelect = & $this->createMySelect();
		
		$t = & $sqlSelect->getTable('relations');
		$t->joinType = false;
		$sqlSelect->useAlias('relations');
		$this->expectError(new PatternExpectation('/type join don\'t needs \\$joinsOn, but it\'s provided/i'));
		$sqlSelect->getFromClause();
		
		$t->joinType = ',';
		$this->expectError(new PatternExpectation('/type join don\'t needs \\$joinsOn, but it\'s provided/i'));
		$sqlSelect->getFromClause();
		
		$t->joinsOn = array();
		$rightStatement = "
			`#__people` AS `people`,
     		`#__relations` AS `relations`
     	";
		$this->assertEqual($this->normalizeStatement($sqlSelect->getFromClause()), $this->normalizeStatement($rightStatement, true));
		
		$t->joinType = 'NATURAL JOIN';
		$t->joinsOn = array('foo');
		$this->expectError(new PatternExpectation('/type join don\'t needs \\$joinsOn, but it\'s provided/i'));
		$sqlSelect->getFromClause();
		
		$t->joinType = 'INNER JOIN';
		$t->joinsOn = array();
		$this->expectError(new PatternExpectation('/\$joinsOn property not provided for/i'));
		$sqlSelect->getFromClause();
	}
	
}