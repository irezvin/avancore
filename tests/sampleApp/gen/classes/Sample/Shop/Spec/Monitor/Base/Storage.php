<?php

class Sample_Shop_Spec_Monitor_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_spec_monitor'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $primaryKey = 'productId'; 

    var $defaults = array (
            'productId' => NULL,
            'diagonal' => NULL,
            'hRes' => NULL,
            'vRes' => NULL,
            'matrixTypeId' => NULL,
        ); 

    var $nullableColumns = array ( 0 => 'matrixTypeId', ); 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'productId', ), ); 
 
    
}

