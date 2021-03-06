<?php

require_once(dirname(__FILE__).'/assets/Result_classes.php');

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

        $a_3 = new Ac_Result();  $a_3->setDebugData('a_3');
        $a_3_1 = new Ac_Result(); $a_3_1->setDebugData('a_3_1');
        $a_3_2 = new Ac_Result(); $a_3_2->setDebugData('a_3_2');
        $a_3->put($a_3_1);
        $a_3->put($a_3_2);
        $a->put($a_3);
        
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
        } while ($r && $i++ < 30);
        
        if (!$this->assertEqual(count($was), count(array_unique($was)), "Items should not be visited twice")) {
            var_dump($was);
        }
        
        $this->assertEqual($repeat, 0);
        
        if (!$this->assertEqual($stage->travLog, $rq = array(
                'a beginStage', 
                'a_1 beginStage', 
                'a_1_1 beginStage', 
                'a_1_1 endStage', 
                'a_1_2 beginStage', 
                'a_1_2 endStage', 
                'a_1 endStage', 
                'a_2 beginStage', 
                'a_2 endStage',
                'a_3 beginStage', 
                'a_3_1 beginStage', 
                'a_3_1 endStage', 
                'a_3_2 beginStage', 
                'a_3_2 endStage', 
                'a_3 endStage', 
                'a endStage', 
        ))) {
            echo "<table border='1'><tr>
                    <td valign='top'>".implode("<br />", $stage->travLog)."</td>
                    <td valign='top'>".implode("<br />", $rq)."</td>
                </tr></table>";
        }
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
    
    function advanceAndLog(Ac_Result_Position $p, $max = false, array & $aLog = array(), array & $bLog = array()) {
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
        
        $p = new Ac_Result_Position($b1);
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
        
        $p2 = new Ac_Result_Position($b1, 'BarResult');
        $foo = array();
        $bar = array();
        list($aLog, $bLog) = $this->advanceAndLog($p2);
        if (!$this->assertEqual($bLog, array(
            'content bar0.1',
            'first bar1',
        ))) var_dump($bLog, $aLog);
        
        $p3 = new Ac_Result_Position($b1, array('FooResult', 'BarResult'));
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
        
        $p = new Ac_Result_Position($b1);
        
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
        
        $p = new Ac_Result_Position($b1);
        
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
        
        $p = new Ac_Result_Position($b1);
        
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
        
        $p = new Ac_Result_Position($b1);
        
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
    
    function testPlainWriter() {
        $outer = new Ac_Result(array('debugData' => 'outer'));
        $sub1 = new Ac_Result(array('debugData' => 'sub1'));
        $sub11 = new Ac_Result(array('debugData' => 'sub11', 'content' => '{sub11 /}'));
        $sub1->put("{sub1}{$sub11}{/sub1}");
        $sub2 = new Ac_Result(array('debugData' => 'sub2', 'content' => '{sub2 /}'));
        $outer->put("{outer}{$sub1} {$sub2}{/outer}");
        ob_start();
        ini_set('html_errors', 0);
        $outer->write();
        ini_set('html_errors', 1);
        $buf = ob_get_clean();
        if (!$this->assertEqual($buf, '{outer}{sub1}{sub11 /}{/sub1} {sub2 /}{/outer}')) {
            var_dump($buf);
        }
    }
    
    function testLazyPlaceholders() {
        $h = new Ac_Result_Html();
        $this->assertEqual($h->listPlaceholders(), $h->listPlaceholders(false, false));
        $this->assertEqual($h->listPlaceholders(true), array());
        $h->getPlaceholder('title');
        $this->assertEqual($h->listPlaceholders(), $h->listPlaceholders(false, false));
        if (!$this->assertEqual($p = $h->listPlaceholders(true), array('title'))) var_dump($p);
    }
    
    function testHtmlResult() {
        $outer = new Ac_Result_Html();
        $outer->getPlaceholder('comments')->addItems(array('line1', 'line2', 'line3'));
        $widget1 = new Ac_Result_Html(array(
            'content' =>  $w1content = '<div><p>Widget 1</p><div>Widget 1 body</div></div>',
            'title' => 'Sub',
            'assets' => array(
                '{FOO}/first.js',
                '{FOO}/first.css',
            )
        ));
        $widget2 = new Ac_Result_Html(array(
            'content' =>  $w2content = '<div><p>Widget 2</p><div>Widget 2 body</div></div>',
            'assets' => array(
                '{FOO}/first.js',
                '{FOO}/first.css',
                '{FOO}/second.js',
            )
        ));
        $widget3 = new Ac_Result_Html(array(
            'content' =>  $w3content = '<div><p>Widget 3</p><div>Widget 3 body</div></div>',
            'assets' => array(
                '{FOO}/second.js',
                '{FOO}/second.css',
            )
        ));
        $widget23 = new Ac_Result_Html(array(
            'content' =>  "<div class='group'>{$widget2} {$widget3}</div>",
        ));
            
        $outer->put("{$widget1} {$widget23}");
        $outer->getPlaceholder('title')->addItems(array('Parent'));
        ini_set('html_errors', 0);
        $outer->write(array('writeRoot' => false));
        ini_set('html_errors', 1);
        
        if (!$this->assertEqual($outer->getContent(), 
            "$w1content <div class='group'>{$w2content} {$w3content}</div>"
        )) var_dump($outer->getContent());
            
        $assets = $outer->getPlaceholder('assets')->getItems();
        $needAssets = array(
            '{FOO}/first.js',
            '{FOO}/first.css',
            '{FOO}/second.js',
            '{FOO}/second.css',
        );
        
        sort($assets);
        sort($needAssets);
        if (!$this->assertEqual($assets, $needAssets)) var_dump($assets);
        $outer->getPlaceholder('doctype')->addItems(array(Ac_Result_Html::DOCTYPE_STRICT));
        $outer->getPlaceholder('doctype')->addItems(array(Ac_Result_Html::DOCTYPE_HTML5));
        $outer->initScripts[] = "console.log('foo');";
        $outer->initScripts[] = new Ac_Js_Script("console.log('bar');");
        ob_start();
        $outer->setWriter($w = new Ac_Result_Writer_RenderHtml(array(
            'assetPlaceholders' => array('{FOO}' => '//cdn.foo.example.com')
        )));
        $w->write();
        $buf = ob_get_clean();
        if (!$this->assertEqual(
            $this->normalizeHtml($buf),
            $this->normalizeHtml(
<<<EOD
                <!DOCTYPE html>
                <html>
                    <head>
                        <title>Parent - Sub</title>
                        <link rel="stylesheet" type="text/css" href="//cdn.foo.example.com/first.css" />
                        <link rel="stylesheet" type="text/css" href="//cdn.foo.example.com/second.css" />
                        <script type="text/javascript" src="//cdn.foo.example.com/first.js"> </script>
                        <script type="text/javascript" src="//cdn.foo.example.com/second.js"> </script>
                    </head>
                    <body>
                <div><p>Widget 1</p><div>Widget 1 body</div></div> <div class='group'><div><p>Widget 2</p><div>Widget 2 body</div></div> <div><p>Widget 3</p><div>Widget 3 body</div></div></div>    
                <script type='text/javascript'>
                    console.log('foo');
                    console.log('bar');
                </script>
                </body>
                </html>


                <!--

                    line1

                    line2

                    line3
                -->
EOD
            )
        )) var_dump($buf);
        
    }
    
    function testResultMagic() {
        
        $r = new Ac_Result_Html();
        $r->assets[] = '{FOO}/bar.js';
        $r->assets[] = '{FOO}/bar.css';
        $r->headTags = array('<link rel="canonical" href="http://www.example.com" />');
        
        if (!$this->assertEqual(
            $i = $r->assets->getItems(),
            $rAssets = array(
                '{FOO}/bar.js',
                '{FOO}/bar.css'
            )
        )) var_dump($i);
        
        if (!$this->assertEqual(
            $i = $r->headTags->getItems(),
            array('<link rel="canonical" href="http://www.example.com" />')
        )) var_dump($i);
        
        $r2 = new Ac_Result_Html();
        $r2->assets[] = '{FOO}/quux.js';
        $r2->assets = $r->assets;
        
        if (!$this->assertEqual(
            $i = $r2->assets->getItems(),
            $rAssets
        )) var_dump($i);
        
        $r3 = new Ac_Result_Html();
        $r3->assets = '{FOO}/quux.js';
        $r3->assets->addItems($r->assets);
        if (!$this->assertEqual(
            $i = $r3->assets->getItems(),
            array_merge(array('{FOO}/quux.js'), $rAssets)
        )) var_dump($i);
        
        $r3->assets = null;
        
        if (!$this->assertEqual(
            $i = $r3->assets->getItems(),
            array()
        )) var_dump($i);
        
        $s = array(
            'console.log("Foo");',
            new Ac_Js_Script('console.log("Bar");')
        );
        $r3->initScripts[] = $s[0];
        $r3->initScripts[] = $s[1];
        
        if (!$this->assertEqual(
            $i = $r3->initScripts->getItems(),
            $s
        )) var_dump($i);
        
        $s = array(
            'console.log("Foo");',
            new Ac_Js_Script('console.log("Bar");')
        );
        $r3->initScripts->setItems($s);
        
        if (!$this->assertEqual(
            $i = $r3->initScripts->getItems(),
            $s
        )) var_dump($i);
        
        $r4 = new Ac_Result();
        $r4->setContent('foo');
        $this->assertEqual($r4->content, 'foo');
        $r4->content = 'bar';
        $this->assertEqual($r4->getContent(), 'bar');
        
    }
    
    function testNonExistentPropertySet() {
        
        $r = new Ac_Result();
        $this->expectException();
        $r->nonExistentProperty = 'bar';
        
    }
    
    function testNonExistentPropertyGet() {
        
        $r = new Ac_Result();
        $this->expectException();
        echo $r->nonExistentProperty2;
        
    }
    
    function testResultWithCharset() {
        
        $inner = new Ac_Result_Html(array('content' => iconv('utf-8', 'windows-1251', 'Немного текста'), 'charset' => 'windows-1251'));
        $outer = new Ac_Result_Html(array('content' => "Снаружи. {$inner} внутри", 'charset' => 'utf-8'));
        $s = new Ac_Result_Stage_Write(array('root' => $outer, 'writeRoot' => false));
        $s->invoke();
        if (!$this->assertEqual($c = $outer->getContent(), 'Снаружи. Немного текста внутри')) var_dump($c);
        
    }
    
    function testResultHtmlMergeContentType() {
        $inner = new Ac_Result_Html(array('content' => 'Some text'));
        $outer = new Ac_Result_Http(array('content' => $inner));
        $stage = new Ac_Result_Stage_Write(array('root' => $outer, 'writeRoot' => false));
        $inner->setContentType('text/html');
        $stage->invoke();
        if (!$this->assertTrue(in_array('Content-Type: text/html', $headers = $outer->getHeaders()->getItems()))) var_dump($headers);
    }
    
    function testResultHtmlHttpOut() {
        $inner = new Ac_Result_Html(array('content' => 'Some text', 'charset' => 'utf-8', 'charsetUsage' => Ac_I_Result_WithCharset::CHARSET_PROPAGATE));
        $outer = new Ac_Result_Html(array('content' => $inner));
        $env = new Ac_Result_Environment_Dummy;
        $outer->setWriter(new Ac_Result_Writer_RenderHtml(array('environment' => $env)));
        $stage = new Ac_Result_Stage_Write(array('root' => $outer, 'writeRoot' => true));
        $stage->invoke();
        $this->assertEqual($env->headers, array(
            'Content-Type: text/html; charset=utf-8'
        ));
        $this->assertTrue(strpos($this->normalizeHtml($env->responseText), '<body>Some text</body>') !== false);
        
        $plain = new Ac_Result_Http(array('contentType' => 'text/plain', 'content' => 'Some Interesting Text'));
        $plain->setWriter(new Ac_Result_Writer_HttpOut(array('environment' => $env)));
        $stage = new Ac_Result_Stage_Write(array('root' => $plain, 'writeRoot' => true));
        $stage->invoke();
        if (!$this->assertEqual($h = $env->headers, array(
            'Content-Type: text/plain'
        ))) var_dump($h);
        if (!$this->assertEqual($text = $env->responseText, $plain->getContent())) var_dump($text);
    }
    
    function testAdvancedPlaceholders() {
        $parent = new Ac_Result_Html(array(
            'title' => 'Will be replaced',
            'meta' => array(
                'keywords' => array('foo', 'bar'),
                'description' => array('aaa bbb ccc'),
                'http' => array(
                    'X-Foo-1' => 'foo1', 
                    'X-Foo-2' => 'You won\'t see me'),
            ),
        ));
        $child = new Ac_Result_Html(array(
            'title' => 'New title',
            'meta' => array(
                'keywords' => array('There can be only one'),
                'description' => array('ddd'),
                'http' => array(
                    'X-Foo-2' => 'foo2', 
                ),
            ),
        ));
        $child->title->setOverwriteOnMerge(true);
        $child->meta->keywords->setOverwriteOnMerge(true);
        $parent->meta->description[] = "some extra";
        $child->meta['http']['X-Foo-3'] = 'foo3';
        $child->meta[] = "not a good idea";
        $child->meta['author'] = "Ilya Rezvin";
        $parent->meta['generator'] = "Avancore 0.3";
        $parent->put($child);
        ob_start();
        $parent->write();
        $c = ob_get_clean();
        if (!$this->assertEqual(
            $this->normalizeHtml($c),
            $this->normalizeHtml(<<<EOD
                <!DOCTYPE html>
                <html>
                <head>
                    <title>New title</title>

                    <meta name="keywords" content="There can be only one" />
                    <meta name="description" content="aaa bbb ccc some extra ddd" />
                    <meta http-equiv="X-Foo-1" content="foo1" />
                    <meta http-equiv="X-Foo-2" content="foo2" />
                    <meta http-equiv="X-Foo-3" content="foo3" />
                    <meta name="generator" content="Avancore 0.3" />
                    <meta name="0" content="not a good idea" />
                    <meta name="author" content="Ilya Rezvin" />
                </head>
                <body>
                </body>
                </html>
EOD
            )
        )) var_dump($c);
        
    }
    
    /**
     * @return Ac_Result
     */
    protected function sample7() {
        $r = new Ac_Result;
        $r1 = new Ac_Result;
        $r2 = new Ac_Result;
        $r1_1 = new Ac_Result(array('content' => 'r1_1'));
        $r1_2 = new Ac_Result(array('content' => 'r1_2'));
        $r2_1 = new Ac_Result(array('content' => 'r2_1'));
        $r2_2 = new Ac_Result(array('content' => 'r2_2'));
        $r->setContent("r1: {$r1} r2: {$r2}");
        $r1->setContent("({$r1_1} {$r1_2})");
        $r2->setContent("({$r2_1} {$r2_2})");
        return compact('r', 'r1', 'r2', 'r1_1', 'r1_2', 'r2_1', 'r2_2');
    }
    
    function testOverrides() {
        $a = $this->sample7();
        $this->assertEqual($a['r']->writeAndReturn(), 'r1: (r1_1 r1_2) r2: (r2_1 r2_2)');
        
        $a = $this->sample7();
        $a['r']->setReplaceWith(new Ac_Result(array('content' => 'Something')));
        $this->assertEqual($a['r']->writeAndReturn(), 'Something');
        
        $a = $this->sample7();
        $a['r1_1']->setOverrideMode(Ac_Result::OVERRIDE_PARENT);
        $this->assertEqual($a['r']->writeAndReturn(), 'r1: r1_1 r2: (r2_1 r2_2)');
        
        $a = $this->sample7();
        $a['r1_1']->setOverrideMode(Ac_Result::OVERRIDE_ALL);
        $this->assertEqual($a['r']->writeAndReturn(), 'r1_1');
        
        $a = $this->sample7();
        $a['r1_1']->setReplaceWith(new Ac_Result(array('content' => 'Foo Bar')));
        $this->assertEqual($a['r']->writeAndReturn(), 'r1: (Foo Bar r1_2) r2: (r2_1 r2_2)');
        
        $a = $this->sample7();
        $a['r1_1']->setReplaceWith(new Ac_Result(array('content' => 'Foo Bar', 'replaceWith' => new Ac_Result(array('content' => 'Baz Quux')))));
        $this->assertEqual($a['r']->writeAndReturn(), 'r1: (Baz Quux r1_2) r2: (r2_1 r2_2)');
        
        $a = $this->sample7();
        $a['r1_1']->setReplaceWith(new Ac_Result(array('content' => 'Foo Bar', 'replaceWith' => new Ac_Result(array('content' => 'Baz Quux', 'overrideMode' => Ac_Result::OVERRIDE_PARENT)))));
        $this->assertEqual($a['r']->writeAndReturn(), 'r1: Baz Quux r2: (r2_1 r2_2)');
        
        $a = $this->sample7();
        $a['r1_1']->setReplaceWith(new Ac_Result(array('content' => 'Foo Bar', 'replaceWith' => new Ac_Result(array('content' => 'Baz Quux', 'overrideMode' => Ac_Result::OVERRIDE_ALL)))));
        $this->assertEqual($a['r']->writeAndReturn(), 'Baz Quux');
    }
    
    function testWriteAgain() {
        $a = $this->sample7();
        $this->assertEqual($a['r']->writeAndReturn(), 'r1: (r1_1 r1_2) r2: (r2_1 r2_2)');
        $this->assertEqual($a['r']->writeAndReturn(), 'r1: (r1_1 r1_2) r2: (r2_1 r2_2)');
        $a['r']->setReplaceWith(new Ac_Result(array('content' => 'Something New')));
        $this->assertEqual($a['r']->writeAndReturn(), 'Something New');
    }
    
    function testWriterReplace() {
        $o = new Ac_Result(array('debugData' => 'o'));
        $i = new Ac_Result(array(
            'content' => 'inner',
            'writer' => 'Ac_Result_Writer_Replace',
            'debugData' => 'i',
        ));
        $o->put("*** {$i} ***");
        if (!$this->assertEqual($str = $o->writeAndReturn(), $i->getContent()))
            var_dump($str);
    }
    
    function testJson() {
        
        $data = array('foo' => 'bar', 'baz' => array('quux', 1, 2, 3));
        $js = new Ac_Result_Json(array('data' => $data));
        
        $i = new Ac_Result(array('content' => "<{$js}>"));
        $o = new Ac_Result(array('content' => "*{$i}*"));
        
        $d = new Ac_Result_Environment_Dummy();
        Ac_Result_Environment::setDefault($d);
        
        $o->write();
        if (!$this->assertEqual($str = $d->responseText, $js->getContent()))
            var_dump($str);
        if (!$this->assertEqual($h = $d->headers, array('Content-Type: text/json')))
            var_dump($h);
        
        
        
        
        $js = new Ac_Result_Js(array('data' => $data));
        
        $i = new Ac_Result(array('content' => "<{$js}>"));
        $o = new Ac_Result(array('content' => "*{$i}*"));
        
        $d = new Ac_Result_Environment_Dummy();
        Ac_Result_Environment::setDefault($d);
        
        $o->write();
        if (!$this->assertEqual($str = $d->responseText, $js->getContent()))
            var_dump($str);
        if (!$this->assertEqual($h = $d->headers, array('Content-Type: text/javascript')))
            var_dump($h);
        
    }
    
    function testHeaders() {
        $p = new Ac_Result_Placeholder_Headers();
        $p[] = "Content-type: text/plain";
        $p[] = "Foo: bar";
        $p[] = "Another: value";
        $p[] = "aNother: value2";
        $p[] = "badheader";
        if (!$this->assertEqual($h = $p->getHeaders(), array(
            'content-type' => array(0 => 'text/plain'),
            'foo' => array(1 => 'bar'),
            'another' => array(2 => 'value', 3 => 'value2'),
            '' => array(4 => 'badheader'),
        ))) var_dump($h);
        if (!$this->assertEqual($h = $p->getHeaders('content-type'), array(
            0 => 'text/plain',
        ))) var_dump($h);
        if (!$this->assertEqual($h = $p->getHeaders('n0ne}{i5t3nt'), array(
        ))) var_dump($h);
        $this->assertEqual(
            $p->replaceHeader('content-type: text/html'),
            array(0 => 'text/plain')
        );
        $this->assertEqual(array_values($p->getHeaders('content-type')), array('text/html'));
        $ex = null;
        try {
            $p->replaceHeader(array('foobar'));
        } catch (Ac_E_InvalidCall $ex) {
        }
        $this->assertNotNull($ex);
        $p->replaceHeader(array('v3', 'v4'), 'anoTHeR');
        $this->assertEqual(array_values($p->getHeaders('another')), array(
           'v3', 'v4'
        ));
        $p->setHeaders(array('foo' => 'baz', 'content-type: image/jpeg', 'a' => array('b', 'c')));
        $this->assertEqual($p->getHeaders(), array(
            'foo' => array(0 => 'baz'),
            'content-type' => array(1 => 'image/jpeg'),
            'a' => array(2 => 'b', 3 => 'c')
        ));
    }
    
    function testHttpStatus() {
        $h = new Ac_Result_Http();
        $h->setStatusCode(404, "not found");
        $this->assertEqual($h->getReasonPhrase(), "not found");
        $d = new Ac_Result_Environment_Dummy();
        Ac_Result_Environment::setDefault($d);
        $h->write();
        $this->assertEqual($d->httpStatus, "404 not found");
    }
    
    function testRedirect() {
        $h = new Ac_Result_Redirect();
        $h->setUrl('http://example.com', Ac_Result_Redirect::REDIR_PERMANENT);
        $d = new Ac_Result_Environment_Dummy();
        Ac_Result_Environment::setDefault($d);
        $h->write();
        $this->assertEqual($d->headers, array('location: http://example.com'));
        $this->assertEqual($d->httpStatus, '301 Moved Permanently');
        
        $h = new Ac_Result_Redirect();
        $d = new Ac_Result_Environment_Dummy();
        Ac_Result_Environment::setDefault($d);
        $h->setUrl('http://example.com/another/page', Ac_Result_Redirect::REDIR_PERMANENT);
        $h2 = new Ac_Result_Html();
        $h2->put('Something');
        $h2->put($h);
        $h2->write();
        $this->assertEqual($d->headers, array('location: http://example.com/another/page'));
        $this->assertEqual($d->httpStatus, '301 Moved Permanently');
    }
    
    function testDeferredsAndStringObjects() {
        require_once(dirname(__FILE__).'/assets/deferredsAndStringObjects.php');
        
        $r = new Ac_Result;
        
        $r->put(new ExampleDeferred('1 ', '2'));
        $r->put("\n");
        $r->put(new ExampleDeferred('2 ', '2'));
        $r->put("\n");
        $r->put(new ExampleDeferred('3 ', '2', true));

        $tmp = new Ac_Result_Stage_Deferreds();
        $tmp->setRoot($r);
        $tmp->invoke();
        
        if (!$this->assertEqual($rendered = $r->writeAndReturn(), "1 2 3\n2 2 3\n3 2 3")) var_dump($rendered);

        // tricky part - nested deferreds
        
        $sub1 = new ExampleDeferred('2.1', ' 3');
        $sub2 = new ExampleDeferred('3.1', ',');
        $sub3 = new ExampleDeferred('3.2', ')');
        
        $r = new Ac_Result;
        
        $r->put(new ExampleDeferred('1 ', '2'));
        $r->put("\n");
        $r->put(new ExampleDeferred('2 ('.$sub1, ')'));
        $r->put("\n");
        $r->put(new ExampleDeferred('3 ('.$sub2.' '.$sub3, ' 2'));
        
        $tmp = new Ac_Result_Stage_Deferreds();
        $tmp->setRoot($r);
        $tmp->invoke();
        
        if (!$this->assertEqual($rendered = $r->writeAndReturn(), "1 2 3\n2 (2.1 3 3) 3\n3 (3.1, 3 3.2) 3 2 3")) var_dump($rendered);

        // trickiest part - nested result with deferreds

        $r1 = new Ac_Result;
        $r1->put(new ExampleDeferred('1 ', '2'));
        $r1->put("\n");
        $r1->put(new ExampleDeferred('2 ', '2'));
        $r1->put("\n");
        $r1->put(new ExampleDeferred('3 ', '2', true));
        
        $r1result = "1 2 3\n2 2 3\n3 2 3";

        $r = new Ac_Result;
        $r->put(new ExampleDeferred('1 ', '2'));
        $r->put("\n");
        $r->put(new ExampleDeferred($r1, ' 2'));
        $r->put("\n");
        $r->put(new ExampleDeferred('3 ', '2', true));

        $r2 = new Ac_Result;
        $r2->put('before ');
        $r2->put($r);
        
        $r3 = new Ac_Result;
        $r3->put(' after');
        $r2->put($r3);
        
        if (!$this->assertEqual($rendered = $r2->writeAndReturn(), "before 1 2 3\n{$r1result} 2 3\n3 2 3 after")) var_dump($rendered);
        
        // test rendering of string objects
        
        $r = new Ac_Result;
        $r->put(new Ac_StringObject_Render(new ExampleRendered('aText')));
        $r->put(' ');
        $r->put(new ExampleDeferred(new Ac_StringObject_Render(new ExampleRendered('aText2')), ' 2'));
        
        if (!$this->assertEqual($rendered = $r->writeAndReturn(), "aText aText2 2 3")) var_dump($rendered);
        
        // test rendering before store

        $r = new Ac_Result;
        $r->put($a = new ExampleDeferred('1 ', '2'));
        $r->put("\n");
        $r->put(new ExampleDeferred('2 ', '2', true));
        $r->put("\n");
        $r->put($b = new ExampleDeferred('3 ', '2'));
        
        $tmp = new Ac_Result_Stage_Deferreds();
        $tmp->setIsBeforeStore(true);
        $tmp->setRoot($r);
        $tmp->invoke();
        
        $s = new Ac_Result_Stage_Write(array('renderDeferreds' => false));
        
        if (!$this->assertEqual($rendered = $r->writeAndReturn($s), "$a\n2 2 3\n$b")) var_dump($rendered);
        
        if (!$this->assertEqual($rendered = $r->writeAndReturn(), "1 2 3\n2 2 3\n3 2 3")) var_dump($rendered);
        
    }

    /**
     * @return Ac_Result
     */
    protected function getTestResultForLoadAndSave($hId = 'AllHandler', Ac_I_Result_Handler & $allHandler = null, $debugPrefix = 'a', $writeOnStore = false) {
        
        require_once(dirname(__FILE__).'/assets/deferredsAndStringObjects.php');
        
        $r = new Ac_Result;
        if (is_null($allHandler)) $allHandler = new AllHandler($hId);
        $r->addHandler($allHandler, 'all');
        $r->put(new ExampleDeferred('1 ', '2'));
        $r->put("\n");
        $r->setDebugData($debugPrefix.' o');
        
        $sr = new Ac_Result;
        $sr->put(new ExampleDeferred('2 ', '2', !$writeOnStore));
        $sr->setDebugData($debugPrefix.' i');
        $sr->setWriteOnStore($writeOnStore);
        
        $r->put($sr);
        
        $r->put("\n");
        $r->put(new ExampleDeferred('3 ', '2'));
        return $r;
    }
    
    
    
    function testLoadAndSave() {
        
        AllHandler::$log = array();
        
        $r = $this->getTestResultForLoadAndSave('allHandler', $allHandler, 'r1.1');
        $r2 = $this->getTestResultForLoadAndSave('allHandler2', $allHandler2, 'r1.2');
        
        $pres2 = $r2->writeAndReturn();
        
        $ser = serialize($r);
        StringObjectCache::clearStrings();
        $r1 = unserialize($ser);
        $pres1 = $r1->writeAndReturn();
        if (!$this->assertEqual($pres1, $pres2)) {
            var_dump($pres1, $pres2);
        }
        
        //var_dump(AllHandler::$log);
        
        // now let's check if render-before-write works
        
        $r = $this->getTestResultForLoadAndSave('allHandler', $allHandler, 'r2.1');
        $r2 = $this->getTestResultForLoadAndSave('allHandler2', $allHandler2, 'r2.2');

        
        ExampleEvaluator::$thirdPart = ' 4before';

        $ser = serialize($r);
        StringObjectCache::clearStrings();
        $r1 = unserialize($ser);
        
        ExampleEvaluator::$thirdPart = ' 4after';
        
        $pres1 = $r1->writeAndReturn();
        if (!$this->assertEqual($pres1, 
              "1 2 4after\n"
            . "2 2 4before\n" // this was evaluated during serialize()
            . "3 2 4after", 
            "\$renderBeforeWrite")) {
            var_dump($pres1);
        }
        
        // now let's check if Ac_Result::$writeOnStore == TRUE works
        
        $r = $this->getTestResultForLoadAndSave('allHandler', $allHandler, 'r3.1', true);
        
        ExampleEvaluator::$thirdPart = ' 5before';

        $ser = serialize($r);
        StringObjectCache::clearStrings();
        $r1 = unserialize($ser);
        
        ExampleEvaluator::$thirdPart = ' 5after';
        
        $pres1 = $r1->writeAndReturn();
        if (!$this->assertEqual($pres1, 
              "1 2 5after\n"
            . "2 2 5before\n" // this was evaluated during serialize()
            . "3 2 5after", 
            "\$renderBeforeWrite")) {
            var_dump($pres1);
        }
        
        $r = $this->getTestResultForLoadAndSave('allHandler', $allHandler, 'r3.1');
        
        // Question: how Store stage should behave when root has writeOnStore()?
        $r->setWriteOnStore(true);
        
        ExampleEvaluator::$thirdPart = ' 6before';

        $ser = serialize($r);
        StringObjectCache::clearStrings();
        $r1 = unserialize($ser);
        
        ExampleEvaluator::$thirdPart = ' 6after';
        
        $pres1 = $r1->writeAndReturn();
        if (!$this->assertEqual($pres1, 
              "1 2 6before\n"
            . "2 2 6before\n" // this was evaluated during serialize()
            . "3 2 6before", 
            "\$renderBeforeWrite")) {
            var_dump($pres1);
        }
        
    }
    
    function testObsoleteOnLoad() {
        $r = new Ac_Result;
        $r1 = new Ac_Result;
        $r->put($r1);
        $r2 = new Ac_Result;
        $r1->put($r2);
        $r2->markObsolete();
        $s = serialize($r);
        $uns = unserialize($s);
        $this->assertTrue($uns->getIsObsolete());
       
    }
    
    function testCloneResult() {
        $r = new Ac_Result();
        $r2 = new Ac_Result(array('content' => 'old'));
        $r->put($r2);
        $cloneR = clone $r;
        $r2->setContent('new');
        if (!$this->assertEqual($strR = $r->writeAndReturn(), 'new')) 
            var_dump($strR);
        if (!$this->assertEqual($strCloneR = $cloneR->writeAndReturn(), 'old')) 
            var_dump($strCloneR);
    }
    
    function testStreamInResult() {
        $r = new Ac_Result();
        $r->put('foo ', $sVal = new TestStreamValue($fl = (dirname(__FILE__).'/assets/Result_classes.php')), ' bar');
        $fc = file_get_contents($fl);
        $cnt = $r->writeAndReturn();
        if (!$this->assertEqual($cnt, 'foo '.$fc.' bar')) var_dump($cnt);
        $this->assertEqual($sVal->outputCalled, 1);
        $r1 = new Ac_Result();
    }
    
    function testSlots() {
        // the final frontier

        $r = new Ac_Result();
        
        $second = new Ac_Result(array('content' => 'second', 'slotId' => 'slot'));
        
        $inner = new Ac_Result();
        $inner->put(
            'a ', 
            
            new Ac_Result(array(
                    'content' => 'first'.($second), 
                    'slotId' => 'slot'
            )), 
            'b '
        );
        $r->put($inner);
        $r->put(new Ac_Result_SlotRenderer(array('slotId' => 'slot', 'before' => '(', 'separator' => ', ', 'after' => ')')));
        $inner2 = new Ac_Result();
        $inner2->put(
            ' c ', 
            new Ac_Result(array('content' => 'third', 'slotId' => 'slot')), 
            'd'
        );
        $r->put($inner2);
        
        require_once(dirname(__FILE__).'/assets/deferredsAndStringObjects.php');
        
        $def = new ExampleDeferred(new Ac_Result(array('slotId' => 'slot', 'content' => 'fourth')), '...');
        
        $r->put($def);
        
        ExampleEvaluator::$thirdPart = '';
            
        $ren = new ExampleRendered(new Ac_Result(array('slotId' => 'slot', 'content' => 'fifth')));
        
        $r->put($ren);
        
        $outer = new Ac_Result;
        $outer->put(
            new Ac_Result(array('slotId' => 'slot', 'content' => 'P.S.#1')), 
            '{', $r, '}', 
            new Ac_Result_SlotRenderer(array('slotId' => 'slot', 'before' => ' (', 'separator' => ', ', 'after' => ')')),
            new Ac_Result(array('slotId' => 'slot', 'content' => 'P.S.#2'))
        );
        
        if (!$this->assertEqual($str = $outer->writeAndReturn(), "{a b (first, second, third, fourth, fifth) c d...} (P.S.#1, P.S.#2)"))
            var_dump($str);
    }
    
    function testDeferredsPassing() {
        require_once(dirname(__FILE__).'/assets/deferredsAndStringObjects.php');
        
        $innerStat = new Ac_Result;
        $innerStat->setDebugData('innerStat');
         
        $innerCached = new Ac_Result;
        $innerStat->setDebugData('innerCached');
        $innerCached->setCacheable(true);
        
        $defStat1 = new ExampleDeferred2(false, true, 'defStat1cnt');
        $defStat1->debData = 'defStat1instance';
        $defStat2 = new ExampleDeferred2(false, true, 'defStat2cnt');
        $defStat2->debData = 'defStat2instance';
        $defStat3 = new ExampleDeferred2(false, true, 'defStat3cnt');
        $defStat3->debData = 'defStat3instance';
        $defCached1 = new ExampleDeferred2(false, false, 'defCached1');
        $defCached1->debData = 'defCached1instance';
        $defCached2 = new ExampleDeferred2(false, false, 'defCached2');
        $defCached2->debData = 'defCached2instance';
        $defCached3 = new ExampleDeferred2(false, false, 'defCached3');
        $defCached3->debData = 'defCached3instance';
        
        $outer = new Ac_Result;
        $outer->setDebugData('outer');
        
        $innerStat->put($defStat1.$defCached1);
        $innerCached->put($defStat2.$defCached2);
        $outer->put($innerStat.$innerCached.$defStat3.$defCached3);
        
        $s = serialize($outer);
        
        $this->assertTrue(strpos($s, $defStat1->debData) === false);
        $this->assertTrue(strpos($s, $defStat2->debData) === false);
        $this->assertTrue(strpos($s, $defStat3->debData) === false);
        $this->assertTrue(strpos($s, $defCached1->debData) !== false);
        $this->assertTrue(strpos($s, $defCached2->debData) !== false);
        $this->assertTrue(strpos($s, $defCached3->debData) !== false);
        
        $s = trim($s);
        $s = preg_replace('/O:[0-9]+:"[^"]+"/', 'a', $s);
        
        // TODO: test results
        //var_dump(unserialize($s));
       
        //echo(htmlspecialchars(serialize($outer)));
    }
    
    function testHtmlInHtml() {
        $inner = new Ac_Result_Html();
        $inner->title = "The Title";
        $inner->setContent("<p>Inner</p>");
        
        
        $outer = new Ac_Result_Html();
        $outer->setContent("<div class='outer'>{$inner}</div>");
        
        $ret = $outer->writeAndReturn(); 
        if (!$this->assertEqual($this->normalizeHtml($ret), $this->normalizeHtml('
            <!DOCTYPE html>
            <html>
            <head>
                <title>The Title</title></head>
            <body>
            <div class=\'outer\'><p>Inner</p></div> 
            </body>
            </html>
        '))) {
            var_dump($ret);
        }
    }
    
}
