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
        $a->put($a_2);
        
        $a_1->put($a_1_1);
        $a_1->put($a_1_2);
        
        $stage = new StageIterator();
        $stage->setRoot($a);
        
        $i = 0;
        $stage->resetTraversal();
        $current = $stage->getCurrent();
        $repeat = 0;
        $was = array();
        do {
            $r = $stage->traverseNext();
            if ($stage->getCurrent()) $was[] = $stage->getCurrent()->getDebugData().':'.$stage->getIsAscend();
        } while ($r && $i++ < 20);
        
        $this->assertEqual(count($was), count(array_unique($was)), "Traversal items should not repeat");
        
        $this->assertEqual($repeat, 0);
        
        $this->assertSame($stage->getFirstChild($a), $a_1);
        
        $this->assertSame($stage->getFirstChild($a_1), $a_1_1);
        
        $this->assertSame($stage->getNextSibling($a_1, $a), $a_2);

        $this->assertEqual($stage->getNextSibling($a_2, $a), null);
        
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
    
    function testStageIteratorAdvanced() {
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
    
    function traverse() {
        return parent::traverse();
    }
    
    function resetTraversal() {
        $this->travLog = array();
        return parent::resetTraversal();
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
    
    function getFirstChild(Ac_Result $item) {
        return parent::getFirstChild($item);
    }
    
    function getNextSibling(Ac_Result $item, Ac_Result $parent = null) {
        return parent::getNextSibling($item, $parent);
    }
    
    function getIsAscend() {
        return $this->isAscend;
    }
    
}