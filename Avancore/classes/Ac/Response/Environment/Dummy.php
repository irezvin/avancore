<?php

class Ac_Response_Environment_Dummy implements Ac_I_Response_Environment {

    var $bufferingCancelled = false;
    
    var $headers = null;
    
    var $cookies = null;
    
    var $sessionData = null;
    
    var $sessionDestroyed = false;
    
    var $responseText = '';
    
    var $outputFinished = false;
    
    function begin() {
        foreach (get_class_vars(get_class($this)) as $k => $v) 
            $this->$k = $v;
    }
    
    function cancelBuffering() {
        $this->bufferingCancelled = false;
    }
    
    function acceptHeaders(array $headers) {
        $this->headers = $headers;
    }
    
    function acceptCookies(array $cookies) {
        $this->cookies = $cookies;
    }
    
    function acceptSessionData(array $sessionData) {
        $this->sessionData = $sessionData;
    }
    
    function destroySession() {
        $this->sessionDestroyed = true;
    }
    
    function acceptResponseText($text) {
        $this->responseText = $text;
    }

    function finishOutput() {
        $this->outputFinished = true;
    }
    
}