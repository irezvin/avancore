<?php

class Ac_Test_Form extends Ac_Test_Base {
    
    protected $bootSampleApp = true;
    
    function testErrorList () {
        $person = Sample::getInstance()->getSamplePersonMapper()->factory();
        $f = new Ac_Form(null, array(
            'controls' => array(
                'errorList' => array(
                    'class' => 'Ac_Form_Control_ErrorList',
                    'hideErrorsShownByOtherControls' => false,
                ),
                'name' => array('class' => 'Ac_Form_Control_Text'),
                'gender' => array('class' => 'Ac_Form_Control_List'),
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
        if (!$this->assertTrue(strpos($pres, $e['name']['foo']) === false)) $invalid = true;
        if (!$this->assertTrue(strpos($pres, $e['gender']['bar']) === false)) $invalid = true;
        if (!$this->assertTrue(strpos($pres, $e['invisible']['baz']) === false)) $invalid = true;
        if (!$this->assertTrue(strpos($pres, 'ErrorList') === false)) $invalid = true;
        if ($invalid) var_dump($pres);
        
        $person->_checked = true;
    }
    
    function testCreateForm() {
        $p = Sample::getInstance()->getSamplePersonMapper()->factory();
        $tf = $this->createTestForm1(array('model' => $p));
        $ctx = $tf->getContext();
        $ctx->updateData(array('name' => 'Name that user has entered'));
        var_dump($tf->getControl('name')->getValue());
        $p->name = 'New name';
        var_dump($tf->getControl('name')->getValue());
        $tf->updateFromModel();
        var_dump($tf->getControl('name')->getValue());
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
                    'sexualOrientationId' => array(
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
    
}