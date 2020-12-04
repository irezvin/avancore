<?php

class Sample_Relation_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__relations'; 

    var $recordClass = 'Sample_Relation'; 

    var $primaryKey = 'relationId'; 

    var $defaults = [
            'relationId' => NULL,
            'personId' => NULL,
            'otherPersonId' => NULL,
            'relationTypeId' => NULL,
            'relationBegin' => NULL,
            'relationEnd' => NULL,
            'notes' => '',
        ]; 

    var $nullableColumns = [ 0 => 'relationBegin', 1 => 'relationEnd', ]; 

    var $autoincFieldName = 'relationId'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'relationId',
    ],
]; 
 
    
}

