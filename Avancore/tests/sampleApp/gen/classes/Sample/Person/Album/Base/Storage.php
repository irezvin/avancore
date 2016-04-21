<?php

class Sample_Person_Album_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__person_albums'; 

    var $recordClass = 'Sample_Person_Album'; 

    var $primaryKey = 'albumId'; 

    var $defaults = array (
            'albumId' => NULL,
            'personId' => '0',
            'albumName' => '\'\'',
        ); 

    var $autoincFieldName = 'albumId'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'albumId', ), ); 
 
    
}

