<?php

class Ac_Test_Result extends Ac_Test_Base {
    
    function testBasicResult() {
        
        $outer = new Ac_Result();
        $outer->put($begin = "Begin ");
        
        $inner = new Ac_Result();
        $inner->put("MiddleContent");
        
        $outer->put($inner);
        
        $outer->put($end = " End ");
        
        
        $this->assertEqual($outer->getContent(), $begin.$inner->getStringObjectMark().$end);
        $this->assertEqual($outer->getSubResults(), array('content' => array($inner->getStringObjectMark() => $inner)));
        $this->assertTrue(in_array($inner, $outer->getStringObjects()));
        
        $suffix = new Ac_Result();
        $suffix->put("SuffixContent");
        
        $outer->beginCapture();
        echo ($suffix);
        echo ($after = " After Suffix");
        $outer->endCapture();
        
        $this->assertEqual($outer->getContent(), $begin.$inner->getStringObjectMark().$end.$suffix->getStringObjectMark().$after);
        $this->assertEqual($outer->getSubResults(), array(
            'content' => array(
                $inner->getStringObjectMark() => $inner,
                $suffix->getStringObjectMark() => $suffix,
            )
        ));
        $this->assertTrue(in_array($suffix, $outer->getStringObjects()));
        
    }
    
    function testStageIterator() {
        $a = new Ac_Result(); $a->setDebugData('a');
        $a_1 = new Ac_Result();  $a_1->setDebugData('a_1');
        $a_1_1 = new Ac_Result(); $a_1_1->setDebugData('a_1_1');
        $a_1_2 = new Ac_Result(); $a_1_2->setDebugData('a_1_2');
        $a_2 = new Ac_Result(); $a_2->setDebugData('a_2');
        
        $a->put($a_1);
        
        $someRandomStringObject = new Ac_StringObject_Wrapper(new stdClass());
        $a->put($someRandomStringObject);
        
        $a->put($a_2);
        
        $a_1->put($a_1_1);
        $a_1->put($a_1_2);
        
        $stage = new StageIterator();
        $stage->setRoot($a);
        
        $i = 0;
        $stage->resetTraversal();
        $current = $stage->getCurrentResult();
        $repeat = 0;
        $was = array();
        do {
            $r = $stage->traverseNext();
            //if ($r) var_dump($stage->getCurrentResult()->getDebugData ().' '. $stage->getCurrentProperty().' '.$stage->getCurrentOffset().' '.$stage->getCurrentPropertyIsString().' '.$stage->getIsChangeable());
            if ($r && $r instanceof Ac_Result && $r->getDebugData() == 'a_1' && $stage->getIsAscend()) {
                $stage->put("After");
                $stage->put(" a_1");
            }
            if ($stage->getCurrentResult()) $was[] = $stage->getCurrentResult()->getDebugData();
        } while ($r && $i++ < 20);
        
        if (!$this->assertEqual(count($was), count(array_unique($was)), "Items should not be visited twice")) {
            var_dump($was);
        }
        
        $this->assertEqual($repeat, 0);
        
        if (!$this->assertEqual($stage->travLog, array(
                'a beginStage', 
                'a beforeChild a_1', 
                'a_1 beginStage', 
                'a_1 beforeChild a_1_1', 
                'a_1_1 beginStage', 
                'a_1_1 endStage', 
                'a_1 afterChild a_1_1', 
                'a_1 beforeChild a_1_2', 
                'a_1_2 beginStage', 
                'a_1_2 endStage', 
                'a_1 afterChild a_1_2', 
                'a_1 endStage', 
                'a afterChild a_1', 
                'a beforeChild a_2',
                'a_2 beginStage', 
                'a_2 endStage',
                'a afterChild a_2', 
                'a endStage', 
        )))         
        var_dump($stage->travLog);
    }
    
