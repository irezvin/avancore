<?php

class Sample_Perk_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__perks'; 

    var $recordClass = 'Sample_Perk'; 

    var $primaryKey = 'perkId'; 

    var $defaults = array (
            'perkId' => NULL,
            'name' => '',
        ); 

    var $nullableColumns = array ( 0 => 'name', ); 

    var $autoincFieldName = 'perkId'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'perkId', ), ); 
 
    
}

