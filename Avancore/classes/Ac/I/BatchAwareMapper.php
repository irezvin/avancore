<?php

interface Ac_I_BatchAwareMapper {
    
    function beginBatchChange(array $fieldList = array());
    
    function endBatchChange();
    
}