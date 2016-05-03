<?php

class Child_Tag_Base_Storage extends Sample_Tag_Storage {

    var $recordClass = 'Child_Tag'; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, array (
                'parts' => array (
                    'personIds' => array (
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'idOfTag',
                        'midDestKey' => 'idOfPerson',
                        'tableKey' => 'personId',
                        'midTableAlias' => 'mid__people',
                    ),
                    'perkIds' => array (
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'idOfTag',
                        'midDestKey' => 'idOfPerk',
                        'tableKey' => 'perkId',
                        'midTableAlias' => 'mid__perks',
                    ),
                ),
            ) 
        );
        return $res;
    }
    
}

