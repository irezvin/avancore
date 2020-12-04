<?php

class Child_Shop_Product_Base_Storage extends Sample_Shop_Product_Storage {

    var $recordClass = 'Child_Shop_Product'; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, [
                'parts' => [
                    'shopCategoryIds' => [
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'productId',
                        'midDestKey' => 'categoryId',
                        'tableKey' => 'id',
                        'midTableAlias' => 'mid__shopCategories',
                    ],
                    'referencedShopProductIds' => [
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'productId',
                        'midDestKey' => 'relatedProductId',
                        'tableKey' => 'id',
                        'midTableAlias' => 'mid__referencedShopProducts',
                    ],
                    'referencingShopProductIds' => [
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'relatedProductId',
                        'midDestKey' => 'productId',
                        'tableKey' => 'id',
                        'midTableAlias' => 'mid__referencingShopProducts',
                    ],
                ],
            ] 
        );
        return $res;
    }
    
}

