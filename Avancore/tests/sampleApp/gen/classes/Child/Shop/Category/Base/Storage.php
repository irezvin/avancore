<?php

class Child_Shop_Category_Base_Storage extends Sample_Shop_Category_Storage {

    var $recordClass = 'Child_Shop_Category'; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, array (
                'parts' => array (
                    'shopProductIds' => array (
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'categoryId',
                        'midDestKey' => 'productId',
                        'tableKey' => 'id',
                        'midTableAlias' => 'mid__shopProducts',
                    ),
                ),
            ) 
        );
        return $res;
    }
    
}

