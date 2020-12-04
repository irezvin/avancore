<?php

class Sample_Perk_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__perks'; 

    var $recordClass = 'Sample_Perk'; 

    var $primaryKey = 'perkId'; 

    var $defaults = [
            'perkId' => NULL,
            'name' => '',
        ]; 

    var $nullableColumns = [ 0 => 'name', ]; 

    var $autoincFieldName = 'perkId'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'perkId',
    ],
]; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, [
                'parts' => [
                    'tagIds' => [
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'idOfPerk',
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

