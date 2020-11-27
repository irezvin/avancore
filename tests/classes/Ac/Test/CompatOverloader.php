<?php

class Ac_Test_CompatOverloader extends Ac_Test_Base {
    
    function testCompatOverloader() {
        $f = new OverloaderTestClass([
            'foo' => 10,
            'bar' => 20,
            'quux' => 21
        ]);
            $this->assertEqual($f->newFoo, 10);
            $this->assertEqual($f->getNewBar(), 20);
            $this->assertEqual($f->getQuux(), 21);
        
        $f->foo = 30;
            $this->assertEqual($f->foo, 30);
            $this->assertEqual($f->newFoo, 30);
       
        $f->bar = 40;
            $this->assertEqual($f->bar, 40);
            $this->assertEqual($f->getNewBar(), 40);
       
        $f->quux = 22;
            $this->assertEqual($f->quux, 22);
            $this->assertEqual($f->getQuux(), 22);
       
        $this->assertEqual($f->newMethod(10), [10]);
        $this->assertEqual($f->newMethod(10, 20), [10, 20]);
        $this->assertEqual($f->newMethod(10, 20, 30), [10, 20, 30]);
        $this->assertEqual($f->newMethod(10, 20, 30, 40), [10, 20, 30, 40]);
        
        
        $this->expectError('Undefined property: OverloaderTestClass::$nonExistentProperty');
        $ret = $f->nonExistentProperty;
        
        $this->assertFalse(isset($f->nonExistentProperty2));
        $f->nonExistentProperty2 = 10;
        $this->assertTrue(isset($f->nonExistentProperty2));
        
        $this->expectError('Call to undefined method OverloaderTestClass::nonExistentMethod()');
        $f->nonExistentMethod();
        
    }
    
}

        
class OverloaderTestClass extends Ac_Prototyped {
    
    use Ac_Compat_Overloader;
    
    protected static $_compat_foo = 'newFoo';
    
    protected static $_compat_bar = 'newBar';
    
    protected static $_compat_quux = true;
    
    protected static $_compat_method = 'newMethod';
    
    function hasPublicVars() {
        return true;
    }
    
    var $newFoo = false;
    
    protected $quux = false;
    
    protected $newBar = 'zz';
    
    function getNewBar() {
        return $this->newBar;
    }
    
    function setNewBar($v) {
        $this->newBar = $v;
    }
    
    function getQuux() {
        return $this->quux;
    }
    
    function setQuux($v) {
        $this->quux = $v;
    }
    
    function newMethod() {
        return (func_get_args());
    }
    
}
