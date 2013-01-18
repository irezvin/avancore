<?php

class Ac_Test_Evaluated extends Ac_Test_Base {
    
    function testEvaluated() {
        $o = new ExampleContext;
        $o->values = array(
            'k1' => new ExampleEvaluated('1982-04-11'),
            'k2' => new ExampleEvaluated('1981-12-23'),
            'k3' => new ExampleEvaluated('2011-04-27')
        );
        $o->dateFormat = 'd.m.Y';
        $e = new ExampleEvaluator;
        $res = $e->evaluateContext($o);
        if (!$this->assertEqual(
            $res, 
            array(
                array('object' => $o->values['k1'], 'key' => 'k1', 'result' => '*0* /11.04.1982/'),
                array('object' => $o->values['k2'], 'key' => 'k2', 'result' => '*0* /23.12.1981/'),
                array('object' => $o->values['k3'], 'key' => 'k3', 'result' => '*0* /27.04.2011/'),
            ),
            'Objects are properly evaluated for the first time'
        )) var_dump($res);
        
        $o->isHtml = 1;
        $res = $e->evaluateContext($o);
        if (!$this->assertEqual(
            $res, 
            array(
                array('object' => $o->values['k1'], 'key' => 'k1', 'result' => '<b>1</b> <em>11.04.1982</em>'),
                array('object' => $o->values['k2'], 'key' => 'k2', 'result' => '<b>1</b> <em>23.12.1981</em>'),
                array('object' => $o->values['k3'], 'key' => 'k3', 'result' => '<b>1</b> <em>27.04.2011</em>'),
            ),
            'Objects are properly re-evaluated for the second time with altered $context state'
        )) var_dump($res);
    }
    
    function testSuggestions() {
        $o = new ExampleContext;
        $o->values = array(
            'k1' => new ExampleEvaluated('1982-04-11'),
            'k2' => new ExampleEvaluated('1981-12-23'),
            'k3' => new ExampleEvaluated2('2011-04-27')
        );
        $o->dateFormat = 'd.m.Y';
        $e = new ExampleEvaluator;
        $res = $e->evaluateContext($o);
        if (!$this->assertEqual(
            $res, 
            array(
                array('object' => $o->values['k1'], 'key' => 'k1', 'result' => '*0* /11.04.1982/'),
                array('object' => $o->values['k2'], 'key' => 'k2', 'result' => '*0* /23.12.1981/'),
                array('object' => $o->values['k3'], 'key' => 'k3', 'result' => '=== 1 27.04.2011 ==='),
            ),
            'One of object suggests another Evaluator which triggers extra execution pass'
        )) var_dump($res);
        
    }
    
    function testMutableContext() {
        $o = new ExampleContextMutable;
        
        $v1 = new ExampleEvaluated('1982-04-11');
        
        $v2 = new ExampleEvaluated3('1981-12-23');
        $v3 = new ExampleEvaluated('2011-04-27');
        $v2->add = $v3;
        
        $o->dateFormat = 'd.m.Y';
        
        $o->addValue($v1);
        $o->addValue($v2);
        
        $ev = new ExampleEvaluator();
        $res = $ev->evaluateContext($o);
        
        if (!$this->assertEqual(
            $res, 
            array(
                array('object' => $v1, 'key' => 0, 'result' => '*0* /11.04.1982/'),
                array('object' => $v2, 'key' => 1, 'result' => '=== 1 23.12.1981 ==='),
                array('object' => $v3, 'key' => 2, 'result' => '*2* /27.04.2011/'),
            ),
            'Second object changes context by adding third object, which triggers extra evaluation step'
        )) var_dump($res);
    }
    
    function testCacheContext() {
        
        $o = new ExampleContextCache();
        
        $o->values = array(
            'k1' => new ExampleEvaluated('1982-04-11'),
            'k2' => new ExampleEvaluated('1981-12-23'),
        );
        $o->dateFormat = 'd.m.Y';
        $e = new ExampleEvaluator;
        $res = $e->evaluateContext($o);
        
        if (!$this->assertEqual(
            $res, 
            $foo = array(
                array('object' => $o->values['k1'], 'key' => 'k1', 'result' => '*0* /11.04.1982/'),
                array('object' => $o->values['k2'], 'key' => 'k2', 'result' => '*0* /23.12.1981/'),
            )
        )) var_dump($res);

        // second pass
        
        $res = $e->evaluateContext($o);
        if (!$this->assertEqual(
            $res, 
            $foo,
            'Same values are returned on second pass'
        )) var_dump($res);
        
        $o->isHtml = 1;
        $res = $e->evaluateContext($o);
        if (!$this->assertEqual(
            $res, 
            $bar = array(
                array('object' => $o->values['k1'], 'key' => 'k1', 'result' => '<b>1</b> <em>11.04.1982</em>'),
                array('object' => $o->values['k2'], 'key' => 'k2', 'result' => '<b>1</b> <em>23.12.1981</em>'),
            ),
            'Objects are re-evaluated when context is altered'
        )) var_dump($res);

        $o->isHtml = false;
        $o->values['k3'] = new ExampleEvaluated('2011-04-27');
        
        $res = $e->evaluateContext($o);
        if (!$this->assertEqual(
            $res, 
            $baz = array(
                array('object' => $o->values['k1'], 'key' => 'k1', 'result' => '*0* /11.04.1982/'),
                array('object' => $o->values['k2'], 'key' => 'k2', 'result' => '*0* /23.12.1981/'),
                array('object' => $o->values['k3'], 'key' => 'k3', 'result' => '*2* /27.04.2011/'),
            ),
            'Only extra object is re-evaluated, old ones are taken from the cache'
        )) var_dump($res);
        
        $res = $e->evaluateContext($o);
        if (!$this->assertEqual(
            $res, 
            $baz,
            'Nothing changes on extra call; only cached values are returned'
        )) var_dump($res);

    }
    
}


