<?php

class Sample_SecondCon extends Ac_Legacy_Controller {
    
    var $_templateClass = 'Sample_SecondCon_Template';
 
    /**
     * @var Ac_UrlMapper_UrlMapper
     */
    function createUrlMapper() {
        return new Ac_UrlMapper_UrlMapper(array('patterns' => array(
            array('const' => array('action' => null), 'definition' => '/'),
            "/{?'action'otherMethod}/{?'argument'[0-9]+}/{?c}",
        )));
    }
    
    function execute() {
        $this->_templatePart = 'default';
    }
    
    function executeOtherMethod() {
        $this->_templatePart = 'otherMethod';
    }
    
}