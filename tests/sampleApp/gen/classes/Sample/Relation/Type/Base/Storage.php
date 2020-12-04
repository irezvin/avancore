<?php

class Sample_Relation_Type_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__relation_types'; 

    var $recordClass = 'Sample_Relation_Type'; 

    var $primaryKey = 'relationTypeId'; 

    var $defaults = [
            'relationTypeId' => NULL,
            'title' => NULL,
            'isSymmetrical' => 0,
        ]; 

    var $autoincFieldName = 'relationTypeId'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'relationTypeId',
    ],
]; 
 
    
}

