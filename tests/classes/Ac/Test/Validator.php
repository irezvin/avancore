<?php

class Ac_Test_Validator extends Ac_Test_Base {
    
    function testValidators() {
    
        ini_set('error_reporting', E_ALL);
        $v = new Ac_Validator_AbstractValidator();
        $something = 'Something';
        $this->assertTrue($v('Foobar', $something));
        $this->assertEqual($something, null, 'Last error NULLed (%s)');
        
    }
    
    
}

