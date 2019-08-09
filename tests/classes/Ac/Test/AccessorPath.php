<?php

require_once('simpletest/mock_objects.php');

class Ac_Test_AccessorPath extends Ac_Test_Base {
    
    var $postArr = array(
        'foo' => 'fooPostValue',
        'bar' => array(
            'sub1' => 'Foo <script>bar \'); DROP DATABASE; \' </script>',
            'sub2' => 'val2',
            'sub3' => array('first', 'TRUE', 'FILE_NOT_FOUND')
        ),
    );
    
    function testParamValue() {
        
        $rq = new Ac_Request();
        
        $r = $rq->populate('http://example.com/some/long/path?foo=fooGetValue', '/index.php', $this->postArr);
        
        $v1 = new Ac_Accessor_Path($rq->get, 'foo');
        
        $v2 = new Ac_Accessor_Path($rq->get, 'foo', null, true);
        
        $this->assertEqual($v1->getValue(), 'fooGetValue', 
            'basic value getter');
        
        $v2->getValue();
        $rq->get->foo = 'bar';
        
        $this->assertEqual($v1->getValue(), 'bar', 
            'non-cached value is retrieved each time');
        
        $this->assertEqual($v2->getValue(), 'fooGetValue', 
            'cached value isn\'t changed');
        
        $this->assertEqual(
            $v = (string) Ac_Accessor_Path::chain($rq->value)->bar->sub1, 
            htmlspecialchars($this->postArr['bar']['sub1'], ENT_QUOTES),
            '__toString() returns HTML-escaped value'
        );
        
        $this->assertEqual(
            Ac_Accessor_Path::chain($rq->value)->bar->sub1->value(), 
            $this->postArr['bar']['sub1'],
            'value() returns unescaped value'
        );
        
        $this->assertEqual(
            Ac_Accessor_Path::chain($rq->value)->bar->sub3->value(),
            $this->postArr['bar']['sub3'],
            'return of array value'
        );
        
        $this->assertEqual(
            Ac_Accessor_Path::chain($rq->value)->bar->sub4->def(array('a' => 'xxx', 'b' => 'yyy'))->a->value(),
            'xxx',
            'retrieval of sub-value of default value'
        );
            
        $this->assertEqual(
            (string) Ac_Accessor_Path::chain($rq->value)->bar->sub3->sub4->sub5->sub6->def('10'),
            '10',
            'default value of non-existent parameter'
        );
        
        $this->expectException(false, 'Disabling of cache is not allowed for Ac_Accessor_Path that already has getValue() called');
        
        $v2->setCache(false);
        
   }
   
   function testParamRef() {
       
       Mock::generate('ParamSrc');
       $srcMock = new MockParamSrc;
       $srcMock->returns('getValue', 'val', array('path1'));
       $srcMock->returns('getValue', 'valGotByPath', array(array('path1')));
       $srcMock->returns('getValue', 'subVal', array('path1[sub]'));
       $srcMock->returns('getValue', 'subValGotByPath', array(array('path1', 'sub')));
       $srcMock->returns('getError', 'valError', array('path1'));
       
       
       $mm = array(
            'value' => 'getValue', 
            'error' => 'getError',
            'set' => 'setValue',
       );
       
       $refStr = new Ac_Accessor_PathRef($srcMock, 'path1', null, false, array(
           'methodMap' => $mm, 
           'cacheChildren' => true,
           'cacheableMethods' => array('error'),
           'passPathAsString' => true)
       );
       
       $this->assertEqual(
           $refStr->getMethodMap(), $mm,
           'getMethodMap() should return value set in $options[methodMap]'
       );
       
       $ref = new Ac_Accessor_PathRef($srcMock, 'path1', null, false, array(
           'methodMap' => $mm
       ));
       
       $srcMock->expectAt(0, 'getValue', array('path1'), 
           'getValue of Ac_Accessor_PathRef with passPathAsString should provide string path');
       
       $this->assertEqual(
           $v = $refStr->value(), 
           'val',
           'basic value retrieval (string path)'
       );
       
       $srcMock->expectAt(0, 'getValue', array(array('path1')), 
           'getValue without passPathAsString should provide array path');
       
       $this->assertEqual($v = $ref->value(), 'valGotByPath',
           'basic value retrieval (array path)');
       
       $srcMock->expectAt(1, 'getValue', array(array('path1', 'sub')),
           'sub-value retrieval (array path)');
       $sub = $ref->sub;
       
       $this->assertTrue($ref->sub !== $ref->sub, 
           'sub-path of PathRef without cacheChildren option should spawn new instances every time');
       
       $this->assertTrue($refStr->sub === $refStr->sub, 
           'sub-path of PathRef without cacheChildren option should return previously created instances');
       
       $this->assertEqual($sub->getPath(), array('path1', 'sub'), 
        'sub-path of spawned children should be appended to the spawner path');
       
       $srcMock->expectAt(2, 'getValue', array(array('path1', 'sub')),
           'spawned child of PathRef without passPathAsString should provide composite path as an array');
       
       $this->assertEqual($v = $ref->sub->value(), 'subValGotByPath');
       
       $srcMock->expectAt(3, 'getValue', array('path1[sub]'),
           'spawned child of PathRef with passPathAsString should provide composite path as a string'
       );
       
       $this->assertEqual($v = $refStr->sub->value(), 'subVal');
       
       $srcMock->expectCallCount('getValue', 4);
       
       $srcMock->expectAt(0, 'getError', array('path1'));
       
       $this->assertEqual($v = $refStr->error(), 'valError');
       
       $this->assertEqual($v = $refStr->error(), 'valError');
       
       $srcMock->expectCallCount('getError', 1, 'Cacheable method should be called only once');
       
       $srcMock->expectAt(0, 'setValue', array(array('path1', 'sub'), 'val'), 
           'Extra parameters of the method should be properly passed to src object');
       
       $srcMock->expectAt(1, 'setValue', array(array('path1', 'sub'), 'val'));
       $ref->sub->set('val');
       $ref->sub->set('val');
       
       $srcMock->expectCallCount('setValue', 2, 'Non-cacheable method is called every time');
   }
   
   function testDecorate() {
       
       $data = array(
           'foo' => array(10, 20, 'bogus', 30, 35, 10),
       );
       
       $dec = Ac_Decorator::factory(array(
           'class' => 'Ac_Param_Filter_Array', 
           'stripKeys' => true, 
           'toArray' => true,
           'conditions' => array(
               array(
                   'class' => 'Ac_Param_Condition_Enum', 
                   'values' => array(10, 20, 30)
               )
           )
      ));
       
       $val = Ac_Accessor_Path::chain($data)->foo->decorate($dec)->value();
       
       $this->assertEqual($val, array(10, 20, 30, 10));
       
       $val2 = Ac_Accessor_Path::chain($data)->onExistent->decorate($dec)->value();
       
       $this->assertEqual($val2, array());
       
   }
   
}

class ParamSrc {
    
    function getValue($path) {}
    
    function setValue($path, $value) {}
    
    function getError($path) {}
    
}