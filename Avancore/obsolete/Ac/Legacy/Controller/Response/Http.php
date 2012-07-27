<?php

class Ac_Legacy_Controller_Response_Http extends Ac_Legacy_Controller_Response {
    
    const redirMultipleChoices = '300';
    
    const redirPermanent = '301';
    
    const redirFound = '302';
    
    const redirSeeOther = '303';
    
    const redirTemporary = '307';
    
    var $contentType = false;
    
    var $expire = false;
    
    var $extraHeaders = array();
    
    var $redirectUrl = false;
    
    var $redirectType = false;
    
    function addExtraHeader($header, $httpCode = false) {
        if ($httpCode === false) $this->extraHeaders[] = $header;
            else $this->extraHeaders[] = array($header, $httpCode);
    }
    
}

?>