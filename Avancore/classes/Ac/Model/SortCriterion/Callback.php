<?php

class Ac_Model_SortCriterion_Callback implements Ac_I_Search_Criterion_Sort {
    
    protected $callback = false;
    
    function __construct($callback) {
        $this->callback = $callback;
    }
    
    function getCallback() {
        return $this->callback;
    }
    
    function __invoke($record1, $record2) {
        $c = $this->callback;
        return $c($record1, $record2);
    }
    
}