    function testStageModify() {
        
        $a = new Ac_Result(); 
        $a->setDebugData('a');
        
        $a_1 = new Ac_Result();  
        $a_1->setDebugData('<a_1>');
        
        $a_2 = new Ac_Result();  
        $a_2->setDebugData('<a_2>');
        
        $a_3 = new Ac_Result();  
        $a_3->setDebugData('<a_3>');
        
        $a_2_replacement = new Ac_Result();
        $a_2_replacement->setDebugData('<a_2_replacement>');
        
        $a->put("a_1: {$a_1} a_2: {$a_2}: a_3 {$a_3}");
        
        $stage = new StageIterator();
        $stage->setRoot($a);
        
        $i = 0;
        $stage->resetTraversal();
        $current = $stage->getCurrentResult();
        $repeat = 0;
        $was = array();
        $wasAtNewA = false;
        do {
            $r = $stage->traverseNext();
            if ($r === $a_1) {
                $stage->put(" (after");
                $stage->put(" a_1)");
            }
            if ($r === $a_2) {
                $stage->replaceCurrentObject($a_2_replacement);
            }
            if ($r === $a_2_replacement) $wasAtNewA = true;
            if ($stage->getCurrentResult()) $was[] = $stage->getCurrentResult()->getDebugData();
        } while ($r && $i++ < 20);
        
        $this->assertFalse($wasAtNewA);
        
        $this->assertEqual($a->getContent(), "a_1: {$a_1} (after a_1) a_2: {$a_2_replacement}: a_3 {$a_3}");
    }

    function mkBunchResult() {
        $r = new BunchResult();
        $r->bunch = array(
            'first' => array(
                'foo1' => new FooResult(array('debugData' => 'foo1')),
                'bar1' => new BarResult(array('debugData' => 'bar1')),
                'res1' => new Ac_Result(array('debugData' => 'res1')),
            ),
            'second' => array(
                'foo2' => new FooResult(array('debugData' => 'foo2')),
                'foo3' => new FooResult(array('debugData' => 'foo3')),
            ),
        );
        
        $r->put('111');
        $r->put(new FooResult(array('debugData' => 'foo0.1')));
        $r->put('222');
        $r->put(new BarResult(array('debugData' => 'bar0.1')));
        $r->put('333');
        $r->put(new FooResult(array('debugData' => 'foo0.2')));
        $r->put('444');
        return $r;
    }
    
    function advanceAndLog(Ac_Result_Stage_Position $p, $max = false, array & $aLog = array(), array & $bLog = array()) {
        $i = 0;
        if ($max === false) 
            $max = count(Ac_Util::flattenArray($p->getResult()->getTraversableBunch())) + 10;
        while (($i++ < $max) && ($res = $p->advance())) {
            $aLog[] = implode(' ', $p->getPosition()).' '.$res->getDebugData();
            $pos = $p->getPosition();
            $bLog[] = $pos[0].' '.$res->getDebugData();
        }
        return array($aLog, $bLog);
    }
    
    function testStagePosition() {
        $b1 = $this->mkBunchResult();
        
        $this->assertEqual(array_keys($b1->getTraversableBunch()), array(
            'content', 'first', 'second'
        ));
        $this->assertEqual(array_keys($b1->getTraversableBunch('BarResult')), array(
            'content', 'first'
        ));
        
        $p = new Ac_Result_Stage_Position($b1);
        list($aLog, $bLog) = $this->advanceAndLog($p);
        if (!$this->assertEqual($bLog, array(
            'content foo0.1',
            'content bar0.1',
            'content foo0.2',
            'first foo1',
            'first bar1',
            'first res1',
            'second foo2',
            'second foo3',
        ))) var_dump($bLog, $aLog);
        $this->assertTrue($p->getIsDone());
        $this->assertNull($p->getObject());
        
        $p2 = new Ac_Result_Stage_Position($b1, 'BarResult');
        $foo = array();
        $bar = array();
        list($aLog, $bLog) = $this->advanceAndLog($p2);
        if (!$this->assertEqual($bLog, array(
            'content bar0.1',
            'first bar1',
        ))) var_dump($bLog, $aLog);
        
        $p3 = new Ac_Result_Stage_Position($b1, array('FooResult', 'BarResult'));
        list($aLog, $bLog) = $this->advanceAndLog($p3);
        if (!$this->assertEqual($bLog, array(
            'content foo0.1',
            'content bar0.1',
            'content foo0.2',
            'first foo1',
            'first bar1',
            'second foo2',
            'second foo3',
        ))) var_dump($bLog, $aLog);
        
    }
    
