<?php

class Sample_Publish_Base_Storage extends Ac_Model_Storage_MonoTable {

    var $tableName = '#__publish'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $primaryKey = 'id'; 

    var $defaults = array (
            'id' => NULL,
            'sharedObjectType' => NULL,
            'published' => '1',
            'deleted' => '0',
            'publishUp' => '0000-00-00 00:00:00',
            'publishDown' => '0000-00-00 00:00:00',
            'authorId' => NULL,
            'editorId' => NULL,
            'pubChannelId' => NULL,
            'dateCreated' => '0000-00-00 00:00:00',
            'dateModified' => '0000-00-00 00:00:00',
            'dateDeleted' => '0000-00-00 00:00:00',
        ); 

    var $nullableColumns = array ( 0 => 'published', 1 => 'deleted', 2 => 'publishUp', 3 => 'publishDown', 4 => 'authorId', 5 => 'editorId', 6 => 'pubChannelId', ); 

    var $autoincFieldName = 'id'; 

    var $uniqueIndices = array ( 'PRIMARY' => array ( 0 => 'id', ), 'idxPubChannelId' => array ( 0 => 'pubChannelId', ), ); 
 
    
}

