<?php 

class Child_Shop_Spec_Monitor_Base_ExtraTable extends Sample_Shop_Spec_Monitor_MapperMixable {

    protected $modelMixable = 'Child_Shop_Spec_Monitor';
    
    protected $implMapper = 'Child_Shop_Spec_Monitor_ImplMapper';
    
    protected $modelMixableId = 'Child_Shop_Spec_Monitor';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_monitorShopClassifier' => array (
                'srcMapperClass' => 'Child_Shop_Spec_Monitor_ImplMapper',
                'destMapperClass' => 'Child_Shop_Classifier_Mapper',
            ),
        ));
        
    }
}

