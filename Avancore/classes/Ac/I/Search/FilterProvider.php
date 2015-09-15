<?php

interface Ac_I_Search_FilterProvider {
    
    function filter(array $records, array $query = array(), $sort = false, $limit = false, $offset = false, & $remainingQuery, & $sorted);
    
}