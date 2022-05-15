<?php
/**
 * @property Child $app Access to App instance (via Mapper)
 */
class Child_Person_Base_Object extends Sample_Person {

    
    var $_mapperClass = 'Child_Person_Mapper';
    
    /**
     * @var Child_Person_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Child_Person_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'portraitPersonPhoto' => [
                'className' => 'Child_Person_Photo',
                'mapperClass' => 'Child_Person_Photo_Mapper',
                'caption' => 'Person photo',
            ],
            'religion' => [
                'className' => 'Child_Religion',
                'mapperClass' => 'Child_Religion_Mapper',
                'caption' => 'Religion',
            ],
            'tags' => [
                'className' => 'Child_Tag',
                'mapperClass' => 'Child_Tag_Mapper',
                'caption' => 'Tags',
            ],
            'tagIds' => [
                'values' => [
                    'mapperClass' => 'Child_Tag_Mapper',
                ],
            ],
            'personAlbums' => [
                'className' => 'Child_Person_Album',
                'mapperClass' => 'Child_Person_Album_Mapper',
                'caption' => 'Person albums',
            ],
            'personPhotos' => [
                'className' => 'Child_Person_Photo',
                'mapperClass' => 'Child_Person_Photo_Mapper',
                'caption' => 'Person photos',
            ],
            'personPosts' => [
                'className' => 'Child_Person_Post',
                'mapperClass' => 'Child_Person_Post_Mapper',
                'caption' => 'Person posts',
            ],
            'incomingRelations' => [
                'className' => 'Child_Relation',
                'mapperClass' => 'Child_Relation_Mapper',
                'otherModelIdInMethodsPrefix' => 'incoming',
                'caption' => 'Relations',
            ],
            'outgoingRelations' => [
                'className' => 'Child_Relation',
                'mapperClass' => 'Child_Relation_Mapper',
                'otherModelIdInMethodsPrefix' => 'outgoing',
                'caption' => 'Relations',
            ],
            'extraCodeShopProducts' => [
                'className' => 'Child_Shop_Product',
                'mapperClass' => 'Child_Shop_Product_Mapper',
                'caption' => 'Shop products',
            ],
            'noteShopProducts' => [
                'className' => 'Child_Shop_Product',
                'mapperClass' => 'Child_Shop_Product_Mapper',
                'caption' => 'Shop products',
            ],
            'personId' => [
                'caption' => 'Person Id',
            ],
            'name' => [
                'caption' => 'Name',
            ],
            'gender' => [
                'caption' => 'Gender',
            ],
            'isSingle' => [
                'caption' => 'Is Single',
            ],
            'birthDate' => [
                'caption' => 'Birth Date',
            ],
            'lastUpdatedDatetime' => [
                'caption' => 'Last Updated Datetime',
            ],
            'createdTs' => [
                'caption' => 'Created Ts',
            ],
            'religionId' => [

                'dummyCaption' => '',
                'values' => [
                    'mapperClass' => 'Child_Religion_Mapper',
                ],
                'caption' => 'Religion Id',
            ],
            'portraitId' => [

                'dummyCaption' => '',
                'values' => [
                    'mapperClass' => 'Child_Person_Photo_Mapper',
                ],
                'caption' => 'Portrait Id',
            ],
        ];
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Person_Photo 
     */
    function getPortraitPersonPhoto() {
        return parent::getPortraitPersonPhoto();
    }
    
    /**
     * @param Child_Person_Photo $portraitPersonPhoto 
     */
    function setPortraitPersonPhoto($portraitPersonPhoto) {
        if ($portraitPersonPhoto && !is_a($portraitPersonPhoto, 'Child_Person_Photo')) 
            trigger_error('$portraitPersonPhoto must be an instance of Child_Person_Photo', E_USER_ERROR);
        return parent::setPortraitPersonPhoto($portraitPersonPhoto);
    }
    
    /**
     * @return Child_Person_Photo  
     */
    function createPortraitPersonPhoto($values = array()) {
        return parent::createPortraitPersonPhoto($values);
    }

    
        
    
    /**
     * @return Child_Religion 
     */
    function getReligion() {
        return parent::getReligion();
    }
    
    /**
     * @param Child_Religion $religion 
     */
    function setReligion($religion) {
        if ($religion && !is_a($religion, 'Child_Religion')) 
            trigger_error('$religion must be an instance of Child_Religion', E_USER_ERROR);
        return parent::setReligion($religion);
    }
    
    /**
     * @return Child_Religion  
     */
    function createReligion($values = array()) {
        return parent::createReligion($values);
    }

    
        
    
    /**
     * @return Child_Tag 
     */
    function getTag($id) {
        return parent::getTag($id);
    }
    
    /**
     * @return Child_Tag 
     */
    function getTagsItem($id) {
        return parent::getTagsItem($id);
    }
    
    /**
     * @param Child_Tag $tag 
     */
    function addTag($tag) {
        if (!is_a($tag, 'Child_Tag'))
            trigger_error('$tag must be an instance of Child_Tag', E_USER_ERROR);
        return parent::addTag($tag);
    }
    
    /**
     * @return Child_Tag  
     */
    function createTag($values = array()) {
        return parent::createTag($values);
    }

    

        
    
    /**
     * @return Child_Person_Album 
     */
    function getPersonAlbum($id) {
        return parent::getPersonAlbum($id);
    }
    
    /**
     * @return Child_Person_Album 
     */
    function getPersonAlbumsItem($id) {
        return parent::getPersonAlbumsItem($id);
    }
    
