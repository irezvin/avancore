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
    
}