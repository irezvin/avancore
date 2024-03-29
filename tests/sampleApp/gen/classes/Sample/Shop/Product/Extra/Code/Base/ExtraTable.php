<?php 

class Sample_Shop_Product_Extra_Code_Base_ExtraTable extends Ac_Model_Mapper_Mixable_ExtraTable {

    protected $extraIsReferenced = false;
    
    protected $modelMixable = 'Sample_Shop_Product_Extra_Code';
    
    protected $implMapper = 'Sample_Shop_Product_Extra_Code_ImplMapper';
    
    protected $modelMixableId = 'Sample_Shop_Product_Extra_Code';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return [
            '_extraCodePerson' => [
                'srcMapperClass' => 'Sample_Shop_Product_Extra_Code_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_extraCodePerson',
                'destVarName' => '_extraCodeShopProducts',
                'destCountVarName' => '_extraCodeShopProductsCount',
                'destLoadedVarName' => '_extraCodeShopProductsLoaded',
                'fieldLinks' => [
                    'responsiblePersonId' => 'personId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
        ];
        
    }
    
    protected function doGetAssociationPrototypes() {
        return [
            'extraCodePerson' => [
                'relationId' => '_extraCodePerson',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'extraCodePerson',
                'plural' => 'extraCodePeople',
                'canLoadSrcObjects' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadExtraCodePeopleFor',
                'loadSrcObjectsMapperMethod' => NULL,
                'getSrcObjectsMapperMethod' => 'getOfExtraCodePeople',
                'createDestObjectMethod' => 'createExtraCodePerson',
                'getDestObjectMethod' => 'getExtraCodePerson',
                'setDestObjectMethod' => 'setExtraCodePerson',
                'clearDestObjectMethod' => 'clearExtraCodePerson',
            ],
        ];
    }
    
    
    /**
     * Loads several people of given one or more shopProductExtraCodes 
     * @param Sample_Shop_Product_Extra_Code|array $shopProductExtraCodes     
     */
    function loadExtraCodePeopleFor($shopProductExtraCodes) {
        $rel = $this->getRelation('_extraCodePerson');
        return $rel->loadDest($shopProductExtraCodes); 
    }

}

