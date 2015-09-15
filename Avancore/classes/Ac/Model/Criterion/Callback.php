<?php

class Ac_Model_Criterion_Callback implements Ac_I_Search_Criterion_Search {
    
    protected $callback = false;
    
    function __construct($callback) {
        $this->callback = $callback;
    }
    
    function getCallback() {
        return $this->callback;
    }
    
    function __invoke($record) {
        $c = $this->callback;
        return $c($record);
    }
    
    function test($record, $name, $value, $adHoc) {
        return $this->__invoke($record);
    }
    
}