<?php

class Ac_Controller_Output_Debug extends Ac_Controller_Output_Native {

    var $resOutput = '';
    var $resHeaders = array();
    var $resExit = false;    
    var $resBufferingCancelled = false;
    
    var $headersSent = false;
    
    function outputResponse(Ac_Controller_Response_Html $r, $asModule = false) {
        $this->resOutput = '';
        $this->resHeaders = array();
        $this->resExit = false;    
        $this->resBufferingCancelled = false;
        ob_start();
        parent::outputResponse($r, $asModule);
        $this->resOutput = ob_get_clean();
    }
    
    function headersSent() {
        return $this->headersSent;
    }
    
    function header($header, $replace = true, $httpResponseCode = null) {
        $this->resHeaders[] = array($header, $replace, $httpResponseCode);
    }
    
    function cancelBuffering() {
        $this->resBufferingCancelled = true;
    }
    
    function exitPhp() {
        $this->resExit = true;
    }
    
    
}

