<?php

class Sample_Perk_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__perks'; 

    var $recordClass = 'Sample_Perk'; 

    var $primaryKey = 'perkId'; 

    var $defaults = array (
            'perkId' => NULL,
            'name' => '',
        ); 

    var $nullableColumns = array ( 0 => 'name', ); 

    var $autoincFieldName = 'perkId'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'perkId', ), ); 
 
 
    
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

