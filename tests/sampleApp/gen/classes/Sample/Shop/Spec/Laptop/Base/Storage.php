<?php

class Sample_Shop_Spec_Laptop_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_spec_laptop'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $primaryKey = 'productId'; 

    var $defaults = [
            'productId' => NULL,
            'weight' => NULL,
            'battery' => '',
        ]; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'productId',
    ],
]; 
 
    
}

