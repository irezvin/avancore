<?php 

class Child_Publish_Base_ExtraTable extends Sample_Publish_MapperMixable {

    protected $modelMixable = 'Child_Publish';
    
    protected $implMapper = 'Child_Publish_ImplMapper';
    
    protected $modelMixableId = 'Child_Publish';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), [
            '_authorPerson' => [
                'srcMapperClass' => 'Child_Publish_ImplMapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ],
            '_editorPerson' => [
                'srcMapperClass' => 'Child_Publish_ImplMapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ],
        ]);
        
    }
}

