<?php

class Child_Person_Photo_Base_Storage extends Sample_Person_Photo_Storage {

    var $recordClass = 'Child_Person_Photo'; 
 
 
    
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

