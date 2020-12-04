<?php 

class Sample_Shop_Spec_Monitor_Base_ExtraTable extends Ac_Model_Mapper_Mixable_ExtraTable {

    protected $extraIsReferenced = false;
    
    protected $modelMixable = 'Sample_Shop_Spec_Monitor';
    
    protected $implMapper = 'Sample_Shop_Spec_Monitor_ImplMapper';
    
    protected $modelMixableId = 'Sample_Shop_Spec_Monitor';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return [
            '_monitorShopClassifier' => [
                'srcMapperClass' => 'Sample_Shop_Spec_Monitor_ImplMapper',
                'destMapperClass' => 'Sample_Shop_Classifier_Mapper',
                'srcVarName' => '_monitorShopClassifier',
                'srcCountVarName' => '_monitorShopSpecsCount',
                'srcLoadedVarName' => '_monitorShopSpecsLoaded',
                'destVarName' => '_monitorShopSpecs',
                'destCountVarName' => '_shopSpecsCount',
                'destLoadedVarName' => '_shopSpecsLoaded',
                'fieldLinks' => [
                    'matrixTypeId' => 'id',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
        ];
        
    }
    
    protected function doGetAssociationPrototypes() {
        return [
            'monitorShopClassifier' => [
                'relationId' => '_monitorShopClassifier',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'monitorShopClassifier',
                'plural' => 'monitorShopClassifier',
                'canLoadSrcObjects' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadMonitorShopClassifierFor',
                'loadSrcObjectsMapperMethod' => NULL,
                'getSrcObjectsMapperMethod' => 'getOfMonitorShopClassifier',
                'createDestObjectMethod' => 'createMonitorShopClassifier',
                'getDestObjectMethod' => 'getMonitorShopClassifier',
                'setDestObjectMethod' => 'setMonitorShopClassifier',
                'clearDestObjectMethod' => 'clearMonitorShopClassifier',
            ],
        ];
    }
    
    
    /**
     * Loads several shopClassifier of given one or more shopSpecMonitor 
     * @param Sample_Shop_Spec_Monitor|array $shopSpecMonitor     
     */
    function loadMonitorShopClassifierFor($shopSpecMonitor) {
        $rel = $this->getRelation('_monitorShopClassifier');
        return $rel->loadDest($shopSpecMonitor); 
    }

}

