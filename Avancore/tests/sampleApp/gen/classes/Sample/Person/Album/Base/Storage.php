<?php

class Sample_Person_Album_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__person_albums'; 

    var $recordClass = 'Sample_Person_Album'; 

    var $primaryKey = 'albumId'; 

    var $defaults = array (
            'albumId' => NULL,
            'personId' => '0',
            'albumName' => '\'\'',
        ); 

    var $autoincFieldName = 'albumId'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'albumId', ), ); 
 
 
    
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

