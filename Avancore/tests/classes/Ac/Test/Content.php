<?php

class Ac_Test_Content extends Ac_Test_Base {

    var $arr = array();
    
    function setUp() {
        parent::setUp();
        Ac_Impl_Cleanup::$debug++;
        Ac_Content::$debugInstances++;
        Ac_Content_StructuredText_PlaceholderRef::$debugInstances++;
        Ac_Debug::clear();
    }
    
    function tearDown() {
        Ac_Impl_Cleanup::$debug++;
        Ac_Content::$debugInstances++;
        Ac_Content_StructuredText_PlaceholderRef::$debugInstances++;
        parent::tearDown();
    }
    
    function testContentText() {
        $text = '123 456 789 012';
        $c = new Ac_Content_Text();
        $c->setText($text);
        
        $this->assertEqual(
            $c.'', 
            $text,
            'Method 1: __toString() works'
        );
        
        $this->assertEqual(
            $c->getEvaluated(), 
            $text,
            'Method 2: getEvaluated() works'
        );
        
        ob_start();
        
        $c->output();
        
        $this->assertEqual(
            ob_get_clean(),
            $text,
            'Method 3: output() works'
        );
        
        $this->assertEqual(
            stream_get_contents($c->getStream()), 
            $text,
            'Method 4: getStream() works'
        );
        
        $f = tempnam(AC_TESTS_TMP_PATH, 'act');
        file_put_contents($f, $text);
        
        $s = new Ac_Content_Text();
        
        $s->setText(Ac_Prototyped::factory(
            array(
                'class' => 'Ac_Value_Stream', 
                '__construct' => array('stream' => 'file://'.$f), 
                'blockSize' => 4
            )
        ));
        
        $this->arr = array();

        $s->output(array($this, 'cb'));
        
        $this->assertEqual(
            $this->arr, 
            array('123 ', '456 ', '789 ', '012'),
            'output() using $callback from Ac_Value_Stream with blockSize 4'
            .'should be done in proper chunks'
        );
        unlink($f);
    }
    
    function cb($val) {
        $this->arr[] = $val;
    }
    
    function testContentStructuredText() {
        $streamContent = 'XXX YYY ZZZ';
        $barContent = "bar:(c {$streamContent} e)";

        $output = "text1 text2 foo:(a b {$barContent}) text3";
        
        $anotherBar = "---another---";
        $outputWithAnotherBar = str_replace($barContent, $anotherBar, $output);
            
        $c = new Ac_Content_StructuredText();
        $s = new Ac_Value_Stream('data://text/plain;base64,'.base64_encode($streamContent));
        $s->setBlockSize(4);
        
        // The Most Ugly Way to use Ac_Content_StructuredText
        
        
        $c->append('text2 ');
        $foo = $c->getPlaceholder('fooPlaceholder', true);
        $c->append(') text3');
        $c->append('a ', 'fooPlaceholder');
        $c->append('b ', 'fooPlaceholder');
        // Reference to sub-placeholder may be added before the actual placeholder
        $foo->append($foo->createRef('barPlaceholder'));
        $c->prepend('foo:(', 'fooPlaceholder');
        $c->prepend('text1 ');
        
        $bar = new Ac_Content_StructuredText();
        $bar->setName('barPlaceholder');
        $bar->append('bar:(');
        $bar->append('c ');
        $bar->append($s);
        $bar->append(' e)');
        $c->putPlaceholder($bar, 'fooPlaceholder');
        
        if (!$this->assertEqual(
            $v = $c.'', 
            $output,
            'append() to the placeholders can be done after append()-ing to the outer blocks and outer placeholders'
        )) var_dump($v, $output);
        
        $this->assertEqual(
            $c->getPlaceholder('fooPlaceholder')->getName(), 
            'fooPlaceholder',
            'By default, placeholders get their names assigned'
        );
        
        $this->arr = array();
        $c->output(array($this, 'cb'));
        if (!$this->assertEqual(
            $this->arr, 
            array(
                'text1 ',
                'text2 ',
                'foo:(',
                'a ',
                'b ',
                'bar:(',
                'c ',
                'XXX ',
                'YYY ',
                'ZZZ',
                ' e)',
                ') text3'
            ),
            'output() shows data in the append()-ed chunks; also streams are retured by blocks'
        )) var_dump($this->arr);
        
        $c->append($anotherBar, array('fooPlaceholder', 'barPlaceholder'), true);
        if (!$this->assertEqual(
            $v = $c.'', 
            $outputWithAnotherBar, 
            'Placeholder can be replaced'
        )) var_dump($v, $outputWithAnotherBar);
        
        $d = new Ac_Content_StructuredText;
        
        $sv = new StringVal('2 ');
        
        $d->begin();
            echo 'a ';
            echo 'b ';
            $d->begin('sub');
                echo '{1 ';
                Ac_Buffer::out($sv);
            $d->end();
            echo 'c';
            $d->begin('sub');
                echo '3} ';
            $d->end();
        $d->end();
        
        $sv->foo = '2 ';
        
        if (!$this->assertEqual(
            $v = $d.'', 
            'a b {1 2 3} c',
            'Capture of the output to the Ac_Content_StructuredText buffer via Ac_Buffer'
        )) var_dump($v);
    }
    
