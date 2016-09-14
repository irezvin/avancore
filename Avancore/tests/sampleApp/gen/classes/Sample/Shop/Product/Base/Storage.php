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
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, array (
                'parts' => array (
                    'shopCategoryIds' => array (
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'productId',
                        'midDestKey' => 'categoryId',
                        'tableKey' => 'id',
                        'midTableAlias' => 'mid__shopCategories',
                    ),
                    'referencedShopProductIds' => array (
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'productId',
                        'midDestKey' => 'relatedProductId',
                        'tableKey' => 'id',
                        'midTableAlias' => 'mid__referencedShopProducts',
                    ),
                    'referencingShopProductIds' => array (
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'relatedProductId',
                        'midDestKey' => 'productId',
                        'tableKey' => 'id',
                        'midTableAlias' => 'mid__referencingShopProducts',
                    ),
                ),
            ) 
        );
        return $res;
    }
    
}

