<?php

class Child_Person_Album_Base_Storage extends Sample_Person_Album_Storage {

    var $recordClass = 'Child_Person_Album'; 
 
 
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = parent::doGetSqlSelectPrototype($primaryAlias);
        Ac_Util::ms($res, array (
                'parts' => array (
                    'personPhotoIds' => array (
                        'class' => 'Ac_Sql_Filter_NNCriterion_Omni',
                        'midSrcKeys' => array (
                            0 => 'personId',
                            1 => 'albumId',
                        ),
                        'midDestKeys' => array (
                            0 => 'personId',
                            1 => 'photoId',
                        ),
                        'tableKeys' => array (
                            0 => 'personId',
                            1 => 'photoId',
                        ),
                        'midTableAlias' => 'mid__personPhotos',
                    ),
                ),
            ) 
        );
        return $res;
    }
    
}

