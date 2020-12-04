<?php 

class Sample_Publish_Base_ExtraTable extends Ac_Model_Mapper_Mixable_ExtraTable {

    protected $extraIsReferenced = true;
    
    protected $modelMixable = 'Sample_Publish';
    
    protected $implMapper = 'Sample_Publish_ImplMapper';
    
    protected $objectTypeField = 'sharedObjectType';
    
    protected $modelMixableId = 'Sample_Publish';
    
    
    
    
    protected function doGetRelationPrototypes() {
        return [
            '_authorPerson' => [
                'srcMapperClass' => 'Sample_Publish_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_authorPerson',
                'destVarName' => '_authorPublish',
                'destCountVarName' => '_authorPublishCount',
                'destLoadedVarName' => '_authorPublishLoaded',
                'fieldLinks' => [
                    'authorId' => 'personId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
            '_editorPerson' => [
                'srcMapperClass' => 'Sample_Publish_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_editorPerson',
                'destVarName' => '_editorPublish',
                'destCountVarName' => '_editorPublishCount',
                'destLoadedVarName' => '_editorPublishLoaded',
                'fieldLinks' => [
                    'editorId' => 'personId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
        ];
        
    }
    
    protected function doGetAssociationPrototypes() {
        return [
            'authorPerson' => [
                'relationId' => '_authorPerson',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'authorPerson',
                'plural' => 'authorPeople',
                'canLoadSrcObjects' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadAuthorPeopleFor',
                'loadSrcObjectsMapperMethod' => NULL,
                'getSrcObjectsMapperMethod' => 'getOfAuthorPeople',
                'createDestObjectMethod' => 'createAuthorPerson',
                'getDestObjectMethod' => 'getAuthorPerson',
                'setDestObjectMethod' => 'setAuthorPerson',
                'clearDestObjectMethod' => 'clearAuthorPerson',
            ],
            'editorPerson' => [
                'relationId' => '_editorPerson',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'editorPerson',
                'plural' => 'editorPeople',
                'canLoadSrcObjects' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadEditorPeopleFor',
                'loadSrcObjectsMapperMethod' => NULL,
                'getSrcObjectsMapperMethod' => 'getOfEditorPeople',
                'createDestObjectMethod' => 'createEditorPerson',
                'getDestObjectMethod' => 'getEditorPerson',
                'setDestObjectMethod' => 'setEditorPerson',
                'clearDestObjectMethod' => 'clearEditorPerson',
            ],
        ];
    }
    
    
    /**
     * Loads several people of given one or more publish 
     * @param Sample_Publish|array $publish     
     */
    function loadAuthorPeopleFor($publish) {
        $rel = $this->getRelation('_authorPerson');
        return $rel->loadDest($publish); 
    }

    
    
    /**
     * Loads several people of given one or more publish 
     * @param Sample_Publish|array $publish     
     */
    function loadEditorPeopleFor($publish) {
        $rel = $this->getRelation('_editorPerson');
        return $rel->loadDest($publish); 
    }

}

