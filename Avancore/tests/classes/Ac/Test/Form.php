<?php

class Ac_Test_Form extends Ac_Test_Base {
    
    protected $bootSampleApp = true;
    
    function testZeroDate() {
        
        $p0 = new Ac_Form_Control_Date(null, array(
            'externalFormat' => 'd-m-Y',
            'internalFormat' => 'Y/m/d',
        ), 'date1');
        $p0->setValue('1982-12-23');
        $this->assertEqual($p0->getDisplayValue(), '23-12-1982');
        $this->assertEqual($p0->getValue(), '1982/12/23');
        
        $p01 = new Ac_Form_Control_Date(null, array(
        ), 'date1');
        $p01->setValue('0000-00-00');
        // default external format is d.m.Y
        $this->assertEqual($p01->getDisplayValue(), '00.00.0000');
        $this->assertEqual($p01->getValue(), '0000-00-00');
        
        
        $p1 = new Ac_Form_Control_Date(null, array(
            'externalFormat' => 'd-m-Y',
            'internalFormat' => 'Y/m/d',
        ), 'date1');
        $p1->setValue('0000-00-00');
        $this->assertEqual($p1->getDisplayValue(), '00-00-0000');
        $this->assertEqual($p1->getValue(), '0000/00/00');
        
        $p2 = new Ac_Form_Control_DateTime(null, array(
            'externalFormat' => 'd-m-Y H:i:s',
            'internalFormat' => 'G:i:s d/n/y',
        ), 'date2');
        $p2->setValue('0000-00-00 00:00:00');
        $this->assertEqual($p2->getDisplayValue(), '00-00-0000 00:00:00');
        $this->assertEqual($p2->getValue(), '0:00:00 00/0/00');
        
        $p3 = new Ac_Form_Control_DateTime(null, array(
            'externalFormat' => 'Foobar',
            'internalFormat' => 'Foobar',
        ), 'date2');
        $p3->setValue('0000-00-00 00:00:00');
        $this->assertEqual($p3->getDisplayValue(), '0000-00-00 00:00:00');
        $this->assertEqual($p3->getValue(), '0000-00-00 00:00:00');
        
    }
    
    function testFormReflectsModelChanges() {
        $p = Sample::getInstance()->getSamplePersonMapper()->createRecord();
        $p->name = 'Old name';
        $tf = $this->createTestForm1(array('model' => $p));
        $p->isSingle = true;
        $ctx = $tf->getContext();
        $ctx->setData(array(
            'name' => 'Input name',
        ));
        $n = $tf->getControl('name');
        $s = $tf->getControl('isSingle');
        
        $tf->setSubmitted(); // required for checkboxes 
        
        // initial values are from request (isSingle is default since it's not in the request)
        $this->assertEqual($n->getValue(), 'Input name');
        $this->assertEqual($s->getValue(), false);
        
        $tf->updateFromModel();
        $this->assertEqual($n->getValue(), 'Old name');
        $this->assertEqual($s->getValue(), true);
        
        $p->name = 'New name';
        $p->isSingle = false;
        
        $this->assertEqual($n->getValue(), 'New name');
        $this->assertEqual($s->getValue(), false);
        
        // round and round and round we go
        $tf->updateFromRequest();
        $this->assertEqual($n->getValue(), 'Input name');
        $this->assertEqual($s->getValue(), false);

        $n->setValue('Set name');
        $s->setValue(true);
        $this->assertEqual($n->getValue(), 'Set name');
        $this->assertEqual($s->getValue(), true);

    }
    
