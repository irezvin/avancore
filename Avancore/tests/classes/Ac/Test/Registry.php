<?php

class Ac_Test_Registry extends Ac_Test_Base {
    
    function getSample() {
        return array(
            'a' => 'aValue',
            'b' => array(
                'b1' => 'b1Value',
                'b2' => 'b2Value'
            )
        );
    }
    
    function testArrayDive() {
        
        $sam = $this->getSample();
        
        $path = array('a');
        $this->assertTrue(Ac_Registry::arrayDive($sam, $path, $ptr));
        $this->assertEqual($path, array());
        $this->assertReference($ptr['ptr'], $sam['a']);
        
        $path = array('b', 'b1');
        $this->assertTrue(Ac_Registry::arrayDive($sam, $path, $ptr));
        $this->assertEqual($path, array());
        $this->assertReference($ptr['ptr'], $sam['b']['b1']);
        
        $path = array('b', 'b2', 'b21');
        $this->assertFalse(Ac_Registry::arrayDive($sam, $path, $ptr));
        $this->assertEqual($path, array('b21'));
        $this->assertReference($ptr['ptr'], $sam['b']['b2']);
        
        $path = array('c');
        $this->assertFalse(Ac_Registry::arrayDive($sam, $path, $ptr));
        $this->assertEqual($path, array('c'));
        $this->assertReference($ptr['ptr'], $sam);
        
        $path = array('b', 'b3');
        $this->assertFalse(Ac_Registry::arrayDive($sam, $path, $ptr));
        $this->assertEqual($path, array('b3'));
        $this->assertReference($ptr['ptr'], $sam['b']);
        
    }
    
    function getRegData1() {
        return array(
            'key1' => 'value1',
            'key2' => array(
                'key2.1' => 'value2.1',
                'key2.2' => 'value2.2'
            )
        );
    }
    
    function testSimpleRegistry() {
        $reg = new Ac_Registry();
        $reg->setRegistry($this->getRegData1());
        $this->assertEqual(
            $reg->getRegistry(), 
            $this->getRegData1(),
            'getRegistry() should return the same value as setRegistry()'
        );
        
        $this->assertIdentical($reg->hasRegistry('key1'), true);
        $this->assertIdentical($reg->hasRegistry('key2', 'key2.1'), true);
        $this->assertIdentical($reg->hasRegistry(array('key2', 'key2.1')), true);
        
        $this->assertIdentical($reg->listRegistry(), array('key1', 'key2'));
        $this->assertIdentical($reg->listRegistry('key1'), null);
        $this->assertIdentical($reg->listRegistry('key2'), array('key2.1', 'key2.2'));
        $this->assertIdentical($reg->listRegistry('key3'), null);
        
        $this->assertIdentical($reg->getRegistry('key1'), 'value1');
        $this->assertIdentical($reg->getRegistry('key2', 'key2.1'), 'value2.1');
        $this->assertIdentical($reg->getRegistry(array('key2', 'key2.1')), 'value2.1');
        
        $this->assertIdentical($reg->hasRegistry('key3'), false);
        $this->assertIdentical($reg->hasRegistry('key3', 'key3.1'), false);
        
        $this->assertIdentical($reg->getRegistry('key3'), null);
        $this->assertIdentical($reg->getRegistry('key3', 'key3.1'), null);
        
        $this->assertIdentical($reg->setRegistry('value3.1', 'key3', 'key3.1'), true);
        $this->assertIdentical($reg->getRegistry('key3', 'key3.1'), 'value3.1');
        
        $this->assertIdentical($reg->addRegistry('extra'), 0);
        $this->assertIdentical($reg->getRegistry(0), 'extra');
        
        $this->assertIdentical($reg->addRegistry('extra2', 'key2'), 0);
        $this->assertIdentical($reg->getRegistry('key2', 0), 'extra2');
        
        $this->assertIdentical($reg->deleteRegistry('key2', 'key2.1'), true);
        $this->assertIdentical($reg->getRegistry('key2', 'key2.1'), null);
        $this->assertIdentical($reg->deleteRegistry('key2', 'key2.1'), false);
    }
    
    function getRegData2() {
        
        $subReg = new Ac_Registry();
        $subReg->setRegistry(array(
            'key2.1' => 'value2.1',
            'key2.2' => 'value2.2'
        ));
        return array(
            'key1' => 'value1',
            'key2' => $subReg
        );
    }
    
