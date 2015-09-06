<?php

interface Ac_I_Search_Criterion_Order extends Ac_I_Search_Criterion {

    function __invoke($record1, $record2);
    
}