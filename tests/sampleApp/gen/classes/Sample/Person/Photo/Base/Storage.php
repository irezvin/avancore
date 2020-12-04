<?php

class Sample_Person_Photo_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__person_photos'; 

    var $recordClass = 'Sample_Person_Photo'; 

    var $primaryKey = 'photoId'; 

    var $defaults = [
            'photoId' => NULL,
            'personId' => NULL,
            'filename' => '',
        ]; 

    var $autoincFieldName = 'photoId'; 

    var $uniqueIndices = [
    'PRIMARY' => [
        0 => 'photoId',
    ],
]; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, [
                'parts' => [
                    'personAlbumIds' => [
                        'class' => 'Ac_Sql_Filter_NNCriterion_Omni',
                        'midSrcKeys' => [
                            0 => 'personId',
                            1 => 'photoId',
                        ],
                        'midDestKeys' => [
                            0 => 'personId',
                            1 => 'albumId',
                        ],
                        'tableKeys' => [
                            0 => 'personId',
                            1 => 'albumId',
                        ],
                        'midTableAlias' => 'mid__personAlbums',
                    ],
                ],
            ] 
        );
        return $res;
    }
    
}

