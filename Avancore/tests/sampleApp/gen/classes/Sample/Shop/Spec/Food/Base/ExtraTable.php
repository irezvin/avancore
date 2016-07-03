<?php 

class Sample_Shop_Spec_Food_Base_ExtraTable extends Ac_Model_Mapper_Mixable_ExtraTable {

    protected $extraIsReferenced = true;
    
    protected $modelMixable = 'Sample_Shop_Spec_Food';
    
    protected $implMapper = 'Sample_Shop_Spec_Food_ImplMapper';
    
    protected $modelMixableId = 'Sample_Shop_Spec_Food';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return array (
            '_shopSpecFoodShopSpec' => array (
                'srcMapperClass' => 'Sample_Shop_Spec_Food_ImplMapper',
                'destMapperClass' => 'Sample_Shop_Spec_Mapper',
                'srcVarName' => '_shopSpecFoodShopSpec',
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
            'shopSpecFoodShopSpec' => array (
                'relationId' => '_shopSpecFoodShopSpec',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'shopSpecFoodShopSpec',
                'plural' => 'shopSpecFoodShopSpecs',
                'canLoadSrcObjects' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadShopSpecFoodShopSpecsFor',
                'loadSrcObjectsMapperMethod' => NULL,
                'getSrcObjectsMapperMethod' => 'getOfShopSpecFoodShopSpecs',
                'createDestObjectMethod' => 'createShopSpecFoodShopSpec',
                'getDestObjectMethod' => 'getShopSpecFoodShopSpec',
                'setDestObjectMethod' => 'setShopSpecFoodShopSpec',
                'clearDestObjectMethod' => 'clearShopSpecFoodShopSpec',
            ),
        );
    }
    
    
    /**
     * Loads one or more shopSpecs of given one or more shopSpecFood 
     * @param Sample_Shop_Spec_Food|array $shopSpecFood     
     */
    function loadShopSpecFoodShopSpecsFor($shopSpecFood) {
        $rel = $this->getRelation('_shopSpecFoodShopSpec');
        return $rel->loadDest($shopSpecFood); 
    }

}

