<?php

class Sample_Shop_Product_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_products'; 

    var $recordClass = 'Sample_Shop_Product'; 

    var $primaryKey = 'id'; 

    var $defaults = array (
            'id' => NULL,
            'sku' => '',
            'title' => '',
            'metaId' => NULL,
            'pubId' => NULL,
        ); 

    var $nullableColumns = array ( 0 => 'metaId', 1 => 'pubId', ); 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'id', ), 'idxPubId' => array ( 0 => 'pubId', ), ); 
 
    
}

