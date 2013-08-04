<?php

require(dirname(__FILE__).'/assets/Template_classes.php');

class Ac_Test_Template extends Ac_Test_Base {

    function testInternals() {

        $t1 = new TestTemplate1;
        //var_dump($t1->getSignature('partSomeObjects'));
        //var_dump($t1->getSignature('partArray'));
        $missing = array();
        $e = null;
        try {
            var_dump($t1->getArgs('partObjects', array('foo' => 'bar'), $missing));
        } catch (Ac_E_Template $e) {
        }
        $this->assertNotNull($e);
        
        $this->assertEqual(
            $t1->getArgs('partObjects', $args = array('object1' => new TestObject1_1, 'object2' => new TestObject1_2), $missing), 
            array_values($args)
        );
        $this->assertEqual($missing, array());
        
        $t1->setValue('object1', $object1 = new TestObject1_1);
        $object2 = new TestObject1_2;
        $this->assertEqual(
            $t1->getArgs('partObjects', $args = array('object2' => $object2), $missing), 
            array($object1, $object2)
        );
        $this->assertEqual($missing, array());
        
        $t1->deleteValue('object1');
        $this->assertEqual(
            $t1->getArgs('partSomeObjects', $args = array(), $missing), 
            array(1 => null)
        );
        $this->assertEqual($missing, array('object1'));
        
        $t1->object1 = $object1 = new TestObject1_1;
        $t1->object2 = $object2 = new TestObject1_2;
        $t1->foo = array();
        $this->assertEqual(
            $t1->getArgs('partObjects', array(), $missing), 
            array($object1, $object2)
        );
        $this->assertEqual($missing, array());
        $this->assertEqual(
            $t1->getArgs('partArray', array(), $missing), 
            array(array())
        );
        $this->assertEqual($missing, array());
        
    }
    
    function testTpl() {
        
        $cVals = array(
            'var1' => 'var1value',
            'var2' => 'var2value',
            'object1' => new TestObject1_1('testObject1'),
            'object2' => new TestObject1_2('testObject2'),
        );
        
        $c = new TestComponent ($cVals);
        $t1 = new TestTemplate1;
        
        $t1->setComponent($c);

        $r = $t1->renderResult('part1');
        $this->assertIsA($r, 'Ac_Result_Html');
        if (!$this->assertEqual(
            $this->normalizeHtml($c = $r->getContent()), 
            $this->normalizeHtml("
                Part1:
                var1 = {$cVals['var1']}
                var2 = {$cVals['var2']}
            ")
        )) var_dump($c);
        $r = $t1->renderResult('objects');
        
        $this->assertIsA($r, 'Ac_Result_Html');
        if (!$this->assertEqual(
            $this->normalizeHtml($c = $r->getContent()), 
            $this->normalizeHtml("
                Objects:
                object1 = {$cVals['object1']}
                object2 = {$cVals['object2']}
            ")
        )) var_dump($c);
    }
    
}