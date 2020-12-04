<?php 

class Sample_Shop_Spec_Computer_Base_ExtraTable extends Ac_Model_Mapper_Mixable_ExtraTable {

    protected $extraIsReferenced = true;
    
    protected $modelMixable = 'Sample_Shop_Spec_Computer';
    
    protected $implMapper = 'Sample_Shop_Spec_Computer_ImplMapper';
    
    protected $modelMixableId = 'Sample_Shop_Spec_Computer';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return [
            '_shopSpecComputerShopSpec' => [
                'srcMapperClass' => 'Sample_Shop_Spec_Computer_ImplMapper',
                'destMapperClass' => 'Sample_Shop_Spec_Mapper',
                'srcVarName' => '_shopSpecComputerShopSpec',
                'fieldLinks' => [
                    'productId' => 'productId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
        ];
        
    }
    
    protected function doGetAssociationPrototypes() {
        return [
            'shopSpecComputerShopSpec' => [
                'relationId' => '_shopSpecComputerShopSpec',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'shopSpecComputerShopSpec',
                'plural' => 'shopSpecComputerShopSpecs',
                'canLoadSrcObjects' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadShopSpecComputerShopSpecsFor',
                'loadSrcObjectsMapperMethod' => NULL,
                'getSrcObjectsMapperMethod' => 'getOfShopSpecComputerShopSpecs',
                'createDestObjectMethod' => 'createShopSpecComputerShopSpec',
                'getDestObjectMethod' => 'getShopSpecComputerShopSpec',
                'setDestObjectMethod' => 'setShopSpecComputerShopSpec',
                'clearDestObjectMethod' => 'clearShopSpecComputerShopSpec',
            ],
        ];
    }
    
    
    /**
     * Loads one or more shopSpecs of given one or more shopSpecComputer 
     * @param Sample_Shop_Spec_Computer|array $shopSpecComputer     
     */
    function loadShopSpecComputerShopSpecsFor($shopSpecComputer) {
        $rel = $this->getRelation('_shopSpecComputerShopSpec');
        return $rel->loadDest($shopSpecComputer); 
    }

}

