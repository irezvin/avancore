<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ae_Test_Autoparams extends Ae_Test_Base {
    
    function testStrictParams() {
        Ae_Autoparams::$strictParams = true;
        $e = Ae_Autoparams::factory(array(
            'class' => 'ApSample',
            'foo' => 'fooVal',
            //'bar' => 'barVal',
            'baz' => 'bazVal'));
        $this->assertArraysMatch(Ae_Autoparams::getObjectProperty($e, array('foo', /*'bar',*/ 'baz')), array('foo' => 'fooVal', /*'bar'=> 'barVal',*/ 'baz' => 'bazVal'));
        $this->expectException();
        $f = Ae_Autoparams::factory(array(
            'class' => 'ApSample',
            'foo' => 'fooVal',
            //'bar' => 'barVal',
            'baz' => 'bazVal',
            'quux' => 'quuxval'
        ));
    }    
}

class ApSample extends Ae_Autoparams {

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
    
    function setBaz($value) {
        $this->bazVal = $value;
    }
    
    function getBaz() {
        return $this->bazVal;
    }
    
}