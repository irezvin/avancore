<?php

class Sample_Person_Album_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__person_albums'; 

    var $recordClass = 'Sample_Person_Album'; 

    var $primaryKey = 'albumId'; 

    var $defaults = [
            'albumId' => NULL,
            'personId' => 0,
            'albumName' => '\'\'',
        ]; 

    var $autoincFieldName = 'albumId'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'albumId',
    ],
]; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, [
                'parts' => [
                    'personPhotoIds' => [
                        'class' => 'Ac_Sql_Filter_NNCriterion_Omni',
                        'midSrcKeys' => [
                            0 => 'personId',
                            1 => 'albumId',
                        ],
                        'midDestKeys' => [
                            0 => 'personId',
                            1 => 'photoId',
                        ],
                        'tableKeys' => [
                            0 => 'personId',
                            1 => 'photoId',
                        ],
                        'midTableAlias' => 'mid__personPhotos',
                    ],
                ],
            ] 
        );
        return $res;
    }
    
}

