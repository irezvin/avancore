<?php

class Ac_Test_Lang extends Ac_Test_Base {
    
    static function callback2($id) {
        if ($id == 'first') return 'Le First';
        if ($id == 'second') return 'Le Second';
        if ($id == 'third') return 'Le Third';
        if ($id == 'fourth') return 'Le Fourth';
    }
    
    function testLangResourceCallbacks() {
        
        $r = new Ac_Lang_Resource();
        $r->setStrings([
            'first' => 'First String'
        ]);
        $callback1 = function($id) {
            if ($id == 'first') return 'First';
            if ($id == 'second') return 'Second';
            if ($id == 'fourth') return 'Fourth';
        };
        Ac_Lang_Resource::registerCallback($callback1);

        $callback2 = ['Ac_Test_Lang', 'callback2'];
        
            $this->assertIdentical(Ac_Lang_Resource::isCallbackRegistered($callback1), true);
            $this->assertIdentical(Ac_Lang_Resource::isCallbackRegistered($callback2), false);
        
        Ac_Lang_Resource::registerCallback($callback2);
        
            $this->assertIdentical(Ac_Lang_Resource::isCallbackRegistered($callback2), true);
        
        
            $this->assertEqual($r->getString('first'), 'First String');
            $this->assertEqual($r->getString('second'), 'Second');
            $this->assertEqual($r->getString('third'), 'Le Third');
        
        Ac_Lang_Resource::unregisterCallback($callback1);
            
            $this->assertIdentical(Ac_Lang_Resource::isCallbackRegistered($callback1), false);
            $this->assertEqual($r->getString('second'), 'Second', "String that was returned at least once is cached");
            $this->assertEqual($r->getString('fourth'), 'Le Fourth', "Provider was successfully unregistrered");
        
        
    }
    
    
}