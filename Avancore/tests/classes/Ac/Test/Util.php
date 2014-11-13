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
    
    function testFlattenArray() {
        $arr = array(
            'foo',
            'bar' => array(
                'baz',
                'quux' => 'moo'
            )
        );
        $f = Ac_Util::flattenArray($arr, -1, '.');
        if (!$this->assertEqual($f = Ac_Util::flattenArray($arr, -1, '.'), array(
            'foo',
            'bar.0' => 'baz',
            'bar.quux' => 'moo',
        ))) {
            var_dump($f);
        }
    }
    
    function testOverriddenMethods() {
        require_once(dirname(__FILE__).'/assets/classesWithOverrides.php');
        $this->assertFalse(Ac_Util::isMethodOverridden('aBaseClass', 'aBaseClass', 'foo'));
        $this->assertFalse(Ac_Util::isMethodOverridden('aBaseClass', 'aBaseClass', 'bar'));
        $this->assertFalse(Ac_Util::isMethodOverridden('aBaseClass', 'aBaseClass', 'baz'));
        $this->assertTrue(!Ac_Util::isMethodOverridden('aBaseClass', 'aBaseClass'));
        
        $this->assertTrue(Ac_Util::isMethodOverridden('aDescendantClass', 'aBaseClass', 'foo'));
        $this->assertFalse(Ac_Util::isMethodOverridden('aDescendantClass', 'aBaseClass', 'bar'));
        $this->assertFalse(Ac_Util::isMethodOverridden('aDescendantClass', 'aBaseClass', 'baz'));
        $this->assertTrue(!array_diff($a = Ac_Util::isMethodOverridden('aDescendantClass', 'aBaseClass'), array('foo')));
        
        $this->assertTrue(Ac_Util::isMethodOverridden('aNotherDescendantClass', 'aBaseClass', 'foo'));
        $this->assertTrue(Ac_Util::isMethodOverridden('aNotherDescendantClass', 'aBaseClass', 'bar'));
        $this->assertFalse(Ac_Util::isMethodOverridden('aNotherDescendantClass', 'aBaseClass', 'baz'));
        $this->assertTrue(!array_diff(Ac_Util::isMethodOverridden('aNotherDescendantClass', 'aBaseClass'), array('foo', 'bar')));
    }
    
}