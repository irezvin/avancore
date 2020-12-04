<?php

class Child_Person_Album_Base_Storage extends Sample_Person_Album_Storage {

    var $recordClass = 'Child_Person_Album'; 
 
 
    
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

