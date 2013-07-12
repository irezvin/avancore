<?php

abstract class Ac_Cr_Result {

    protected $methodReturnValue = null;

    protected $methodOutput = false;
    
    function setMethodReturnValue($methodReturnValue) {
        $this->methodReturnValue = $methodReturnValue;
    }

    function getMethodReturnValue() {
        return $this->methodReturnValue;
    }

    function setMethodOutput($methodOutput) {
        $this->methodOutput = $methodOutput;
    }

    function getMethodOutput() {
        return $this->methodOutput;
    }    
    
    abstract function getResponse();
    
}