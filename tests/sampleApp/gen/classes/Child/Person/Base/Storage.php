<?php

class Child_Person_Base_Storage extends Sample_Person_Storage {

    var $recordClass = 'Child_Person'; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, [
                'parts' => [
                    'tagIds' => [
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'idOfPerson',
                        'midDestKey' => 'idOfTag',
                        'tableKey' => 'tagId',
                        'midTableAlias' => 'mid__tags',
                    ],
                ],
            ] 
        );
        return $res;
    }
    
}

