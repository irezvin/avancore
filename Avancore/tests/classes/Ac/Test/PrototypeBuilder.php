<?php

class Ac_Test_PrototypeBuilder extends Ac_Test_Base {
    
    var $targetProto = array(
        'instance1' => array(
            'class' => 'Foobar',
            'prop1' => 'val1',
            'prop2' => 'val2',
            'prop3' => 'val3',
            'prop4' => 'valProp4.1',
            'overrideProp' => 'overrideValue'
        ),
        'instance2' => array(
            'class' => 'aClass',
            'prop1' => 'val1',
            'prop2' => 'val2',
            'prop3' => 'valProp3.2',
            'overrideProp' => 'overrideValue'
        ),
    );
    
    function testPrototypeBuilder() {
        
        $target = null;
        $b = new Ac_Prototype_Builder();
        
        $b->addDefault(array('class' => 'Foobar'), array('prop1' => 'val1', 'prop2' => 'val2', 'prop3' => 'val3'));
        $b->addOverride(array('overrideProp' => 'overrideValue'));
        $b->addPrototype('instance1', array('prop4' => 'valProp4.1', 'overrideProp' => 'uWontSeeMee'));
        $b->addPrototype('instance2', array('class' => 'aClass', 'prop3' => 'valProp3.2'));
        
        $this->assertEqual(
            $r = $b->getResult(), 
            $this->targetProto,
            'procedural building, no target'
        );
        
        $target = array();
        $b2 = new Ac_Prototype_Builder($target);
        
        $b2->addPrototype('instance1', array('prop4' => 'valProp4.1', 'overrideProp' => 'uWontSeeMee'));
        $b2->addOverride(array('overrideProp' => 'overrideValue'));
        $b2->addDefault(array('class' => 'Foobar'), array('prop1' => 'val1', 'prop2' => 'val2', 'prop3' => 'val3'));
        $b2->addPrototype('instance2', array('class' => 'aClass', 'prop3' => 'valProp3.2'));
        
        $this->assertEqual($target, $this->targetProto, 
            'using $target and a mixed order of building');
        
        $this->assertEqual($b2->getResult(), $this->targetProto, 
            'getResult() works too');
        
        $this->expectException(false, 'Throw on invalid $forKey in getResult()');
        $b2->getResult(false, 'noSuchKey');
        
    }    
    
    function testPrototypeBuilderSingle() {
        
        $target = null;
        $b = new Ac_Prototype_Builder($target, false, true);
        
        $b->addDefault(array('class' => 'Foobar'), array('prop1' => 'val1', 'prop2' => 'val2', 'prop3' => 'val3'));
        $b->addOverride(array('overrideProp' => 'overrideValue'));
        $b->addPrototype('instance1', array('prop4' => 'valProp4.1', 'overrideProp' => 'uWontSeeMee'));
        
        $this->assertEqual(
            $r = $b->getResult(), 
            $this->targetProto['instance1'],
            'procedural building of single prototype, no target'
        );
        
        $target = array();
        $b2 = new Ac_Prototype_Builder($target, false, true);
        
        $b2->addPrototype('instance1', array('prop4' => 'valProp4.1', 'overrideProp' => 'uWontSeeMee'));
        $b2->addOverride(array('overrideProp' => 'overrideValue'));
        $b2->addDefault(array('class' => 'Foobar'), array('prop1' => 'val1', 'prop2' => 'val2', 'prop3' => 'val3'));
        
        $this->assertEqual($target, $this->targetProto['instance1'], 
            'using $target and a mixed order of building of single prototype');
        
        $this->assertEqual($b2->getResult(), $this->targetProto['instance1'], 
            'getResult() for single prototype works too');
        
        $this->assertEqual($b2->getResult(true), array('instance1' => $this->targetProto['instance1']), 
            'getResult($alwaysReturnManu) for single prototype');
    }
    
    function testKeyToFromPrototype() {
        $target = null;
        $b = new Ac_Prototype_Builder($target, 'id');
        $b->addPrototype(array('id' => 'foo'));
        $b->addPrototype('bar', array());
        $this->assertEqual($r = $b->getResult(), array(
            'foo' => array('id' => 'foo'),
            'bar' => array('id' => 'bar'),
        ));
    }
    
    function testMagic() {
        $target = array();
        $b = new Ac_Prototype_Builder($target);
        
        $b->override(array('overrideProp' => 'overrideValue'))->also()
            ->class('Foobar')->prop1('val1')->prop2('val2')->prop3('val3')
            ->instance1 = array('prop4' => 'valProp4.1', 'overrideProp' => 'uWontSeeMee');
        
        $this->assertEqual($target, array('instance1' => $this->targetProto['instance1']));
    }
    
}