    function testContentCreateDelete() {
        $c1 = new Ac_Content_StructuredText;
        $c1->append('foo');
        $c1->append('bar', 'baz');
        $c1->append($quux = new Ac_Content_StructuredText(array('name' => 'quux')), 'baz');
        $quux->append('aaa');
        $quux->append($c1->createRef());
        $ic = Ac_Debug::getInstanceCounters();
        $this->assertEqual(
            $ic['Ac_Content_StructuredText']['existing'], 
            3, 
            'three instances of Ac_Content_StructuredText were created'
        );
        $this->assertEqual(
            $ic['Ac_Content_StructuredText_PlaceholderRef']['existing'], 
            3,
            'three instances of Ac_Content_StructuredText_PlaceholderRef were created'
        );
        $c1->clear();
        unset($c1);
        unset($quux);
        $ic = Ac_Debug::getInstanceCounters();
        $wrong = false;
        if (!$this->assertEqual(
            $ic['Ac_Content_StructuredText']['existing'], 
            0,
            'no instances of Ac_Content_StructuredText left after clear() + unset()'
        )) $wrong = true;
        if (!$this->assertEqual(
            $ic['Ac_Content_StructuredText_PlaceholderRef']['existing'], 
            0,
            'no instances of Ac_Content_StructuredText_PlaceholderRef left after clear() + unset()'
        )) $wrong = true;
        if ($wrong) var_dump(
            $ic, 
            Ac_Debug::$misc['Ac_Impl_Cleanup']
        );
    }
    
    function testContentStCyclicRef() {
        $c1 = new Ac_Content_StructuredText;
        $c1->append('foo');
        $c1->append($c1->createRef());
        $this->expectError();
        $c1->__toString();
    }
    
    function testContentClone() {
        $c = new Ac_Content_StructuredText;
        $c->append('aaa ');
        $c->append('bbb ', 'foo');
        $c->append('ccc ', 'foo');
        $c->append('ddd ');
        $c->append($c->getPlaceholder('foo')->createRef());
        $this->assertEqual($c.'', $orig = 'aaa bbb ccc ddd bbb ccc ');
        $c1 = $c->createClone();
        $this->assertEqual($c1.'', $orig);
        $c1->setText('eee ', 'foo');
        $this->assertEqual($c1.'', $new = 'aaa eee ddd eee ', 'clone is successfully changed');
        $this->assertEqual($c.'', $orig, 'original StructuredText not changed after clone is changed');
        $c->clear();
        $this->assertEqual($c1.'', $new, 'clone isn\'t affected by original ->clear()');
        $c1->clear();
        unset($c);
        unset($c1);
        $ic = Ac_Debug::getInstanceCounters();
        if (!$this->assertEqual(
            $ic['Ac_Content_StructuredText']['existing'] + $ic['Ac_Content_StructuredText_PlaceholderRef']['existing'], 
            0,
            'no instances of Ac_Content_StructuredText or Ac_Content_StructuredText_PlaceholderRef left after clear() + unset()'
        )) var_dump($ic);
        
    }
    
    function testContentStMerge() {
        $co = new Ac_Content_StructuredText;
        $co
            ->append('Guys:')
            ->append(' Ilya, Serge', 'guys')
            ->append('; Girls:')
            ->append(' Tanya, Vika', 'girls')
            ->append('; Habits:')
            ->append(' Bad', 'habits');
        
        $this->assertEqual($co.'', 'Guys: Ilya, Serge; Girls: Tanya, Vika; Habits: Bad');
        
        $cm = new Ac_Content_StructuredText;
        $cm
            ->append('; Something more:')
            ->append(' some text')
            ->append(', Oksana', 'girls')
            ->append(', Yan', 'guys')
            ->append(' Good', 'habits')
            ->append('; p.s.: it\'s cool!', 'p.s.');
        
        $cm->getPlaceholder('habits')->setMergeMode(Ac_Content_StructuredText::mergeReplace);
        
        $cm->setRefsOnMerge(Ac_Content_StructuredText::refsLeaveOriginal);
        
        $cl = $co->createClone();
        $cm->mergeToContent($cl);
        
        $this->assertEqual(
            $cl.'', 
            $newText = 'Guys: Ilya, Serge, Yan; Girls: Tanya, Vika, Oksana; Habits: Good; Something more: some text; p.s.: it\'s cool!'
        );
        
        $cm->setText($newPs = '; p.s.: it\'s fun!', 'p.s.');
        
        $this->assertEqual(
            $cl.'', 
            $new2 = str_replace('cool', 'fun', $newText),
            'Since new placeholder was copied-by-reference, merged object\' text reflected the change'
        );
        
        $cl2 = $co->createClone();
        $cm->setCloneOnMerge(true);
        $cm->mergeToContent($cl2);
        $this->assertEqual($orig = $cl2.'', $new2);
        $cm->setText('', 'p.s.');
        $this->assertEqual($cl2.'', $orig, 
            'Change to source object did not affect target object since \$mergeToContent was true');
    }
    
    function testContentHtmlPart() {
        
    }
    
    function testCmsBlock() {
        
    }
    
}

class StringVal {
    
    var $foo;
    
    function __construct($foo = '?') { $this->foo = $foo; }
    
    function __toString() { return $this->foo; }
    
}