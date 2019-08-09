<?php

interface Ac_I_Search_Criterion_ExtendedSort extends Ac_I_Search_Criterion_Sort {
    
    function sort(array $records);
    
}