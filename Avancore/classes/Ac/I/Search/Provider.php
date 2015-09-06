<?php

interface Ac_I_Search_Provider {
    
    /**
     * @return Ac_I_SearchResult
     */
    function search(array $query = array(), $orderBy = false, $limit = false, $offset = false);
    
}