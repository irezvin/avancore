<?php

class Sample_Shop_Classifier_Type_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_classifier_type'; 

    var $recordClass = 'Sample_Shop_Classifier_Type'; 

    var $primaryKey = 'type'; 

    var $defaults = [
            'type' => NULL,
        ]; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'type',
    ],
]; 
 
    
}

