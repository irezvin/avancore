<?php

class Ac_Result_Http extends Ac_Result_Http_Abstract {
    
    protected $statusCode = false;
    
    protected $reasonPhrase = false;
    
    function setStatusCode($statusCode, $reasonPhrase = false) {
        $this->statusCode = $statusCode;
        if ($reasonPhrase !== false) $this->reasonPhrase = $reasonPhrase;
    }

    function getStatusCode() {
        return $this->statusCode;
    }

    function setReasonPhrase($reasonPhrase) {
        $this->reasonPhrase = $reasonPhrase;
    }

    function getReasonPhrase() {
        return $this->reasonPhrase;
    }
    
}