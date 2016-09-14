<?php

class Sample_Person_Post_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__person_posts'; 

    var $recordClass = 'Sample_Person_Post'; 

    var $primaryKey = 'id'; 

    var $defaults = array (
            'id' => NULL,
            'personId' => NULL,
            'photoId' => NULL,
            'title' => '',
            'content' => '',
            'pubId' => NULL,
        ); 

    var $nullableColumns = array ( 0 => 'personId', 1 => 'photoId', 2 => 'title', 3 => 'content', 4 => 'pubId', ); 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'id', ), 'idxPubId' => array ( 0 => 'pubId', ), ); 
 
    
}

