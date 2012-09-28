<?php

interface Ac_I_RightMergeable {
    
    function isRightMergeableWith($value);
    
    function rightMergeWith($value, $preserveExistingValues = false);
    
}