    function testErrorList () {
        $person = Sample::getInstance()->getSamplePersonMapper()->createRecord();
        $f = new Ac_Form(null, array(
            'controls' => array(
                'errorList' => array(
                    'class' => 'Ac_Form_Control_ErrorList',
                    'hideErrorsShownByOtherControls' => false,
                ),
                'tabs' => array(
                    'class' => 'Ac_Form_Control_Tabs',
                    'controls' => array(
                        'sheet1' => array(),
                    ),
                ),
                'name' => array(
                    'class' => 'Ac_Form_Control_Text', 
                    'displayParent' => '../tabs/sheet1'
                ),
                'gender' => array(
                    'class' => 'Ac_Form_Control_List', 
                    'displayParent' => '../tabs/sheet1'
                ),
            ),
            'model' => $person
        ));
        
        $e = array();
        $e['name']['foo'] = 'Name error';
        $e['gender']['bar'] = 'Another error';
        $e['invisible']['baz'] = 'Invisible error';
        
        $person->_errors = $e;
        $person->_checked = true;
        
        $pres = $f->fetchPresentation();
        
        $invalid = false;
        if (!$this->assertTrue(strpos($pres, $e['name']['foo']) !== false)) $invalid = true;
        if (!$this->assertTrue(strpos($pres, $e['gender']['bar']) !== false)) $invalid = true;
        if (!$this->assertTrue(strpos($pres, $e['invisible']['baz']) !== false)) $invalid = true;
        if ($invalid) var_dump($pres);

        $el = $f->getControl('errorList');
        $el->hideErrorsShownByOtherControls = true;
        $ee = Ac_Util::implode_r('\n', $el->getAllErrors());
        $this->assertTrue(strpos($ee, $e['name']['foo']) === false);
        $this->assertTrue(strpos($ee, $e['gender']['bar']) === false);
        $this->assertTrue(strpos($ee, $e['invisible']['baz']) !== false);
        
        $el->showErrorsInMainArea = false;
        $this->assertTrue(!!$el->getErrors());
        $this->assertTrue(!$el->getValue());
        
        $el->showErrorsInMainArea = true;
        $this->assertTrue(!$el->getErrors());
        $this->assertTrue(!!$el->getValue());
        
        $person->_errors = array();
        
        $pres = $f->fetchPresentation(true);
        
        $invalid = false;
        $issue = 'fetchPresentation(true) should return updated controls';
        if (!$this->assertTrue(strpos($pres, $e['name']['foo']) === false, $issue)) $invalid = true;
        if (!$this->assertTrue(strpos($pres, $e['gender']['bar']) === false, $issue)) $invalid = true;
        if (!$this->assertTrue(strpos($pres, $e['invisible']['baz']) === false, $issue)) $invalid = true;
        if (!$this->assertTrue(strpos($pres, 'ErrorList') === false)) $invalid = true;
        if ($invalid) var_dump($pres);
        
        $person->_checked = true;
    }
    
    /**
     * @return Ac_Form
     */
    function createTestForm1(array $prototypeExtra = array()) {
        $f1 = new Ac_Form(
            null,
            Ac_Util::m(array(
                'controls' => array(
                    'tabs' => array(
                        'class' => 'Ac_Form_Control_Tabs',
                        'caption' => '',
                        'model' => null,
                        'controls' => array(
                            'general' => array(
                            ),
                            'intimate' => array(
                            ),
                        ),
                    ),
                    'name' => array(
                        'class' => 'Ac_Form_Control_Text',
                        'displayParent' => '/tabs/general',
                    ),
                    'gender' => array(
                        'class' => 'Ac_Form_Control_List',
                        'displayParent' => '../tabs/intimate',
                    ),
                    'birthDate' => array(
                        'class' => 'Ac_Form_Control_Date',
                        'displayParent' => '/tabs/general',
                    ),
                    'religionId' => array(
                        'class' => 'Ac_Form_Control_List',
                        'displayParent' => '../tabs/intimate',
                        'displayOrder' => 2,
                    ),
                    'isSingle' => array(
                        'class' => 'Ac_Form_Control_Toggle',
                        'displayParent' => '../tabs/intimate',
                        'displayOrder' => 1,
                    ),
                    'submit' => array(
                        'class' => 'Ac_Form_Control_Button',
                    ),
                )
            ), $prototypeExtra)
        );
        return $f1;
    }
    
    function _testFormTr() {
        $tf1 = $this->createTestForm1();
        $trs = new Tr_Scanner(new Tr_Forms_Scanner);
        $root = $trs->scan($tf1);
        $iter = new RecursiveTreeIterator($root);
        echo "\n<br />".$root;
        foreach ($iter as $foo) echo "\n<br />".$foo;
        //$iter = new RecursiveTreeIterator();
        //foreach ($iter as $foo) var_dump($foo);
        
    }
    
}