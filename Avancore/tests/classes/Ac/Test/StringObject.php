<?php

class Ac_Test_StringObject extends Ac_Test_Base {
    
    function testStringObject() {
        $c = new TestStringContainer();
        
        $item1 = new TestStringObject('item1');
        $item2 = new TestStringObject2('item2');
        $item3 = clone $item1; 
        $item3->data .= '.clone';
        
        $this->assertNotEqual($item3->getStringObjectMark(), $item2->getStringObjectMark(), "stringObjectMark should change after cloning");
        
        
        $c->buf = "Item 1: $item1, item 2: $item2, item 3: $item3";
        
        if (!$this->assertEqual($c->buf, 
            "Item 1: ".($item1->getStringObjectMark()).", item 2: ".($item2->getStringObjectMark()).", item 3: ".($item3->getStringObjectMark()),
            "items should return string object marks on __toString()"
        )) var_dump($c->buf);
        
        if (!$this->assertEqual(
            $repl = Ac_StringObject::replaceObjects($c->buf, 'getData'), 
            "Item 1: ".($item1->data).", item 2: ".($item2->data).", item 3: ".($item3->data),
            "replaceObjects should properly recognize string marks and replace strings"
        )) var_dump($repl);
        
        if (!$this->assertEqual(
            $repl = Ac_StringObject::replaceObjects($c->buf, 'getFooData', true), 
            "Item 1: ".($item1->getStringObjectMark()).", item 2: ".($item2->getFooData()).", item 3: ".($item3->getStringObjectMark()),
            "replaceObjects with \$checkMethod=true should replace only supporting objects"
        )) var_dump($repl);
        
    }
    
    function testEvaluatedStringObject() {
    }
    
}

class TestStringObject implements Ac_I_StringObject {
    
    protected $stringObjectMark = false;
    
    var $data = null;

    function __construct($data = null) {
        Ac_StringObject::onConstruct($this);
        $this->data = $data;
    }
    
    function __clone() {
        Ac_StringObject::onClone($this);
    }
    
    function __wakeup() {
        Ac_StringObject::onWakeup($this);
    }
    
    function setStringObjectMark($stringObjectMark) {
        $this->stringObjectMark = $stringObjectMark;
    }

    function getStringObjectMark() {
        return $this->stringObjectMark;
    }
    
    function __toString() {
        return $this->stringObjectMark;
    }
    
    function getData() {
        return $this->data;
    }
    
}

class TestStringObject2 extends TestStringObject {
    
    function getFooData() {
        return '***'.$this->data.'***';
    }
    
}

class TestStringContainer implements Ac_I_StringObjectContainer {
    
    var $buf1 = '';
    
    var $buf2 = '';
    
    var $stringObjects = array();
    
    function getStringBuffers() {
        return array($this->buf1, $this->buf2);
    }    
    
    function registerStringObjects(array $stringObjects) {
        $this->stringObjects = array_merge($this->stringObjects, Ac_StringObject::registerMany($stringObjects));
    }
    
}