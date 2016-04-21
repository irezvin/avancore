<?php

class Sample_Religion_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__religion'; 

    var $recordClass = 'Sample_Religion'; 

    var $primaryKey = 'religionId'; 

    var $defaults = array (
            'religionId' => NULL,
            'title' => NULL,
        ); 

    var $autoincFieldName = 'religionId'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'religionId', ), 'Index_2' => array ( 0 => 'title', ), ); 
 
    
}

