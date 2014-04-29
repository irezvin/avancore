<?php

require_once(dirname(__FILE__).'/assets/MixinClasses.php');

class Ac_Test_Mixin extends Ac_Test_Base {
    
    function testSimpleMixin() {
        $b = new Body();
        $b->width = 1;
        $b->height = 2;
        $b->length = 3;
        $this->assertEqual($b->getVolume(), 6);
        $b->weight = 60;
        $this->assertEqual($b->getDensity(), 10);
        $this->assertEqual($b->getDENsitY(), 10, 'Case-insensitive method names support'); 
        $this->assertEqual($b->width, 1);
        $this->assertEqual($b->height, 2);
        $this->expectError("Undefined property: Body::\$moo");
        echo $b->moo;
        
        $b->randomVar = 10;
        $this->assertEqual($b->randomVar, 10);
        $this->assertEqual(isset($b->randomVar), true);
        unset($b->randomVar);
        $this->assertEqual(isset($b->randomVar), false);
        
        $this->expectError("Call to undefined method Body::m()");
        $b->m();
        
        $this->expectError("Cannot access protected property Body::\$protVar");
        $b->protVar = 10;
        
        $this->expectError("Call to protected method Body::protMethod() from context 'Ac_Test_Mixin'");
        $b->protMethod();
        
        $this->expectError("Call to private method Body::privMethod() from context 'Ac_Test_Mixin'");
        $b->privMethod();
        
        $this->assertEqual($b->listMixables(), array(0, 1));
        $this->assertEqual($b->listMixables('Weight'), array(0));
        $this->assertEqual($b->listMixables('Dimensions'), array(1));
    }
    
    function testModelMixin() {
        $p = new ModelProp;
        $md = new Ac_Model_Data(array('mixables' => array($p)));
        $this->assertEqual($md->listProperties(), array('extraProp', 'extraPublicProp'));
        $p->setExtraProp(10);
        $p->extraPublicProp = 20;
        $this->assertTrue($md->hasMethod('getExtraProp'));
        $this->assertEqual($md->getField('extraProp'), 10);
        $this->assertEqual($md->getField('extraPublicProp'), 20);
    }
    
}