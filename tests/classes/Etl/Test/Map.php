<?php

class Etl_Test_Map extends Etl_Test_Class_Abstract {
        
    function testMap() {
    
        $cm = Ac_Etl_Map::create(array(
            'toCol1' => 'fromCol1', 
            'toCol2' => new Ac_Sql_Expression("CONCAT('--', fromCol2)"), 
            array(new Ac_Sql_Expression('toCol3'), 'fromCol3')
        ), new Ac_Sql_Db_Ae(), 'to', 'from');
        
        $a = array();
        
        foreach($cm as $item) $a[] = array($item->left(true), $item->right(true), $item->eq());
        
        if (!$this->assertEqual($a, array(
            array('`to`.`toCol1`', '`from`.`fromCol1`', '`to`.`toCol1` = `from`.`fromCol1`'),
            array('`to`.`toCol2`', 'CONCAT(\'--\', fromCol2)', '`to`.`toCol2` = CONCAT(\'--\', fromCol2)'),
            array('toCol3', '`from`.`fromCol3`', 'toCol3 = `from`.`fromCol3`'),
        ))) var_dump($a);
        
        $cm->wrapRight("IFNULL({o}, '')");
        
        $a = array();
        foreach($cm as $item) $a[] = array($item->left(true), $item->right(true), $item->eq());
        
        if (!$this->assertEqual($a, array(
            array('`to`.`toCol1`', 'IFNULL(`from`.`fromCol1`, \'\')', '`to`.`toCol1` = IFNULL(`from`.`fromCol1`, \'\')'),
            array('`to`.`toCol2`', 'IFNULL(CONCAT(\'--\', fromCol2), \'\')', '`to`.`toCol2` = IFNULL(CONCAT(\'--\', fromCol2), \'\')'),
            array('toCol3', 'IFNULL(`from`.`fromCol3`, \'\')', 'toCol3 = IFNULL(`from`.`fromCol3`, \'\')'),
        ))) var_dump($a);
        
        $cm->wrapLeft("/**/{o}");
        
        $a = array();
        foreach($cm as $item) $a[] = array($item->left(true), $item->right(true), $item->eq());
        
        if (!$this->assertEqual($a, array(
            array('/**/`to`.`toCol1`', 'IFNULL(`from`.`fromCol1`, \'\')', '/**/`to`.`toCol1` = IFNULL(`from`.`fromCol1`, \'\')'),
            array('/**/`to`.`toCol2`', 'IFNULL(CONCAT(\'--\', fromCol2), \'\')', '/**/`to`.`toCol2` = IFNULL(CONCAT(\'--\', fromCol2), \'\')'),
            array('/**/toCol3', 'IFNULL(`from`.`fromCol3`, \'\')', '/**/toCol3 = IFNULL(`from`.`fromCol3`, \'\')'),
        ))) var_dump($a);
        
        $a = array();
        
        $cm->merge(array('toCol2' => new Ac_Sql_Value('foo')));
        if (!$this->assertEqual($e = $cm->getItem(1)->eq(), "/**/`to`.`toCol2` = 'foo'")) var_dump($e);
        
        $cm->merge(array('toCol1' => new Ac_Sql_Value('xxx')), 'CONCAT({o}, {n})');
        if (!$this->assertEqual($e = $cm->getItem(0)->eq(), "/**/`to`.`toCol1` = CONCAT(IFNULL(`from`.`fromCol1`, ''), 'xxx')")) var_dump($e);

        $cm2 = $cm->cloneObject()->flip();
        $a = array();
        $eq = $cm2->eq(' = ', ' AND ', array('({o})', 'NOT {o}'));
        if (!$this->assertEqual($eq, 
            "NOT (CONCAT(IFNULL(`from`.`fromCol1`, ''), 'xxx') = /**/`to`.`toCol1`) ".
            "AND NOT ('foo' = /**/`to`.`toCol2`) ".
            "AND NOT (IFNULL(`from`.`fromCol3`, '') = /**/toCol3)"
        ))  var_dump($eq);
        
        $cm3 = Ac_Etl_Map::create(array('col1' => new Ac_Sql_Value('val1'), array(new Ac_Sql_Expression('col2'), new Ac_Sql_Value('val2'))), new Ac_Sql_Db_Ae(), 'dest', 'src');
        if (!$this->assertEqual($eq = $cm3->eq(), "`dest`.`col1` = 'val1', col2 = 'val2'")) var_dump($eq);
        
        $cm4 = $cm3->cloneObject()->diff(array(array(new Ac_Sql_Expression('col2'), 'foo')));
        if (!$this->assertEqual($eq = $cm4->eq(), "`dest`.`col1` = 'val1'")) var_dump($eq);
        
        $cm5 = $cm3->cloneObject()->union(array('col3' => 'nnn'));
        if (!$this->assertEqual($eq = $cm5->eq(), "`dest`.`col1` = 'val1', col2 = 'val2', `dest`.`col3` = `src`.`nnn`")) var_dump($eq);
        
        $this->assertEqual($cm5->intersect($cm3)->eq(), $cm3->eq());
        
        $cmv = Ac_Etl_Map::create(array('col1' => 10, 'col2' => 'abc', 'col3' => new Ac_Sql_Expression("CONCAT('foo', 'bar')")), new Ac_Sql_Db_Ae(), 'a', 'b');
        $cmv->rightAreValues();
        if (!($this->assertEqual($eq = $cmv->eq(), "`a`.`col1` = 10, `a`.`col2` = 'abc', `a`.`col3` = CONCAT('foo', 'bar')"))) {
            var_dump($eq);
        }
        
        $assoc = $cmv->assoc();
        
        if (!$this->assertEqual($assoc, array("`a`.`col1`" => "10", "`a`.`col2`" => "'abc'", "`a`.`col3`" => "CONCAT('foo', 'bar')"))) 
            var_dump($assoc);
        
        $lr = Ac_Etl_Map::create(array('c{o}{l}' => '--{o}--{l}--'), new Ac_Sql_Db_Ae(), 'l', 'r')->rightAreValues()->wrapRight('IFNULL({o}, {l})');
        
        if (!$this->assertEqual($eq = $lr->eq(), "`l`.`c{o}{l}` = IFNULL('--{o}--{l}--', `l`.`c{o}{l}`)")) var_dump($eq);
        
        $mapNumKeys = Ac_Etl_Map::create(array('col1', 'col2'), new Ac_Sql_Db_Ae(), 'a', 'b');
        
        if (!$this->assertEqual($eq = $mapNumKeys->eq(), '`a`.`col1` = `b`.`col1`, `a`.`col2` = `b`.`col2`')) var_dump($eq);
        
    }
    
    function testDuplicateKey() {
        $this->expectError(); // Warning about duplicate key
        $m = Ac_Etl_Map::create(array(
            array('foo', 'bar'), 
            array('foo', 'quux'),
        ),
        $db = new Ac_Sql_Db_Ae(), 
        'l', 'r');
//        var_dump($m->assoc());
//        $m->flip();
//        var_dump($m->copyStmt(new Ac_Sql_Select($db, array('tables' => array('t' => array('name' => 'example'))))));
        
    }
    
}