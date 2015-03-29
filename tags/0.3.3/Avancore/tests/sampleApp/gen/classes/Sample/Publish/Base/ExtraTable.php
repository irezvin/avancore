<?php 

class Sample_Publish_Base_ExtraTable extends Ac_Model_Mapper_Mixable_ExtraTable {

    protected $extraIsReferenced = true;
    
    protected $modelMixable = 'Sample_Publish';
    
    protected $implMapper = 'Sample_Publish_ImplMapper';
    
    

    
    protected function doGetRelationPrototypes() {
        return array (
            '_authorAuthorPerson' => array (
                'srcMapperClass' => 'Sample_Publish_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_authorAuthorPerson',
                'fieldLinks' => array (
                    'authorId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
            '_editorEditorPerson' => array (
                'srcMapperClass' => 'Sample_Publish_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_editorEditorPerson',
                'fieldLinks' => array (
                    'editorId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
        );
        
    }
    
    protected function doGetAssociationPrototypes() {
        return array (
            'authorAuthorPerson' => array (
                'relationId' => '_authorAuthorPerson',
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
            ),
            'editorEditorPerson' => array (
                'relationId' => '_editorEditorPerson',
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
            ),
        );
    }
    
    
    /**
     * Loads several people of given one or more publish 
     * @param Sample_Publish|array $publish     
     */
    function loadAuthorPeopleFor($publish) {
        $rel = $this->getRelation('_authorAuthorPerson');
        return $rel->loadDest($publish); 
    }

    
    
    /**
     * Loads several people of given one or more publish 
     * @param Sample_Publish|array $publish     
     */
    function loadEditorPeopleFor($publish) {
        $rel = $this->getRelation('_editorEditorPerson');
        return $rel->loadDest($publish); 
    }

}

