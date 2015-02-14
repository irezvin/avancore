<?php 

class Sample_Shop_Product_Note_Base_ExtraTable extends Ac_Model_Mapper_Mixable_ExtraTable {

    protected $extraIsReferenced = false;
    
    protected $implMapper = 'Sample_Shop_Product_Note_ImplMapper';
    
    

    
    protected function doGetRelationPrototypes() {
        return array (
            '_notePerson' => array (
                'srcMapperClass' => 'Sample_Shop_Product_Note_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_notePerson',
                'srcCountVarName' => '_noteShopProductsCount',
                'srcLoadedVarName' => '_noteShopProductsLoaded',
                'destVarName' => '_noteShopProducts',
                'destCountVarName' => '_shopProductsCount',
                'destLoadedVarName' => '_shopProductsLoaded',
                'fieldLinks' => array (
                    'noteAuthorId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
        );
        
    }
    
    protected function doGetAssociationPrototypes() {
        return array (
            'notePerson' => array (
                'relationId' => '_notePerson',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'notePerson',
                'plural' => 'notePeople',
                'canLoadSrcObjects' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadNotePeopleFor',
                'loadSrcObjectsMapperMethod' => NULL,
                'getSrcObjectsMapperMethod' => 'getOfNotePeople',
                'createDestObjectMethod' => 'createNotePerson',
                'getDestObjectMethod' => 'getNotePerson',
                'setDestObjectMethod' => 'setNotePerson',
                'clearDestObjectMethod' => 'clearNotePerson',
            ),
        );
    }

    
    
    /**
     * Loads several people of given one or more shopProductNotes 
     * @param Sample_Shop_Product_Note|array $shopProductNotes     
     */
    function loadNotePeopleFor($shopProductNotes) {
        $rel = $this->getRelation('_notePerson');
        return $rel->loadDest($shopProductNotes); 
    }

}

