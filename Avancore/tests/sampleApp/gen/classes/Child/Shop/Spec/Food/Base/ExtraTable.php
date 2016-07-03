<?php 

class Child_Shop_Spec_Food_Base_ExtraTable extends Sample_Shop_Spec_Food_MapperMixable {

    protected $modelMixable = 'Child_Shop_Spec_Food';
    
    protected $implMapper = 'Child_Shop_Spec_Food_ImplMapper';
    
    protected $modelMixableId = 'Child_Shop_Spec_Food';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_shopSpecFoodShopSpec' => array (
                'srcMapperClass' => 'Child_Shop_Spec_Food_ImplMapper',
                'destMapperClass' => 'Child_Shop_Spec_Mapper',
            ),
        ));
        
    }
}

