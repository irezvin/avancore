<?php

interface Ac_I_Mergeable {
    
    function isMergeableWith($value);
    
    function mergeWith($value, $preserveExistingValues = false);
    
}