<?php

interface Ac_I_Search_Criterion_Sort {

    function __invoke($record1, $record2);
    
}