    function getLast(array $foo) {
        return array_pop($foo);
    }
    
    function testStagePositionExtMorph() {
        $b1 = $this->mkBunchResult();
        
        $p = new Ac_Result_Stage_Position($b1);
        
        $aLog = array();
        $bLog = array();

        $this->advanceAndLog($p, 1, $aLog, $bLog);
        $this->assertEqual($this->getLast($bLog), 'content foo0.1');
        $b1->insertAtPosition(0, $s = 'Lengthy and very interesting text here 123 456 789');
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'content bar0.1')) var_dump($bLog);
        
        $newNext = new BarResult(array('debugData' => 'I am The Next'));
        $pos = $p->getPosition();
        $newPos = $pos[1] + strlen($p->getObject());
        $b1->insertAtPosition($newPos, $newNext);
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'content I am The Next')) var_dump($bLog);
        
        $b1->removeFromPosition(0, strlen($s));
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'content foo0.2')) var_dump($bLog);
        
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'first foo1')) var_dump($bLog);
        $b1->bunch['first'] = array_merge(array('veryFirst' => new BarResult(array('debugData' => 'veryFirst'))), $b1->bunch['first']);
        $b1->touchStringObjects();
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'first bar1')) var_dump($bLog);
        array_splice($b1->bunch['first'], 3, 0, array('theNext' => new FooResult(array('debugData' => 'next'))));
        $b1->touchStringObjects();
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'first next')) var_dump($bLog);
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'first res1')) var_dump($bLog);
    }
    
        
    function testStagePositionExtDrop() {
        $b1 = $this->mkBunchResult();
        
        $p = new Ac_Result_Stage_Position($b1);
        
        $aLog = array();
        $bLog = array();

        $this->advanceAndLog($p, 1, $aLog, $bLog);
        $this->assertEqual($this->getLast($bLog), 'content foo0.1');
        $b1->removeFromContent($ob = $p->getObject());
        $this->assertFalse(strpos($b1->getContent(), ''.$ob));
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        $this->assertEqual($this->getLast($bLog), 'content bar0.1');
        $p->gotoPosition('first', false);
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        $this->assertEqual($this->getLast($bLog), 'first foo1');
        unset($b1->bunch['first']['foo1']);
        $b1->touchStringObjects();
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        $this->assertEqual($this->getLast($bLog), 'first bar1');
    }

    
    function testStagePositionIntMorph() {
        $b1 = $this->mkBunchResult();
        
        $p = new Ac_Result_Stage_Position($b1);
        
        $aLog = array();
        $bLog = array();

        $this->advanceAndLog($p, 1, $aLog, $bLog);
        $this->assertEqual($this->getLast($bLog), 'content foo0.1');
        $p->insertBefore($s = 'Lengthy and very interesting text here 123 456 789');
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'content bar0.1')) var_dump($bLog);
        
        $newNext = new BarResult(array('debugData' => 'I am The Next'));
        $pos = $p->getPosition();
        $p->insertAfter('<<QQQ ', true);
        $p->insertAfter(' RRR>>', true);
        $p->insertAfter($newNext, true);
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'content I am The Next')) var_dump($bLog);
        $p->insertBefore('{Some text before}', true);
        $p->insertAfter('{Some text}', true);
        $this->assertTrue(strpos($b1->getContent(), "<<QQQ  RRR>>{Some text before}$newNext{Some text}"));
        
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'content foo0.2')) var_dump($bLog);
        
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'first foo1')) var_dump($bLog);
        $p->insertBefore(new BarResult(array('debugData' => 'veryFirst')));
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'first bar1')) var_dump($bLog);
        $p->insertAfter(new BarResult(array('debugData' => 'next')));
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'first next')) var_dump($bLog);
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'first res1')) var_dump($bLog);
    }
    
        
    function testStagePositionIntDrop() {
        $b1 = $this->mkBunchResult();
        
        $p = new Ac_Result_Stage_Position($b1);
        
        $aLog = array();
        $bLog = array();

        $this->advanceAndLog($p, 1, $aLog, $bLog);
        $this->assertEqual($this->getLast($bLog), 'content foo0.1');
        $ob = $p->getObject();
        $p->removeCurrentObject();
        $this->assertFalse(strpos($b1->getContent(), ''.$ob));
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        $this->assertEqual($this->getLast($bLog), 'content bar0.1');
        $p->gotoPosition('first', false);
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        $this->assertEqual($this->getLast($bLog), 'first foo1');
        $p->replaceCurrentObject(new BarResult(array('debugData' => 'somethingHere')));
        $this->advanceAndLog($p, 1, $aLog, $bLog);
        if (!$this->assertEqual($this->getLast($bLog), 'first bar1')) var_dump($bLog);
    }

    function _testStageIteratorAdvanced() { // TODO
        $log = array();
        $inner = new Ac_Result(array(
            'debugData' => 'inner',
            'content' => '<inner>'.AllHandler::so('innerHandler', $log).'</inner>',
        ));
        $outer = new Ac_Result(array(
            'debugData' => 'outer',
            'content' => 
                '<outer>'.AllHandler::so('outerHandler1', $log).$inner.AllHandler::so('outerHandler2').'</outer>'
        ));
        $stage = new StageIterator(array('root' => $outer));
        
    }
    
}

