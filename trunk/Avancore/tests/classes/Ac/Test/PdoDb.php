<?php
    
require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_PdoDb extends Ac_Test_Base {
    
    function testPdo() {
        
        require_once('app.config.php');
        
        $pdoParams = array(
            'dsn' => "mysql:dbname=".$config['db'].";host=127.0.0.1",
            'username' => $config['user'],
            'password' => $config['password'],
            'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8;')
        );
        $db = new Ac_Sql_Db_Pdo($pdoParams, new Ac_Sql_Quoter_Mysql);
        $pdo = $db->getPdo();
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->assertTrue(is_array($db->fetchColumn('SHOW TABLES')));
        $this->assertEqual($db->nameQuote('Foo'), "`Foo`");
        $this->assertEqual($db->nameUnqote("`Foo`"), "Foo");
        $a = $db->fetchArray('SELECT * FROM ac_people');
        $b = $db->fetchArray('SELECT * FROM ac_people', false, true);
        $this->assertTrue(!isset($a[0][0]));
        $this->assertTrue(isset($b[0][0]));
    }
    
}