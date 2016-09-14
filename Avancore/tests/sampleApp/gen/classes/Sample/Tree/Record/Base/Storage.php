<?php

class Sample_Tree_Record_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__tree_records'; 

    var $recordClass = 'Sample_Tree_Record'; 

    var $primaryKey = 'id'; 

    var $defaults = array (
            'id' => NULL,
            'title' => '',
            'tag' => NULL,
        ); 

    var $nullableColumns = array ( 0 => 'tag', ); 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'id', ), ); 
 
    
}

