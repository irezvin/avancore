<?php

class Sample_Shop_Classifier_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_classifier'; 

    var $recordClass = 'Sample_Shop_Classifier'; 

    var $primaryKey = 'id'; 

    var $defaults = array (
            'id' => NULL,
            'title' => '',
            'type' => NULL,
        ); 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'id', ), 'type_title' => array ( 0 => 'type', 1 => 'title', ), ); 
 
    
}

