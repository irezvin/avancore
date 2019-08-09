<?php

class Sample_Person_Photo_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__person_photos'; 

    var $recordClass = 'Sample_Person_Photo'; 

    var $primaryKey = 'photoId'; 

    var $defaults = array (
            'photoId' => NULL,
            'personId' => NULL,
            'filename' => '',
        ); 

    var $autoincFieldName = 'photoId'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'photoId', ), ); 
 
 
    
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

