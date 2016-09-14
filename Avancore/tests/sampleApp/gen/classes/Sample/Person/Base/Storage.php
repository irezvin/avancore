<?php

class Sample_Person_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__people'; 

    var $recordClass = 'Sample_Person'; 

    var $primaryKey = 'personId'; 

    var $defaults = array (
            'personId' => NULL,
            'name' => NULL,
            'gender' => 'F',
            'isSingle' => '1',
            'birthDate' => NULL,
            'lastUpdatedDatetime' => NULL,
            'createdTs' => 'CURRENT_TIMESTAMP',
            'religionId' => NULL,
            'portraitId' => NULL,
        ); 

    var $nullableColumns = array ( 0 => 'lastUpdatedDatetime', 1 => 'religionId', 2 => 'portraitId', ); 

    var $autoincFieldName = 'personId'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'personId', ), ); 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, array (
                'parts' => array (
                    'tagIds' => array (
                        'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                        'midSrcKey' => 'idOfPerson',
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

