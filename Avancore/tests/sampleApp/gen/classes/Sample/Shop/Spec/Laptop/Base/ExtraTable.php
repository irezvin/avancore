<?php 

class Sample_Shop_Spec_Laptop_Base_ExtraTable extends Ac_Model_Mapper_Mixable_ExtraTable {

    protected $extraIsReferenced = true;
    
    protected $modelMixable = 'Sample_Shop_Spec_Laptop';
    
    protected $implMapper = 'Sample_Shop_Spec_Laptop_ImplMapper';
    
    protected $modelMixableId = 'Sample_Shop_Spec_Laptop';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return array (
            '_shopSpecLaptopShopSpec' => array (
                'srcMapperClass' => 'Sample_Shop_Spec_Laptop_ImplMapper',
                'destMapperClass' => 'Sample_Shop_Spec_Mapper',
                'srcVarName' => '_shopSpecLaptopShopSpec',
                'fieldLinks' => array (
                    'productId' => 'productId',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
        );
        
    }
    
    protected function doGetAssociationPrototypes() {
        return array (
            'shopSpecLaptopShopSpec' => array (
                'relationId' => '_shopSpecLaptopShopSpec',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'shopSpecLaptopShopSpec',
                'plural' => 'shopSpecLaptopShopSpecs',
                'canLoadSrcObjects' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadShopSpecLaptopShopSpecsFor',
                'loadSrcObjectsMapperMethod' => NULL,
                'getSrcObjectsMapperMethod' => 'getOfShopSpecLaptopShopSpecs',
                'createDestObjectMethod' => 'createShopSpecLaptopShopSpec',
                'getDestObjectMethod' => 'getShopSpecLaptopShopSpec',
                'setDestObjectMethod' => 'setShopSpecLaptopShopSpec',
                'clearDestObjectMethod' => 'clearShopSpecLaptopShopSpec',
            ),
        );
    }
    
    
    /**
     * Loads one or more shopSpecs of given one or more shopSpecLaptop 
     * @param Sample_Shop_Spec_Laptop|array $shopSpecLaptop     
     */
    function loadShopSpecLaptopShopSpecsFor($shopSpecLaptop) {
        $rel = $this->getRelation('_shopSpecLaptopShopSpec');
        return $rel->loadDest($shopSpecLaptop); 
    }

}

