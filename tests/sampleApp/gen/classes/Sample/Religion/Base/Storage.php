<?php

class Sample_Religion_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__religion'; 

    var $recordClass = 'Sample_Religion'; 

    var $primaryKey = 'religionId'; 

    var $defaults = [
            'religionId' => NULL,
            'title' => NULL,
        ]; 

    var $autoincFieldName = 'religionId'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'religionId',
    ],
    'Index_2' => [
        0 => 'title',
    ],
]; 
 
    
}

