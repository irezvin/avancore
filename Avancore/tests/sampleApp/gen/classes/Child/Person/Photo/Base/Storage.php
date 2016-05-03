<?php

class Child_Person_Photo_Base_Storage extends Sample_Person_Photo_Storage {

    var $recordClass = 'Child_Person_Photo'; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, array (
                'parts' => array (
                    'personAlbumIds' => array (
                        'class' => 'Ac_Sql_Filter_NNCriterion_Omni',
                        'midSrcKeys' => array (
                            0 => 'personId',
                            1 => 'photoId',
                        ),
                        'midDestKeys' => array (
                            0 => 'personId',
                            1 => 'albumId',
                        ),
                        'tableKeys' => array (
                            0 => 'personId',
                            1 => 'albumId',
                        ),
                        'midTableAlias' => 'mid__personAlbums',
                    ),
                ),
            ) 
        );
        return $res;
    }
    
}

