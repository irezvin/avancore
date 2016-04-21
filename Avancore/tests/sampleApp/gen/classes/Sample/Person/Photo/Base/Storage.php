<?php

class Sample_Person_Photo_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__person_photos'; 

    var $recordClass = 'Sample_Person_Photo'; 

    var $primaryKey = 'photoId'; 

    var $defaults = array (
            'photoId' => NULL,
            'personId' => NULL,
            'filename' => '',
        ); 

    var $autoincFieldName = 'photoId'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'photoId', ), ); 
 
    
}

