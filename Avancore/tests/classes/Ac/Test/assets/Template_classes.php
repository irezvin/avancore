<?php


class TestTemplate1 extends Ac_Template {

    // Public -- for testing purposes
    function getStack() {
        return $this->stack;
    }
    
    function getSignature($class, $method) {
        return parent::getSignature($class, $method);
    }
    
    function getArgs($object, $method, array $args, & $missingArgs = array()) {
        return parent::getArgs($object, $method, $args, $missingArgs);
    }
    
    protected function partPart1() {
?>
        Part1: 
        var1 = <?php echo $this->var1; ?> 
        var2 = <?php echo $this->var2; ?>
<?php
    }
    
    protected function partPart2() {
?>
        Part1: 
        var1 = <?php echo $this->var1; ?> 
        var2 = <?php echo $this->var2; ?>
<?php
    }
    
    protected function partObjects(TestObject1_1 $object1, TestObject1_2 $object2) {
?>
        Objects: 
        object1 = <?php echo $object1; ?> 
        object2 = <?php echo $object2; ?>
<?php
    }
    
    protected function partSomeObjects(TestObject1_1 $object1, TestObject1 $object3 = null) {
?>
        SomeObjects: 
        object1 = <?php echo $object1; ?> 
        object2 = <?php echo $object3; ?>
<?php
    }
    
    protected function partNoObjectMatch(TestObject1_2 $object1 = null) {
?>
        NoObjectMatch:
        object1 = <?php echo $object1; ?> 
<?php
    }
    
    protected function partArray(array $foo = array()) {
?>        
        Array: <?php echo implode(", ", $foo); ?> 
<?php        
    }
    
    protected function partWrapper1($buffer) { 
?>
        Begin wrapper1
<?php   echo $buffer; ?> 
        End wrapper1
<?php
    }
    
    protected function partWrapper2($buffer) { 
?>
        Begin wrapper2
<?php   echo $buffer; ?> 
        End wrapper2
<?php
    }
    
    protected function partWithWrapper1() {
        $this->wrap('wrapper1');
?>        
        Text of part with wrapper1
<?php        
    }
    
    protected function partWithoutWrapper() {
        $this->dontWrap();
?>        
        Text of part without wrapper
<?php        
    }
    
    protected function partWithNesting() {
        $this->wrap('wrapper2');
?>
        Part with nesting:
        
        <?php $this->showWithWrapper1(); ?>
        
        <?php $this->showWithoutWrapper(); ?>
        
        <?php $this->showObjects(); ?>
        
<?php
    }
    
}

class TestComponent extends Ac_Prototyped {

    var $var1 = 'val1';
    var $var2 = 'val2';
    var $object1 = null;

    protected $partName = 'part1';
    protected $object2 = null;
    
    function __construct(array $prototype = array()) {
        
        parent::__construct($prototype);
    }
    
    function hasPublicVars() {
        return true;
    }
    
    function setPartName($partName) {
        $this->partName = $partName;
    }

    function getPartName() {
        return $this->partName;
    }

    function setObject2($object2) {
        $this->object2 = $object2;
    }

    function getObject2() {
        return $this->object2;
    }    
}

class TestObject1 {
    
    var $value;
    
    function __construct($value = 'TestObject1') {
        $this->value = $value;
    }
    
    function __toString() {
        return $this->value.' '.get_class($this);
    }
    
}

class TestObject1_1 extends TestObject1 {
}

class TestObject1_2 extends TestObject1 {}