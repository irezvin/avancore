<?php

class Child_Perk_Base_Storage extends Sample_Perk_Storage {

    var $recordClass = 'Child_Perk'; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, array (
                'parts' => array (
                    'tagIds' => array (
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'idOfPerk',
                        'midDestKey' => 'idOfTag',
                        'tableKey' => 'tagId',
                        'midTableAlias' => 'mid__tags',
                    ),
                ),
            ) 
        );
        return $res;
    }
    
}