class AllHandler implements Ac_I_Result_Handler_All {

    static $log = array();
    
    var $myLog = null;
    
    var $name = false;
    
    static function so($name, & $log = null) {
        return new Ac_StringObject_Wrapper(new self($name, $log));
    }
    
    function __construct($name, & $log = null) {
        $this->name = $name;
        if (!is_null($log)) $this->myLog = & $log;
            else $this->myLog = & self::$log;
    }
    
    function handleDefault($event, $stage, $result) {
        $aa = func_get_args();
        array_unshift($this->name, $a);
        foreach ($aa as $k => $v) 
            if (is_object($v) && $v instanceof Ac_Result) $aa[$k] = $v->getDebugData();
        $this->log[] = implode('; ', $aa);
    }
    
}

class StageIterator extends Ac_Result_Stage {
    
    var $travLog = array();
    
    var $defaultTraverseClasses = array('Ac_Result', 'Ac_I_StringObject');
    
    function traverse($classes = null) {
        return parent::traverse($classes);
    }
    
    function resetTraversal($classes = null) {
        $this->travLog = array();
        return parent::resetTraversal($classes);
    }
    
    function traverseNext() {
        return parent::traverseNext();
    }
    
    function invokeHandlers(Ac_Result $result = null, $stageName, $args = null) {
        $args = func_get_args();
        if ($result) {
            $aa = $args;
            foreach ($aa as $k => $v) 
                if (is_object($v) && $v instanceof Ac_Result) $aa[$k] = $v->getDebugData();
            $this->travLog[] = implode(' ',$aa);
        }
        return call_user_func_array(array('Ac_Result_Stage', 'invokeHandlers'), $args);
    }
    
    function getIsAscend() {
        return $this->isAscend;
    }
    
}

class BunchResult extends Ac_Result {
    
    var $bunch = array();

    function touchStringObjects() {
        parent::touchStringObjects();
    }
    
    protected function doGetTraversableBunch($classes = false) {
        if ($classes !== false) {
            $res = Ac_Util::getObjectsOfClass($this->bunch, $classes);
        } else {
            $res = $this->bunch;
        }
        return $res;
    }
    
    function addToList($property, $object, $position) {
        array_splice($this->bunch[$property], $position, 0, array($object));
        $this->touchStringObjects();
    }
    
    function removeFromList($property, $object) {
        $k = array_search($object, array_values($this->bunch[$property]), true);
        if ($k !== false) {
            array_splice($this->bunch[$property], $k, 1);
        }
        $this->touchStringObjects();
    }
    
}

class FooResult extends Ac_Result {
}

class BarResult extends Ac_Result {
}