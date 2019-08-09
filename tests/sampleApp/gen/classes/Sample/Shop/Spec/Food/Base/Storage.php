<?php

class Sample_Shop_Spec_Food_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_spec_food'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $primaryKey = 'productId'; 

    var $defaults = array (
            'productId' => NULL,
            'storageType' => 'shelfStable',
            'storageTerm' => '0',
            'storageTermUnit' => 'days',
        ); 

    var $nullableColumns = array ( 0 => 'storageType', ); 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'productId', ), ); 
 
    
}

