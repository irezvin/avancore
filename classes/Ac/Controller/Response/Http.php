<?php

class Ac_Controller_Response_Http extends Ac_Controller_Response {
    
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
    
    /**
     * Finds and returns last HTTP code explicitly added by addExtraHeader call
     * or FALSE is nothing is found
     * 
     * @return number|boolean
     */
    function getHttpCode() {
        $res = false;
        foreach ($this->extraHeaders as $h) {
            if (is_array($h) && isset($h[1])) {
                $res = $h[1];
            }
        }
        return $res;
    }
    
}

