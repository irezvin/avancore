<?php 

class Sample_Shop_Product_Extra_Code_Base_ExtraTable extends Ac_Model_Mapper_Mixable_ExtraTable {

    protected $tableName = '#__shop_product_extraCodes';
    
    protected $extraIsReferenced = false;
    
    protected $modelMixable = 'Sample_Shop_Product_Extra_Code';
    
    

    
    protected function doGetRelationPrototypes() {
        return array (
            '_person' => array (
                'srcMapperClass' => 'Sample_Shop_Product_Extra_Code_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_person',
                'destVarName' => '_shopProducts',
                'destCountVarName' => '_shopProductsCount',
                'destLoadedVarName' => '_shopProductsLoaded',
                'fieldLinks' => array (
                    'responsiblePersonId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
        );
        
    }
    
    protected function doGetAssociationPrototypes() {
        return array (
            'person' => array (
                'relationId' => '_person',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'person',
                'plural' => 'people',
                'canLoadSrcObjects' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadPeopleFor',
                'loadSrcObjectsMapperMethod' => NULL,
                'getSrcObjectsMapperMethod' => 'getOfPeople',
                'createDestObjectMethod' => 'createPerson',
                'getDestObjectMethod' => 'getPerson',
                'setDestObjectMethod' => 'setPerson',
                'clearDestObjectMethod' => 'clearPerson',
            ),
        );
    }

    
    
    /**
     * Loads several people of given one or more shopProductExtraCodes 
     * @param Sample_Shop_Product_Extra_Code|array $shopProductExtraCodes     
     */
    function loadPeopleFor($shopProductExtraCodes) {
        $rel = $this->getRelation('_person');
        return $rel->loadDest($shopProductExtraCodes); 
    }

}

