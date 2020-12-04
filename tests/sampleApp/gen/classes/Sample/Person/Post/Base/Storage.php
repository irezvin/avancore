<?php

class Sample_Person_Post_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__person_posts'; 

    var $recordClass = 'Sample_Person_Post'; 

    var $primaryKey = 'id'; 

    var $defaults = [
            'id' => NULL,
            'personId' => NULL,
            'photoId' => NULL,
            'title' => '',
            'content' => '',
            'pubId' => NULL,
        ]; 

    var $nullableColumns = [ 0 => 'personId', 1 => 'photoId', 2 => 'title', 3 => 'content', 4 => 'pubId', ]; 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'id',
    ],
    'idxPubId' => [
        0 => 'pubId',
    ],
]; 
 
    
}

