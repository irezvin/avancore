<?php

class Sample_Tree_Record_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__tree_records'; 

    var $recordClass = 'Sample_Tree_Record'; 

    var $primaryKey = 'id'; 

    var $defaults = [
            'id' => NULL,
            'title' => '',
            'tag' => NULL,
        ]; 

    var $nullableColumns = [ 0 => 'tag', ]; 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'id',
    ],
]; 
 
    
}