    function testCompositeRegistry() {
        $reg = new Ac_Registry();
        
        $reg->setRegistry($this->getRegData2());
        
        $this->assertEqual(
            $reg->getRegistry(), 
            $this->getRegData2(),
            'getRegistry() should return the same value as setRegistry()'
        );
        
        $this->assertIdentical($d = $reg->exportRegistry(true), $this->getRegData1());
        
        $this->assertIdentical($reg->hasRegistry('key2', 'key2.1'), true);
        $this->assertIdentical($reg->hasRegistry(array('key2', 'key2.1')), true);
        
        $this->assertIdentical($reg->listRegistry('key2'), array('key2.1', 'key2.2'));
        
        $this->assertIdentical($reg->getRegistry('key2', 'key2.1'), 'value2.1');
        $this->assertIdentical($reg->getRegistry(array('key2', 'key2.1')), 'value2.1');
        
        $this->assertIdentical($reg->addRegistry('extra2', 'key2'), 0);
        $this->assertIdentical($reg->getRegistry('key2', 0), 'extra2');
        
        $this->assertIdentical($reg->deleteRegistry('key2', 'key2.1'), true);
        $this->assertIdentical($reg->getRegistry('key2', 'key2.1'), null);
        $this->assertIdentical($reg->deleteRegistry('key2', 'key2.1'), false);
        
        $reg2 = new Ac_Registry();
        $data = array('aaa' => 'bbb');
        $reg2->setRegistry($data);
        $this->assertIdentical($reg->setRegistry($reg2, 'key2'), true);
        $this->assertIdentical($reg->getRegistry('key2'), $data);
    }
    
    function testExceptions() {

        $reg = new Ac_Registry;
        $reg->setRegistry($this->getRegData1());
        
        $e = null;
        try {
            $reg->addRegistry('aaa', 'key1');
        } catch (Ac_E_Registry $e1) {
            $e = $e1;
        }
        $this->assertIsA($e, 'Ac_E_Registry');
        $this->assertIdentical($e->getTargetPath(), array('key1'));
        $this->assertIdentical($e->getRegDescr(), get_class($reg));
        $this->assertIdentical($e->getOpType(), Ac_E_Registry::opAddRegistry);
        
        $e = null;
        try {
            $reg->setRegistry('aaa', 'key1', 'key2.1');
        } catch (Ac_E_Registry $e1) {
            $e = $e1;
        }
        $this->assertIsA($e, 'Ac_E_Registry');
        $this->assertIdentical($e->getTargetPath(), array('key1', 'key2.1'));
        $this->assertIdentical($e->getRegDescr(), get_class($reg));
        $this->assertIdentical($e->getOpType(), Ac_E_Registry::opSetRegistry);
        
        $e = null;
        try {
            $reg->mergeRegistry(array('aaa'), false, 'key1', 'key2.1');
        } catch (Ac_E_Registry $e1) {
            $e = $e1;
        }
        $this->assertIsA($e, 'Ac_E_Registry');
        $this->assertIdentical($e->getTargetPath(), array('key1', 'key2.1'));
        $this->assertIdentical($e->getRegDescr(), get_class($reg));
        $this->assertIdentical($e->getOpType(), Ac_E_Registry::opMergeRegistry);

    }
    
    function testMerge() {
        
        $src = array(
            'key1' => 'value1',
            'key2' => array(
                'key2.1' => 'value2.1', 
                'key2.2' => 'value2.2',
                'extra2.3',
            ),
            'extra3'
        );
        
        $dest = array(
            'dest3',
            'key1' => 'dest1',
            'key2' => array(
                'key2.1' => 'dest2.1',
                'dest2.3',
                'key2.4' => 'dest2.4',
            ),
        );
        
        $reg1 = new Ac_Registry();
        
        $reg1->setRegistry($src);
        
        $reg2 = new Ac_Registry();
        
        $reg2->setRegistry($dest);
        
        $reg1->mergeRegistry($reg2);
        
        $this->assertIdentical($new = $reg1->exportRegistry(), $a = array(
            'key1' => 'dest1',
            'key2' => array(
                'key2.1' => 'dest2.1', 
                'key2.2' => 'value2.2',
                'extra2.3',
                'dest2.3',
                'key2.4' => 'dest2.4'
            ),
            'extra3',
            'dest3'
        ));
        
        $reg1->setRegistry($src);
        
        $reg1->mergeRegistry($reg2, true);
        
        $this->assertIdentical($new = $reg1->exportRegistry(), $a = array(
            'key1' => 'value1',
            'key2' => array(
                'key2.1' => 'value2.1', 
                'key2.2' => 'value2.2',
                'extra2.3',
                'dest2.3',
                'key2.4' => 'dest2.4'
            ),
            'extra3',
            'dest3'
        ));
        
    }
    
}