<?php

class Sample_Shop_Spec_Computer_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_spec_computer'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $primaryKey = 'productId'; 

    var $defaults = array (
            'productId' => NULL,
            'hdd' => NULL,
            'ram' => NULL,
            'os' => '',
        ); 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'productId', ), ); 
 
    
}

