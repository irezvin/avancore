<?php

class Ac_Event_Data {
    
    protected $event;
    
    protected $issuer;
    
    protected $arguments;
    
    function __construct($event, $issuer, array $arguments = array()) {
        $this->event = $event;
        $this->issuer = $issuer;
        $this->arguments = $arguments;
    }
    
    function getEvent() {
        return $this->event;
    }
    
    function getIssuer() {
        return $this->issuer;
    }
    
    function getArguments() {
        return $this->arguments;
    }
    
}