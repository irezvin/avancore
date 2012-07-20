<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Autoparams extends Ac_Test_Base {
    
    function testStrictParams() {
        Ac_Autoparams::$strictParams = true;
        $e = Ac_Autoparams::factory(array(
            'class' => 'ApSample',
            'foo' => 'fooVal',
            //'bar' => 'barVal',
            'baz' => 'bazVal'));
        $this->assertArraysMatch(Ac_Autoparams::getObjectProperty($e, array('foo', /*'bar',*/ 'baz')), array('foo' => 'fooVal', /*'bar'=> 'barVal',*/ 'baz' => 'bazVal'));
        $this->expectException();
        $f = Ac_Autoparams::factory(array(
            'class' => 'ApSample',
            'foo' => 'fooVal',
            //'bar' => 'barVal',
            'baz' => 'bazVal',
            'quux' => 'quuxval'
        ));
    }
    
    function testAccessor() {
        $obj = new ApSample();
        $acc = new Ac_Accessor($obj);
        $this->assertTrue(!array_diff($acc->listProperties(), array('foo', 'bar', 'baz', 'readOnlyParam', 'writeOnlyParam')));
        $acc->foo = '123';
        $this->assertEqual($obj->foo, '123');
        $acc->bar = '123';
        $this->assertEqual($obj->bar, '123');
        $acc->baz = '123';
        $this->assertEqual($obj->getBaz(), '123');
    }
    
    
}

class ApSample extends Ac_Autoparams {

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
    
}