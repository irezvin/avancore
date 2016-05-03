<?php

class Child_Shop_Product_Base_Storage extends Sample_Shop_Product_Storage {

    var $recordClass = 'Child_Shop_Product'; 
 
 
    
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

