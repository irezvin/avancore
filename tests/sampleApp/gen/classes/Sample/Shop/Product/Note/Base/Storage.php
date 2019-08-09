<?php

class Sample_Shop_Product_Note_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_product_notes'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $primaryKey = 'productId'; 

    var $defaults = array (
            'productId' => NULL,
            'note' => '',
            'noteAuthorId' => NULL,
        ); 

    var $nullableColumns = array ( 0 => 'noteAuthorId', ); 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'productId', ), ); 
 
    
}

