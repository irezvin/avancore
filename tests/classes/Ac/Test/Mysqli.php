<?php

class Ac_Test_Mysqli extends Ac_Test_Base {
    
    function testMysqli() {
        require('pdo.config.php');
        $db = new Ac_Sql_Db_Mysqli([
            'dbName' => $dbname,
            'dbPrefix' => $dbprefix,
            'username' => $dbuser,
            'password' => $dbpassword,
            'host' => $dbhost,
            'initCommands' => [
                'set @foo := 10',
                'set @bar := 20'
            ]
        ]);
        if (!$this->assertEqual($r = $db->args('10', '20', '30')->fetchRow(
                "SELECT ? a, ? b, ? c"), ['a' => '10', 'b' => '20', 'c' => '30']
        )) {
            var_dump($r);
        }
        if (!$this->assertEqual($r = $db->fetchRow("SELECT @foo foo, @bar bar"), 
            ['foo' => '10', 'bar' => '20']
        , "Should run initCommands")) {
            var_dump($r);
        }
        $this->assertTrue($db->getLink(), 'Db link returned');
        $db->query("
            DROP TABLE IF EXISTS tmp_mysqli
        ");
        $db->query("
            CREATE TEMPORARY TABLE tmp_mysqli AS 
            SELECT 1 AS id, 'First Item' AS title
            UNION SELECT 2, 'Second Item'
            UNION SELECT 3, 'Third Item'
            UNION SELECT 4, 'Fourth Item'
        ");
        if (!$this->assertEqual($r = $db->fetchArray("SELECT * FROM tmp_mysqli"), [
            ['id' => '1', 'title' => 'First Item'],
            ['id' => '2', 'title' => 'Second Item'],
            ['id' => '3', 'title' => 'Third Item'],
            ['id' => '4', 'title' => 'Fourth Item'],
        ], "fetchArray works")) {
            var_dump($r);
        }
        if (!$this->assertEqual($r = $db->fetchColumn("SELECT title FROM tmp_mysqli"), [
            'First Item', 'Second Item', 'Third Item', 'Fourth Item'
        ], "fetchColumn works")) {
            var_dump($r);
        }
        if (!$this->assertEqual($r = $db->fetchColumn("SELECT id, title FROM tmp_mysqli", "id", "title"), [
            'First Item' => '1',
            'Second Item' => '2',
            'Third Item' => '3',
            'Fourth Item' => '4',
        ], "fetchColumn (key indexing) works")) {
            var_dump($r);
        }
        if (!$this->assertEqual($r = $db->fetchColumn("SELECT id, title FROM tmp_mysqli", 0, 1), [
            'First Item' => '1',
            'Second Item' => '2',
            'Third Item' => '3',
            'Fourth Item' => '4',
        ], "fetchColumn (numeric indexing) works")) {
            var_dump($r);
        }
        if (!$this->assertEqual($r = $db->fetchValue("SELECT COUNT(*) FROM tmp_mysqli"), '4', 'fetchValue works')) {
            var_dump($r);
        }
        if (!$this->assertEqual($r = $db->fetchValue("SELECT title FROM tmp_mysqli LIMIT 1"), 
            'First Item', 'fetchValue (col indexing) works')) 
        {
            var_dump($r);
        }
        if (!$this->assertEqual($r = $db->fetchValue("SELECT * FROM tmp_mysqli LIMIT 1", "title"),
            'First Item', 'fetchValue (assoc col name) works')) 
        {
            var_dump($r);
        }
        if (!$this->assertEqual($r = $db->fetchValue("SELECT id, 'Foo', title FROM tmp_mysqli LIMIT 1", 2),
            'First Item', 'fetchValue (num col name) works')) 
        {
            var_dump($r);
        }
        $this->assertEqual($db->q('Foo'), "'Foo'", "q() works");
        $this->assertEqual($db->n('Bar'), "`Bar`", "n() works");
        
        $db->query("CREATE TEMPORARY TABLE tmp_mysqli_2 AS SELECT 'b1' id, 'b2' title");
        $rr = $db->getResultResource("SELECT a.*, b.*, 'val' AS `expr` FROM tmp_mysqli a, tmp_mysqli_2 b LIMIT 2");
        $byTables = [];
        while ($row = $db->resultFetchAssocByTables($rr, $fieldsInfo)) {
            $byTables[] = $row;
        }
        if (!$this->assertEqual($byTables, [
            ['a' => ['id' => 1, 'title' => 'First Item'], 'b' => ['id' => 'b1', 'title' => 'b2'], '' => ['expr' => 'val']],
            ['a' => ['id' => 2, 'title' => 'Second Item'], 'b' => ['id' => 'b1', 'title' => 'b2'], '' => ['expr' => 'val']]
        ], "resultFetchAssocByTables works")) {
            var_dump($byTables);
        }
    }
    
}