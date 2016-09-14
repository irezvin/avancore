<?php

class Sample_Tree_Combo_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__tree_combos'; 

    var $recordClass = 'Sample_Tree_Combo'; 

    var $primaryKey = 'id'; 

    var $defaults = array (
            'id' => NULL,
            'leftCol' => '0',
            'rightCol' => '1',
            'parentId' => NULL,
            'ordering' => '0',
            'title' => '',
            'tag' => NULL,
            'ignore' => '0',
            'depth' => '0',
        ); 

    var $nullableColumns = array ( 0 => 'parentId', 1 => 'tag', ); 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'id', ), ); 
 
    
}

