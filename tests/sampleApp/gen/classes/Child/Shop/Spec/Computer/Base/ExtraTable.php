<?php 

class Child_Shop_Spec_Computer_Base_ExtraTable extends Sample_Shop_Spec_Computer_MapperMixable {

    protected $modelMixable = 'Child_Shop_Spec_Computer';
    
    protected $implMapper = 'Child_Shop_Spec_Computer_ImplMapper';
    
    protected $modelMixableId = 'Child_Shop_Spec_Computer';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_shopSpecComputerShopSpec' => array (
                'srcMapperClass' => 'Child_Shop_Spec_Computer_ImplMapper',
                'destMapperClass' => 'Child_Shop_Spec_Mapper',
            ),
        ));
        
    }
}

