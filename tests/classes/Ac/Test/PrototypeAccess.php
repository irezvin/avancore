<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_PrototypeAccess extends Ac_Test_Base {
    
    function testStrictParams() {
        Ac_Prototyped::$strictParams = true;
        $e = Ac_Prototyped::factory(array(
            'class' => 'ApSample',
            'foo' => 'fooVal',
            //'bar' => 'barVal',
            'baz' => 'bazVal'));
        $this->assertArraysMatch(Ac_Accessor::getObjectProperty($e, array('foo', /*'bar',*/ 'baz')), array('foo' => 'fooVal', /*'bar'=> 'barVal',*/ 'baz' => 'bazVal'));
        $this->expectException();
        $f = Ac_Prototyped::factory(array(
            'class' => 'ApSample',
            'foo' => 'fooVal',
            //'bar' => 'barVal',
            'baz' => 'bazVal',
            'quux' => 'quuxval'
        ));
    }
    
    function testNewFactory() {
        $a1 = Ac_Prototyped::factory(new ApSample, 'ApSample', array('baz' => 'baz1'), true);
        $a2 = Ac_Prototyped::factory(new ApSample, 'ApSample', array('baz' => 'baz2'), false);
        $a3 = Ac_Prototyped::factory(array(), 'ApSample', array('baz' => 'baz3'), false);
        $this->assertEqual($a1->getBaz(), 'baz1');
        $this->assertEqual($a2->getBaz(), false);
        $this->assertEqual($a3->getBaz(), 'baz3');
    }
    
    function testArgMapping() {
        $m = new ReflectionMethod('ApSample2', '__construct');
        $this->assertArraysMatch (
            Ac_Accessor::mapFunctionArgs($m, array('foo' => 1)), 
            array(1),
            'map arg by name'
        );
        $this->assertArraysMatch (
            Ac_Accessor::mapFunctionArgs($m, array('bar' => 2, 'foo' => 1)), 
            array(1, 2),
            'pass several args by name in different order'
        );
        $this->assertArraysMatch (
            Ac_Accessor::mapFunctionArgs($m, array('foo' => 1, 'bar' => 2)), 
            array(1, 2),
            'pass several args by name in proper order'
        );
        $this->assertArraysMatch (
            Ac_Accessor::mapFunctionArgs($m, array('baz' => 3)), 
            array(null, 'barDefault', 3),
            'supplying only deep arg puts default values before'
        );
        $this->assertArraysMatch (
            Ac_Accessor::mapFunctionArgs($m, array('2param' => 3, 'aaa')), 
            array('aaa', 'barDefault', 3),
            'supply args by index'
        );
        $this->assertArraysMatch (
            Ac_Accessor::mapFunctionArgs($m, array('1param' => 2, 'foo' => 'aaa')), 
            array('aaa', 2),
            'mix index- and name-based args'
        );
        
        $m2 = new ReflectionMethod('ApSample2', 'valBarBaz');
        
        $this->assertArraysMatch (
            Ac_Accessor::mapFunctionArgs($m2, array('bar' => 'barSet')), 
            array('barSet')
        );
        
        $a = Ac_Prototyped::factory(array(
            'class' => 'ApSample2', 
            '__construct' => array('foo' => 1, 'baz' => 3),
        ));
        
        $this->assertArraysMatch(array($a->foo, $a->bar, $a->baz), array(1, 'barDefault', 3));
        
        $a = Ac_Prototyped::factory(array(
            'class' => 'ApSample2', 
            '__construct' => array('foo' => 1, 'baz' => 3),
            '__initialize' => array(
                'valBarBaz' => array('baz' => 'aBaz', 'bar' => 'aBar'),
            ),
            'foo' => 4,
            'baz' => 5
        ));

        
        $this->expectException(false, 
           '$prototype[__initialize] for non-Ac_Prortotyped does allow to call public methods only'
        );
        
        $e = Ac_Prototyped::factory(array(
            'class' => 'ApSample2',
            '__construct' => array(null),
            '__initialize' => array('protMethod' => array())
        ));
        
        $this->assertArraysMatch(array($a->foo, $a->bar, $a->baz), array(4, 'aBar', 5));

        $e = Ac_Prototyped::factory(array(
            'class' => 'ApSample',
            'baz' => 'setBaz',
            '__initialize' => array('assignBarAndBaz' => array(10, 20))
        ));
        
        $this->assertEqual(
            array($e->bar, $e->getBaz()), 
            array(10, 'setBaz'),
            '$prototype[__initialize] works for Ac_Prototyped too; properties overwrite values set by __initialize'
        );

        $this->expectException(false, 
           '$prototype[__initialize] for Ac_Prototyped does allow to call public methods only'
        );
        
        $e = Ac_Prototyped::factory(array(
            'class' => 'ApSample',
            '__initialize' => array('protMethod' => array())
        ));
        
    }
    
}

class ApSample extends Ac_Prototyped {

    var $foo = null;
    
    protected $barVal = null;
    
    protected $bazVal = null;

    function hasPublicVars() {
        return true;
    }
    
    function __set($prop, $val) {
        if ($prop == 'bar') $this->barVal = $val;
    }
    
    function __get($prop) {
        if ($prop == 'bar') return $this->barVal;
    }
    
    function __isset($prop) {
        if ($prop == 'bar') return true;
    }
    
    function __list_magic() {
        return array('bar');
    }
    
    function setBaz($value) {
        $this->bazVal = $value;
    }
    
    function getBaz() {
        return $this->bazVal;
    }
    
    function getReadOnlyParam() {
        return 'readOnlyValue';
    }
    
    function setWriteOnlyParam($value) {
    }
    
    function assignBarAndBaz($barVal, $bazVal = 'defaultBaz') {
        $this->barVal = $barVal;
        $this->bazVal = $bazVal;
    }
    
    protected function protMethod() {
    }
    
}

class ApSample2 {
    
    var $foo = false;
    var $bar = false;
    var $baz = false;
    
    function __construct($foo, $bar = 'barDefault', $baz = 'bazDefault') {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->baz = $baz;
    }
    
    function valBarBaz($bar, $baz = 'bazSet') {
        $this->bar = $bar;
        $this->baz = $baz;
    }
    
    function setFoo($foo) {
        $this->foo = $foo;
    }
    
    protected function protMethod($prot) {
    }
    
}

class ApSample3 {
    protected $foo = false;
    protected $bar = false;
    
    function setFoo($val) {
        if ($val == 'ex1') throw new ex1("setFoo: \$val cannot be 'ex1'!");
        if ($val == 'ex2') throw new ex2("setFoo: \$val cannot be 'ex2'!");
        if ($val == 'ex') throw new ex("setFoo: \$val cannot be 'ex'!");
        $this->foo = $val;
    }
    
    function setBar($val) {
        if ($val == 'ex1') throw new ex1("setBar: \$val cannot be 'ex1'!");
        if ($val == 'ex2') throw new ex2("setBar: \$val cannot be 'ex2'!");
        if ($val == 'ex') throw new ex("setBar: \$val cannot be 'ex'!");
        $this->bar = $val;
    }
    
    function getFoo() {
        return $this->foo;
    }
    
    function getBar() {
        return $this->bar;
    }
}

class ex extends Exception {
}

class ex1 extends ex {
}

class ex2 extends ex {
}

