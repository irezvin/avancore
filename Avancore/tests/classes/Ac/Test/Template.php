<?php

require(dirname(__FILE__).'/assets/Template_classes.php');

class Ac_Test_Template extends Ac_Test_Base {

    
    function testResultTemplateWithInnerResult() {
        
        $t = new TestTemplate1;
        
        $r = new Ac_Result_Html;
        
        $r->setDebugData("inner");
        
        $r->title = 'Foo';
        
        $r->assets->addItems(array('foo.js', 'foo.css'));
        
        $r->content = "<h1>Bar</h1>\n<p>baz</p>";
        
        $templateResult = new Ac_Result_Template();
        
        $templateResult->setTemplateInstance($t);
        
        $templateResult->setPartName('innerResult');
        
        $templateResult->setPartArgs(array('result' => $r, 'prefix' => "<p>Before</p>\n", 'suffix' => "\n<p>After</p>"));
        
        $templateResult->setDebugData("template");
        
        $outerResult = new Ac_Result_Html();

        //$templateResult = $templateResult->render();
        
        //$templateResult = "<p>Before</p>\n{$r}\n<p>After</p>";
        
        $outerResult->put("<p>Outer before</p>\n", $templateResult, "\n<p>Outer after</p>");
        
        $outerResult->setDebugData("outer");
      
        $f = function($result, Ac_Result_Stage $stage, $callbackType) {
            $stack = $stage->getStack();
            $r = "\n".str_repeat(" - ", count($stack));
            $r .= $callbackType." ";
            if (!$stack) $r .= "root";
            else {
                $curr = $stack[0];
                $offset = $curr[0].":".$curr[1];
                $r .= $offset;
            }
            $r .= ":".get_class($result);
            if (strlen($dd = $result->getDebugData())) {
                $r .= "[".$dd."]";
            }
            if ($result instanceof Ac_Result) $r .= json_encode($result->getContent(), JSON_UNESCAPED_UNICODE);
            echo $r;
        };
        
        $cb = new Ac_Result_Stage_Write(array(
            'beginItemCallback' => $f,
            'endItemCallback' => $f
        ));
        
        //ob_start();
        //$cb->setRoot($outerResult);
        //$cb->invoke();
        //echo("<pre>".nl2br(htmlspecialchars(ob_get_clean()))."</pre>");
            
        $e = new Ac_Result_Environment_Dummy();
        Ac_Result_Environment::setDefault($e);
        
        $writer = new Ac_Result_Writer_RenderHtml;
        $writer->setEnvironment($e);
        $writer->writeResult($outerResult);
        
        if (!$this->assertEqual($this->normalizeHtml($e->responseText), $this->normalizeHtml('
            <!DOCTYPE html>
            <html>
            <head>
            <title>Foo</title>
            <link rel="stylesheet" type="text/css" href="foo.css" />
            <script type="text/javascript" src="foo.js"> </script>
            </head>
            <body>
                <p>Outer before</p>
                    <p>Before</p>
                        <h1>Bar</h1>
                        <p>baz</p>
                    <p>After</p>
                <p>Outer after</p>
            </body>
            </html>
        '))) {
            var_dump($e->responseText);
        }
        
    }    
    
    function testInternals() {

        $t1 = new TestTemplate1;
        //var_dump($t1->getSignature('partSomeObjects'));
        //var_dump($t1->getSignature('partArray'));
        $missing = array();
        $e = null;
        try {
            var_dump($t1->getArgs($t1, 'partObjects', array('foo' => 'bar'), $missing));
        } catch (Ac_E_Template $e) {
        }
        $this->assertNotNull($e);
        
        $this->assertEqual(
            $t1->getArgs($t1, 'partObjects', $args = array('object1' => new TestObject1_1, 'object2' => new TestObject1_2), $missing), 
            array_values($args)
        );
        $this->assertEqual($missing, array());
        
        $t1->setField('object1', $object1 = new TestObject1_1);
        $object2 = new TestObject1_2;
        if (!$this->assertEqual(
            $a = $t1->getArgs($t1, 'partObjects', $args = array('object2' => $object2), $missing), 
            array($object1, $object2)
        )) var_dump($a);
        $this->assertEqual($missing, array());
        
        $t1->deleteField('object1');
        $this->assertEqual(
            $t1->getArgs($t1, 'partSomeObjects', $args = array(), $missing), 
            array(1 => null)
        );
        $this->assertEqual($missing, array('object1'));
        
        $t1->object1 = $object1 = new TestObject1_1;
        $t1->object2 = $object2 = new TestObject1_2;
        $t1->foo = array();
        $this->assertEqual(
            $t1->getArgs($t1, 'partObjects', array(), $missing), 
            array($object1, $object2)
        );
        $this->assertEqual($missing, array());
        $this->assertEqual(
            $t1->getArgs($t1, 'partArray', array(), $missing), 
            array(array())
        );
        $this->assertEqual($missing, array());
        
    }
    
    function testTpl() {
        
        $cVals = array(
            'var1' => 'var1value',
            'var2' => 'var2value',
            'object1' => new TestObject1_1('testObject1'),
            'object2' => new TestObject1_2('testObject2'),
        );
        
        $c = new TestComponent ($cVals);
        $t1 = new TestTemplate1;
        
        $t1->setComponent($c);

        $r = $t1->renderResult('part1');
        $this->assertIsA($r, 'Ac_Result_Html');
        if (!$this->assertEqual(
            $this->normalizeHtml($c = $r->getContent()), 
            $this->normalizeHtml("
                Part1:
                var1 = {$cVals['var1']}
                var2 = {$cVals['var2']}
            ")
        )) var_dump($c);
        $this->assertEqual(count($t1->getStack()), 0);
        $r = $t1->renderResult('objects');
        
        $this->assertIsA($r, 'Ac_Result_Html');
        if (!$this->assertEqual(
            $this->normalizeHtml($c = $r->getContent()), 
            $this->normalizeHtml("
                Objects:
                object1 = {$cVals['object1']}
                object2 = {$cVals['object2']}
            ")
        )) var_dump($c);
        $this->assertEqual(count($t1->getStack()), 0);
                
        $r = $t1->renderResult('withWrapper1');
        if (!$this->assertEqual(
            $this->normalizeHtml($c = $r->getContent()), 
            $this->normalizeHtml("
                Begin wrapper1
                Text of part with wrapper1
                End wrapper1
            ")
        )) var_dump($c);
        $this->assertEqual(count($t1->getStack()), 0);
        
        $t1->setWrapTopLevel('wrapper1');
        
        $r = $t1->renderResult('objects');
        if (!$this->assertEqual(
            $this->normalizeHtml($c = $r->getContent()), 
            $this->normalizeHtml("
                Begin wrapper1
                Objects:
                object1 = {$cVals['object1']}
                object2 = {$cVals['object2']}
                End wrapper1
            ")
        )) var_dump($c);
        $this->assertEqual(count($t1->getStack()), 0);
        
        $r = $t1->renderResult('withoutWrapper');
        if (!$this->assertEqual(
            $this->normalizeHtml($c = $r->getContent()), 
            $this->normalizeHtml("
                Text of part without wrapper
            ")
        )) var_dump($c);
        $this->assertEqual(count($t1->getStack()), 0);
        
        $r = $t1->renderResult('withNesting');
        if (!$this->assertEqual(
            $this->normalizeHtml($c = $r->getContent()), 
            $this->normalizeHtml("
                Begin wrapper2
                
                Part with nesting:
                
                Begin wrapper1
                Text of part with wrapper1
                End wrapper1
                
                Text of part without wrapper
                
                Objects:
                object1 = {$cVals['object1']}
                object2 = {$cVals['object2']}

                End wrapper2
            ")
        )) var_dump($c);
        $this->assertEqual(count($t1->getStack()), 0);
        
        $r = new Ac_Result_Html();
        $r->put("Some text before\n");
        $t1->setDefaultWrapper(false);
        $t1->setWrapTopLevel(false);
        $t1->renderTo($r, 'objects', array('object2' => ($o2 = new TestObject1_2('1_2'))));
        if (!$this->assertEqual(
            $this->normalizeHtml($c = $r->getContent()), 
            $this->normalizeHtml("
                Some text before
                Objects:
                object1 = {$cVals['object1']}
                object2 = {$o2}
            ")
        )) var_dump($c);
        
    }
    
    function testTemplateResult() {
        
        $t = new TestTemplate1;
        
        $c = new TestComponent;
        
        $tplRes = new Ac_Result_Template(array(
            'template' => $t,
            'component' => $c,
            'partName' => 'part1',
            'renderedResultWriter' => 'Ac_Result_Writer_Plain'
        ));
        
        $r = new Ac_Result(array("content" => " 
            Outer result
            {$tplRes}
            /Outer result
        "));
            
        $w = $r->writeAndReturn();
        
        if (!$this->assertEqual(
            $this->normalizeHtml($w),
            $this->normalizeHtml("
               Outer result
               Part1:
               var1 = val1
               var2 = val2
               /Outer result
        "))) var_dump($w);

        $w = $tplRes->writeAndReturn();
        if (!$this->assertEqual(
            $this->normalizeHtml($w),
            $this->normalizeHtml("
               Part1:
               var1 = val1
               var2 = val2
        "))) var_dump($w);
    }
    
}