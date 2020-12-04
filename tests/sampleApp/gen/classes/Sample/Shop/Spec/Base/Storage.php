<?php

class Sample_Shop_Spec_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_specs'; 

    var $recordClass = 'Sample_Shop_Spec'; 

    var $primaryKey = 'productId'; 

    var $defaults = [
            'productId' => NULL,
            'detailsUrl' => '',
            'specsType' => '',
        ]; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'productId',
    ],
]; 
 
    
}