class ExampleEvaluated implements Ac_I_EvaluatedObject {
    
    var $date = false;
    var $cc = 0;
    
    function __construct($date = false) {
        $this->date = $date;
        if (is_string($this->date)) $this->date = strtotime($this->date);
    }
    
    function evaluateDefault($dateFormat) {
        $this->cc++;
        return $this->date === false? date($dateFormat) : date($dateFormat, $this->date);
    }
    
}

class ExampleEvaluated2 extends ExampleEvaluated implements Ac_I_EvaluatedObject_WithSuggestion {
    
    function suggestEvaluatorPrototype(Ac_Evaluator $basicEvaluator) {
        if ($basicEvaluator instanceof ExampleEvaluator) {
            return array('class' => 'ExampleEvaluator2');
        }
    }
    
}

class ExampleEvaluated3 extends ExampleEvaluated2 {
    
    var $add = null;
    
    function suggestEvaluatorPrototype(Ac_Evaluator $basicEvaluator) {
        if ($basicEvaluator instanceof ExampleEvaluator) {
            return array('class' => 'ExampleEvaluator3');
        }
    }
    
}

class ExampleContext implements Ac_I_EvaluationContext {
        
    var $values = array();
    
    var $foo = 0;
    
    var $isHtml = false;
    
    var $dateFormat = 'Y-m-d H:i:s';    
    
    function getFoo() {
        return $this->foo++;
    }
    
    function getEvaluatedObjects() {
        return $this->values;
    }
}

class ExampleEvaluator extends Ac_Evaluator {
    
    protected $supportedClasses = array('ExampleEvaluated');
    
    function doGetEvaluationResults(array $objects, Ac_I_EvaluationContext $context = null) {
        $foo = $context->getFoo();
        $res = array();
        foreach ($objects as $k => $v) {
            $ev = $v->evaluateDefault($context->dateFormat);
            $res[$k] = $context->isHtml? "<b>{$foo}</b> <em>$ev</em>"  : "*{$foo}* /{$ev}/";
        }
        return $res;
    }
    
}

class ExampleEvaluator2 extends ExampleEvaluator {
    
    function doGetEvaluationResults(array $objects, Ac_I_EvaluationContext $context = null) {
        $foo = $context->getFoo();
        $res = array();
        foreach ($objects as $k => $v) {
            $ev = $v->evaluateDefault($context->dateFormat);
            $res[$k] = $context->isHtml? "<h1>{$foo} {$ev}</h1>" : "=== {$foo} {$ev} ===";
        }
        return $res;
    }
    
}

class ExampleContextMutable extends ExampleContext implements Ac_I_EvaluationContext_Mutable {
        
    protected $added = array();
    
    function addValue(ExampleEvaluated $value) {
        $this->values[] = $value;
        $k = max(array_keys($this->values));
        if (count($this->added)) 
            foreach ($this->added as & $v) {
                $v['values'][$k] = $value;
            }
    }
    
    function notifyEvaluationBegin(Ac_Evaluator $evaluator) {
        $this->added[] = array('evaluator' => $evaluator, 'values' => array());
    }
    
    function notifyEvaluationEnd(Ac_Evaluator $evaluator, &$newObjects) {
        foreach (array_reverse(array_keys($this->added)) as $k) {
            if ($this->added[$k]['evaluator'] == $evaluator) {
                $newObjects = $this->added[$k]['values'];
                unset($this->added[$k]);
            }
        }
    }
    
}

class ExampleContextCache extends ExampleContext implements Ac_I_EvaluationContext_WithCache {
        
    var $cache = array();
    
    function getEvaluationGroupData(Ac_Evaluator $evaluator) {
        $res = Ac_Accessor::getObjectProperty($this, array('isHtml', 'dateFormat'));
        $res['ev'] = $evaluator->getCacheGroupData();
        return $res;
    }
    
    function getEvaluationResults($groupId, $keys = null) {
        if (!isset($this->cache[$groupId])) return array();
        else return array_intersect_key ($this->cache[$groupId], array_flip($keys));
    }
    
    function setEvaluationResults($groupId, array $data, $replace = false) {
        if (!isset($this->cache[$groupId]) || $replace) $this->cache[$groupId] = array();
        foreach ($data as $k => $v) $this->cache[$groupId][$k] = $v;
    }
    
}


class ExampleEvaluator3 extends ExampleEvaluator {
    
    protected $supportedClasses = array('ExampleEvaluated');
    
    function doGetEvaluationResults(array $objects, Ac_I_EvaluationContext $context = null) {
        $foo = $context->getFoo();
        $res = array();
        foreach ($objects as $k => $v) {
            if (isset($v->add) && is_object($v->add) && $v->add instanceof ExampleEvaluated) $context->addValue($v->add);
            $ev = $v->evaluateDefault($context->dateFormat);
            $res[$k] = $context->isHtml? "<h1>{$foo} {$ev}</h1>" : "=== {$foo} {$ev} ===";
        }
        return $res;
    }
    
}

