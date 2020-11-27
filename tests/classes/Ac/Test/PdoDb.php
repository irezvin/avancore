<?php
    
require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_PdoDb extends Ac_Test_Base {
    
    function testPdo() {
        
        require('pdo.config.php');
        
        $pdoParams = array(
            'dsn' => "mysql:dbname=".$config['db'].";host=127.0.0.1",
            'username' => $config['user'],
            'password' => $config['password'],
        );
        $db = new Ac_Sql_Db_Pdo([
            'dialect' => new Ac_Sql_Dialect_Mysql(),
            'pdo' => $pdoParams, 
            'initCommands' => [
                "set names utf8",
                "set @foo := 'bar'",
                "set @baz := 'quux'"
            ],
        ]);
        $pdo = $db->getPdo();
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->assertEqual($v = $db->fetchRow('select @foo a, @baz b'), ['a' => 'bar', 'b' => 'quux'],
            'Init commands were called');
        $this->assertTrue(is_array($db->fetchColumn('SHOW TABLES')));
        $this->assertEqual($db->nameQuote('Foo'), "`Foo`");
        $this->assertEqual($db->nameUnqote("`Foo`"), "Foo");
        $a = $db->fetchArray('SELECT * FROM ac_people');
        $b = $db->fetchArray('SELECT * FROM ac_people', false, true);
        
        $this->assertTrue(!isset($a[0][0]));
        $this->assertTrue(isset($b[0][0]));
        
        $a = $db->fetchArray("SELECT * FROM ac_people WHERE personId IN (3, 4) ORDER BY personId ASC", array('gender', 'name'));
        $this->assertArraysMatch(array(
            'M' => array('Илья' => array(0 => array('name' => 'Илья', 'gender' => 'M', 'personId' => 3))),
            'F' => array('Таня' => array(0 => array('name' => 'Таня', 'gender' => 'F', 'personId' => 4)))
        ), $a);
        //var_dump($a);
        
        $b = $db->fetchArray("SELECT * FROM ac_people ORDER BY personId ASC", array('gender', 'name', true));
        $this->assertArraysMatch(array(
            'M' => array('Илья' => array('name' => 'Илья', 'gender' => 'M', 'personId' => 3)),
            'F' => array('Таня' => array('name' => 'Таня', 'gender' => 'F', 'personId' => 4))
        ), $b);
        //var_dump($b);
        
        $c = $db->fetchColumn(
            "SELECT isSingle, gender, name FROM ac_people WHERE personId IN (3, 4) ORDER BY personId ASC", 
            'name',
            array('isSingle', 'gender', true)
        );
        $this->assertArraysMatch(array(
            0 => array('M' => 'Илья', 'F' => 'Таня'),
        ), $c);
        //var_dump($c);
    }
    
}