<?php

class Sample_Shop_Product_Extra_Code_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_product_extraCodes'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $primaryKey = 'productId'; 

    var $defaults = array (
            'productId' => NULL,
            'ean' => '',
            'asin' => '',
            'gtin' => '',
            'responsiblePersonId' => NULL,
        ); 

    var $nullableColumns = array ( 0 => 'responsiblePersonId', ); 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'productId', ), ); 
 
    
}

