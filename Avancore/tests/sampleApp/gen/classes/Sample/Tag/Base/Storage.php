<?php

class Sample_Tag_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__tags'; 

    var $recordClass = 'Sample_Tag'; 

    var $primaryKey = 'tagId'; 

    var $defaults = array (
            'tagId' => NULL,
            'title' => NULL,
            'titleM' => NULL,
            'titleF' => NULL,
        ); 

    var $nullableColumns = array ( 0 => 'titleM', 1 => 'titleF', ); 

    var $autoincFieldName = 'tagId'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'tagId', ), 'Index_2' => array ( 0 => 'title', ), ); 
 
    
}

