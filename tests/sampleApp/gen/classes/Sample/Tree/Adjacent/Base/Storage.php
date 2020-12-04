<?php

class Sample_Tree_Adjacent_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__tree_adjacent'; 

    var $recordClass = 'Sample_Tree_Adjacent'; 

    var $primaryKey = 'id'; 

    var $defaults = [
            'id' => NULL,
            'parentId' => NULL,
            'ordering' => 0,
            'title' => '',
            'tag' => NULL,
        ]; 

    var $nullableColumns = [ 0 => 'parentId', 1 => 'tag', ]; 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'id',
    ],
]; 
 
    
}

