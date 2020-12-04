<?php 

class Child_Shop_Product_Note_Base_ExtraTable extends Sample_Shop_Product_Note_MapperMixable {

    protected $implMapper = 'Child_Shop_Product_Note_ImplMapper';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), [
            '_notePerson' => [
                'srcMapperClass' => 'Child_Shop_Product_Note_ImplMapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ],
        ]);
        
    }
}

