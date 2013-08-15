<?php

class Ac_Test_Util extends Ac_Test_Base {
    
    function testUtil() {
        
        $a = array('foo' => array());
        $b = array('foo'=> array(1, 2, 3));
        $c = Ac_Util::m($a, $b);
        if (!$this->assertEqual($c['foo'], $b['foo'])) var_dump($c);
        
    }
    
    function testIndexArray() {
        
        $c = new stdClass();
        $c->foo =  10;
        $c->bar = 20;
        
        $a = array(
            array('foo' => 5, 'bar' => 6),
            array('foo' => 6, 'bar' => 7),
            $c
        );
        
        if (!$this->assertEqual($idx = Ac_Util::indexArray($a, array('foo', 'bar'), false), $need = array(
            5 => array(6 => array(array('foo' => 5, 'bar' => 6))),
            6 => array(7 => array(array('foo' => 6, 'bar' => 7))),
            10=> array(20 => array($c))
        ))) {
            var_dump($idx);
        }
        
        if (!$this->assertEqual($idx = Ac_Util::indexArray($a, array('foo', 'bar'), true), $need = array(
            5 => array(6 => array('foo' => 5, 'bar' => 6)),
            6 => array(7 => array('foo' => 6, 'bar' => 7)),
            10=> array(20 => $c)
        ))) {
            var_dump($idx);
        }
        
        if (!$this->assertEqual($idx = Ac_Util::indexArray($a, 'foo', false), $need = array(
            5 => array((array('foo' => 5, 'bar' => 6))),
            6 => array((array('foo' => 6, 'bar' => 7))),
            10=> array(($c))
        ))) {
            var_dump($idx);
        }
        
        if (!$this->assertEqual($idx = Ac_Util::indexArray($a, array('bar'), true), $need = array(
            6 => array('foo' => 5, 'bar' => 6),
            7 => array('foo' => 6, 'bar' => 7),
            20 => $c
        ))) {
            var_dump($idx);
        }
        
    }
    
}