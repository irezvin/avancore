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
    
    function testSliceBuffer() {
        $o1 = new TestStringObject();
        $o2 = new TestStringObject();
        $s = 'abc'.$o1.'def'.$o2.'ghi';
        $sliced = Ac_StringObject::sliceStringWithObjects($s);
        $this->assertIdentical($sliced, array(
            'abc', $o1, 'def', $o2, 'ghi'
        ));
    }
    
    function testStringObjectContext() {
        $o1 = new TestStringObject();
        $o2 = new TestStringObject();
        $o3 = new TestStringObject();
        $s = '111'.$o1.'222'.$o2.'333'.$o3.'444';
        
        $beg1 = strlen('111');
        $end1 = $beg1 + strlen($o1) - 1;
        
        $beg2  = strlen('111'.$o1.'222');
        $end2 = $beg2 + strlen($o2) - 1;
        
        $beg3  = strlen('111'.$o1.'222'.$o2.'333');
        $end3 = $beg3 + strlen($o3) - 1;
        
        $sliced = Ac_StringObject::sliceStringWithObjects($s);
        
        if (!$this->assertEqual( // before $o1
            $sc = Ac_StringObject::getStringObjectContext($s, 0), 
            array(
                'prev' => FALSE, 
                'current' => FALSE, 
                'next' => array(''.$o1, $beg1)
            )
        )) var_dump($sc);
        
        foreach (array($beg1, $beg1 + 1, $end1) as $pos)
            if (!$this->assertEqual( // at $o1
                $sc = Ac_StringObject::getStringObjectContext($s, $pos), 
                array(
                    'prev' => FALSE, 
                    'current' => array(''.$o1, $beg1), 
                    'next' => array(''.$o2, $beg2)
                )
            )) var_dump($pos, $sc);
            
        if (!$this->assertEqual( // between $o1 and $o2
            $sc = Ac_StringObject::getStringObjectContext($s, $end1 + 1), 
            array(
                'prev' => array(''.$o1, $beg1), 
                'current' => FALSE, 
                'next' => array(''.$o2, $beg2)
            )
        )) var_dump($sc);
            
        foreach (array($beg2, $beg2 + 1, $end2) as $pos)
            if (!$this->assertEqual( // at $o2
                $sc = Ac_StringObject::getStringObjectContext($s, $pos), 
                array(
                    'prev' => array(''.$o1, $beg1), 
                    'current' => array(''.$o2, $beg2), 
                    'next' => array(''.$o3, $beg3)
                )
            )) var_dump($pos, $sc);
        
        foreach (array($beg3, $beg3 + 1, $end3) as $pos)
            if (!$this->assertEqual( // at $o3; also cache is used
                $sc = Ac_StringObject::getStringObjectContext($s, $pos, true), 
                array(
                    'prev' => array(''.$o2, $beg2), 
                    'current' => array(''.$o3, $beg3), 
                    'next' => FALSE
                )
            )) var_dump($pos, $sc);

        foreach (array($end3 + 1, strlen($s) - 1, strlen($s)) as $pos)
            if (!$this->assertEqual( // after $o3; also cache is used
                $sc = Ac_StringObject::getStringObjectContext($s, $pos, true), 
                array(
                    'prev' => array(''.$o3, $beg3), 
                    'current' => FALSE, 
                    'next' => FALSE
                )
            )) var_dump($pos, $sc);
            
        $theFoo = ''.$o1;
        if (!$this->assertEqual( // before first: get next
            $sc = Ac_StringObject::getStringObjectContext($theFoo, -1), 
            array(
                'prev' => FALSE, 
                'current' => FALSE, 
                'next' => array(''.$o1, 0),
            )
        )) var_dump($sc);
        
        
    }
    
    function testEvaluatedStringObject() {
        // TODO
    }
    
    function testWrappers() {
        $o1 = new Ac_StringObject_Wrapper(new InnerObject('AAA'));
        $o2 = new Ac_StringObject_Wrapper(new InnerObject('BBB'));
        $s = "foo {$o1} bar {$o2}";
        $this->assertEqual(Ac_StringObject::replaceObjects($s, 'getData'), "foo AAA bar BBB");
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

class TestStringContainer implements Ac_I_StringObject_Container {
    
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

class InnerObject {

    var $data = null;
    
    function __construct($data) {
        $this->data = $data;
    }
    
    function getData() {
        return $this->data;
    }
    
}