    /**
     * @param Child_Person_Album $personAlbum 
     */
    function addPersonAlbum($personAlbum) {
        if (!is_a($personAlbum, 'Child_Person_Album'))
            trigger_error('$personAlbum must be an instance of Child_Person_Album', E_USER_ERROR);
        return parent::addPersonAlbum($personAlbum);
    }
    
    /**
     * @return Child_Person_Album  
     */
    function createPersonAlbum($values = array()) {
        return parent::createPersonAlbum($values);
    }

    

        
    
    /**
     * @return Child_Person_Photo 
     */
    function getPersonPhoto($id) {
        return parent::getPersonPhoto($id);
    }
    
    /**
     * @return Child_Person_Photo 
     */
    function getPersonPhotosItem($id) {
        return parent::getPersonPhotosItem($id);
    }
    
    /**
     * @param Child_Person_Photo $personPhoto 
     */
    function addPersonPhoto($personPhoto) {
        if (!is_a($personPhoto, 'Child_Person_Photo'))
            trigger_error('$personPhoto must be an instance of Child_Person_Photo', E_USER_ERROR);
        return parent::addPersonPhoto($personPhoto);
    }
    
    /**
     * @return Child_Person_Photo  
     */
    function createPersonPhoto($values = array()) {
        return parent::createPersonPhoto($values);
    }

    

        
    
    /**
     * @return Child_Person_Post 
     */
    function getPersonPost($id) {
        return parent::getPersonPost($id);
    }
    
    /**
     * @return Child_Person_Post 
     */
    function getPersonPostsItem($id) {
        return parent::getPersonPostsItem($id);
    }
    
    /**
     * @param Child_Person_Post $personPost 
     */
    function addPersonPost($personPost) {
        if (!is_a($personPost, 'Child_Person_Post'))
            trigger_error('$personPost must be an instance of Child_Person_Post', E_USER_ERROR);
        return parent::addPersonPost($personPost);
    }
    
    /**
     * @return Child_Person_Post  
     */
    function createPersonPost($values = array()) {
        return parent::createPersonPost($values);
    }

    

        
    
    /**
     * @return Child_Relation 
     */
    function getIncomingRelation($id) {
        return parent::getIncomingRelation($id);
    }
    
    /**
     * @return Child_Relation 
     */
    function getIncomingRelationsItem($id) {
        return parent::getIncomingRelationsItem($id);
    }
    
    /**
     * @param Child_Relation $incomingRelation 
     */
    function addIncomingRelation($incomingRelation) {
        if (!is_a($incomingRelation, 'Child_Relation'))
            trigger_error('$incomingRelation must be an instance of Child_Relation', E_USER_ERROR);
        return parent::addIncomingRelation($incomingRelation);
    }
    
    /**
     * @return Child_Relation  
     */
    function createIncomingRelation($values = array()) {
        return parent::createIncomingRelation($values);
    }

    

        
    
    /**
     * @return Child_Relation 
     */
    function getOutgoingRelation($id) {
        return parent::getOutgoingRelation($id);
    }
    
    /**
     * @return Child_Relation 
     */
    function getOutgoingRelationsItem($id) {
        return parent::getOutgoingRelationsItem($id);
    }
    
    /**
     * @param Child_Relation $outgoingRelation 
     */
    function addOutgoingRelation($outgoingRelation) {
        if (!is_a($outgoingRelation, 'Child_Relation'))
            trigger_error('$outgoingRelation must be an instance of Child_Relation', E_USER_ERROR);
        return parent::addOutgoingRelation($outgoingRelation);
    }
    
    /**
     * @return Child_Relation  
     */
    function createOutgoingRelation($values = array()) {
        return parent::createOutgoingRelation($values);
    }

    

        
    
    /**
     * @return Child_Shop_Product 
     */
    function getExtraCodeShopProduct($id) {
        return parent::getExtraCodeShopProduct($id);
    }
    
    /**
     * @return Child_Shop_Product 
     */
    function getExtraCodeShopProductsItem($id) {
        return parent::getExtraCodeShopProductsItem($id);
    }
    
    /**
     * @param Child_Shop_Product $extraCodeShopProduct 
     */
    function addExtraCodeShopProduct($extraCodeShopProduct) {
        if (!is_a($extraCodeShopProduct, 'Child_Shop_Product'))
            trigger_error('$extraCodeShopProduct must be an instance of Child_Shop_Product', E_USER_ERROR);
        return parent::addExtraCodeShopProduct($extraCodeShopProduct);
    }
    
    /**
     * @return Child_Shop_Product  
     */
    function createExtraCodeShopProduct($values = array()) {
        return parent::createExtraCodeShopProduct($values);
    }

    

        
    
    /**
     * @return Child_Shop_Product 
     */
    function getNoteShopProduct($id) {
        return parent::getNoteShopProduct($id);
    }
    
    /**
     * @return Child_Shop_Product 
     */
    function getNoteShopProductsItem($id) {
        return parent::getNoteShopProductsItem($id);
    }
    
    /**
     * @param Child_Shop_Product $noteShopProduct 
     */
    function addNoteShopProduct($noteShopProduct) {
        if (!is_a($noteShopProduct, 'Child_Shop_Product'))
            trigger_error('$noteShopProduct must be an instance of Child_Shop_Product', E_USER_ERROR);
        return parent::addNoteShopProduct($noteShopProduct);
    }
    
    /**
     * @return Child_Shop_Product  
     */
    function createNoteShopProduct($values = array()) {
        return parent::createNoteShopProduct($values);
    }

    

  
    
}

