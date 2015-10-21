<?php

interface Ac_I_Search_FilterProvider {
    
    const IDENTIFIER_CRITERION = '_peIdentifier';
    
    function filter(array $records, array $query = array(), $sort = false, $limit = false, $offset = false,  & $remainingQuery = true, & $sorted = false, $areByIds = false);
    
}