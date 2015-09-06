<?php

interface Ac_I_Search_Criterion_Filter extends Ac_I_Search_Criterion {
    
    function test($record, $name, $value, $adHoc);
    
}