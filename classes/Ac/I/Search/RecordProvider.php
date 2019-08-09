<?php

interface Ac_I_Search_RecordProvider {
    
    function find(array $query = array(), $keysToList = true, $sort = false, $limit = false, $offset = false, & $remainingQuery = array(), & $sorted = false);
    
}