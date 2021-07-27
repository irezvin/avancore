<?php

class Sample_FirstCon extends Ac_Controller {
    
    var $_templateClass = 'Sample_FirstCon_Template';
 
    function execute() {
        $this->_templatePart = 'defaultMethod';
    }
    
    function executeOtherMethod($argument) {
        $this->_templatePart = 'otherMethod';
    }
    
    function executeOneArg($onlyArg) {
        echo "executeOneArg: onlyArg: ".$onlyArg;
    }
    
    function executeTwoArgsOneDefault($firstArg, $secondArg = 'defValue') {
        echo "executeTwoArgsOneDefault: firstArg: {$firstArg}, secondArg: {$secondArg}";
    }
    
}