<?php

class Sample_Relation_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__relations'; 

    var $recordClass = 'Sample_Relation'; 

    var $primaryKey = 'relationId'; 

    var $defaults = array (
            'relationId' => NULL,
            'personId' => NULL,
            'otherPersonId' => NULL,
            'relationTypeId' => NULL,
            'relationBegin' => NULL,
            'relationEnd' => NULL,
            'notes' => '',
        ); 

    var $nullableColumns = array ( 0 => 'relationBegin', 1 => 'relationEnd', ); 

    var $autoincFieldName = 'relationId'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'relationId', ), ); 
 
    
}

