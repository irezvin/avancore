<?php 

class Child_Shop_Product_Extra_Code_Base_ExtraTable extends Sample_Shop_Product_Extra_Code_MapperMixable {

    protected $modelMixable = 'Child_Shop_Product_Extra_Code';
    
    protected $implMapper = 'Child_Shop_Product_Extra_Code_ImplMapper';
    
    

    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_extraCodeExtraCodePerson' => array (
                'srcMapperClass' => 'Child_Shop_Product_Extra_Code_ImplMapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ),
        ));
        
    }
}

