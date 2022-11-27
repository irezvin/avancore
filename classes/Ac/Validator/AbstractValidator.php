<?php

class Ac_Validator_AbstractValidator extends Ac_Decorator {
    
    protected $lastError = false;
    
    function getError($value = null) {
        if (!func_num_args()) {
            return $this->lastError;
        }
        $this->lastError = $this->doGetError($value);
        return $this->lastError;
    }
    
    function apply($value, & $error = null) {
        $res = !$this->getError($value);
        $error = $this->lastError;
        return $res;
    }
    
    function __invoke($value, & $error = null) {
        return $this->apply($value, $error);
    }
    
    protected function doGetError($value) {
    }
    
}