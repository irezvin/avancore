<?php 

class Child_Shop_Spec_Laptop_Base_ExtraTable extends Sample_Shop_Spec_Laptop_MapperMixable {

    protected $modelMixable = 'Child_Shop_Spec_Laptop';
    
    protected $implMapper = 'Child_Shop_Spec_Laptop_ImplMapper';
    
    protected $modelMixableId = 'Child_Shop_Spec_Laptop';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_shopSpecLaptopShopSpec' => array (
                'srcMapperClass' => 'Child_Shop_Spec_Laptop_ImplMapper',
                'destMapperClass' => 'Child_Shop_Spec_Mapper',
            ),
        ));
        
    }
}

