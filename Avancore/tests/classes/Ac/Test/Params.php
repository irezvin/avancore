<?php

class Ac_Test_Params extends Ac_Test_Base {
    
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
        
        $v1 = new Ac_Param_Value($rq->get, 'foo');
        
        $v2 = new Ac_Param_Value($rq->get, 'foo', null, true);
        
        $this->assertEqual($v1->getValue(), 'fooGetValue', 
            'basic value getter');
        
        $v2->getValue();
        $rq->get->foo = 'bar';
        
        $this->assertEqual($v1->getValue(), 'bar', 
            'non-cached value is retrieved each time');
        
        $this->assertEqual($v2->getValue(), 'fooGetValue', 
            'cached value isn\'t changed');
        
        $this->assertEqual(
            $v = (string) Ac_Param_Value::chain($rq->value)->bar->sub1, 
            htmlspecialchars($this->postArr['bar']['sub1'], ENT_QUOTES),
            '__toString() returns HTML-escaped value'
        );
        
        $this->assertEqual(
            Ac_Param_Value::chain($rq->value)->bar->sub1->value(), 
            $this->postArr['bar']['sub1'],
            'value() returns unescaped value'
        );
        
        $this->assertEqual(
            Ac_Param_Value::chain($rq->value)->bar->sub3->value(),
            $this->postArr['bar']['sub3'],
            'return of array value'
        );
        
        $this->assertEqual(
            Ac_Param_Value::chain($rq->value)->bar->sub4->def(array('a' => 'xxx', 'b' => 'yyy'))->a->value(),
            'xxx',
            'retrieval of sub-value of default value'
        );
            
        $this->assertEqual(
            (string) Ac_Param_Value::chain($rq->value)->bar->sub3->sub4->sub5->sub6->def('10'),
            '10',
            'default value of non-existent parameter'
        );
        
        $this->expectException(false, 'Disabling of cache is not allowed for Ac_Param_Value that already has getValue() called');
        
        $v2->setCache(false);
        
   }
    
}