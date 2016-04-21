<?php

class Sample_Shop_Category_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_categories'; 

    var $recordClass = 'Sample_Shop_Category'; 

    var $primaryKey = 'id'; 

    var $defaults = array (
            'id' => NULL,
            'title' => NULL,
            'leftCol' => NULL,
            'rightCol' => NULL,
            'ignore' => NULL,
            'parentId' => NULL,
            'ordering' => NULL,
            'depth' => NULL,
            'metaId' => NULL,
            'pubId' => NULL,
        ); 

    var $nullableColumns = array ( 0 => 'title', 1 => 'parentId', 2 => 'metaId', 3 => 'pubId', ); 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'id', ), 'idxPubId' => array ( 0 => 'pubId', ), ); 
 
    
}

