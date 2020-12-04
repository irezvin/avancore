<?php

class Sample_Shop_Spec_Monitor_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_spec_monitor'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $primaryKey = 'productId'; 

    var $defaults = [
            'productId' => NULL,
            'diagonal' => NULL,
            'hRes' => NULL,
            'vRes' => NULL,
            'matrixTypeId' => NULL,
        ]; 

    var $nullableColumns = [ 0 => 'matrixTypeId', ]; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'productId',
    ],
]; 
 
    
}

