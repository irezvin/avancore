<?php

interface Ac_I_Search_Criterion_Bulk extends Ac_I_Search_Criterion_Search {
    
    function filter (array $records, $name, $value, $adHoc);
    
}