<?php

class Sample_Shop_Category_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__shop_categories'; 

    var $recordClass = 'Sample_Shop_Category'; 

    var $primaryKey = 'id'; 

    var $defaults = [
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
        ]; 

    var $nullableColumns = [ 0 => 'title', 1 => 'parentId', 2 => 'metaId', 3 => 'pubId', ]; 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'id',
    ],
    'idxPubId' => [
        0 => 'pubId',
    ],
]; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, [
                'parts' => [
                    'shopProductIds' => [
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'categoryId',
                        'midDestKey' => 'productId',
                        'tableKey' => 'id',
                        'midTableAlias' => 'mid__shopProducts',
                    ],
                ],
            ] 
        );
        return $res;
    }
    
}

