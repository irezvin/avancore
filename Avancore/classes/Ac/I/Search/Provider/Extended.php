<?php

interface Ac_I_Search_Provider_Extended extends Ac_I_SearchProvider {
 
    function findRecords (array $query = array(), $orderBy = false, $limit = false, $offset = false);
    
    function findFirstRecord (array $query = array(), $orderBy = false);
    
    function findSingleRecord (array $query = array(), $orderBy = false);
    
    function countRecords (array $query = array());
    
}