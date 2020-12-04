<?php

class Sample_Tag_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__tags'; 

    var $recordClass = 'Sample_Tag'; 

    var $primaryKey = 'tagId'; 

    var $defaults = [
            'tagId' => NULL,
            'title' => NULL,
            'titleM' => NULL,
            'titleF' => NULL,
        ]; 

    var $nullableColumns = [ 0 => 'titleM', 1 => 'titleF', ]; 

    var $autoincFieldName = 'tagId'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'tagId',
    ],
    'Index_2' => [
        0 => 'title',
    ],
]; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, [
                'parts' => [
                    'personIds' => [
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'idOfTag',
                        'midDestKey' => 'idOfPerson',
                        'tableKey' => 'personId',
                        'midTableAlias' => 'mid__people',
                    ],
                    'perkIds' => [
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'idOfTag',
                        'midDestKey' => 'idOfPerk',
                        'tableKey' => 'perkId',
                        'midTableAlias' => 'mid__perks',
                    ],
                ],
            ] 
        );
        return $res;
    }
    
}

