<?php

class Sample_Shop_Spec_Food_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_spec_food'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $primaryKey = 'productId'; 

    var $defaults = [
            'productId' => NULL,
            'storageType' => 'shelfStable',
            'storageTerm' => 0,
            'storageTermUnit' => 'days',
        ]; 

    var $nullableColumns = [ 0 => 'storageType', ]; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'productId',
    ],
]; 
 
    
}

