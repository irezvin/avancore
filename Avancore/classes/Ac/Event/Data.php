<?php

class Ac_Event_Data {
    
    protected $issuer;
    
    protected $event;
    
    protected $arguments;
    
    function __construct($issuer, $event, array $arguments = array()) {
        $this->issuer = $issuer;
        $this->event = $event;
        $this->arguments = $arguments;
    }
    
    function getIssuer() {
        return $this->issuer;
    }
    
    function getEvent() {
        return $this->event;
    }
    
    function getArguments() {
        return $this->arguments;
    }
    
}