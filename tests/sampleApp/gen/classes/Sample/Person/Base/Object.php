<?php

class Sample_Person_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_portraitPersonPhoto = false;

    var $_religion = false;

    var $_tags = false;

    var $_tagsCount = false;

    var $_tagsLoaded = false;

    var $_tagIds = false;

    var $_personAlbums = false;

    var $_personAlbumsCount = false;

    var $_personAlbumsLoaded = false;

    var $_personPhotos = false;

    var $_personPhotosCount = false;

    var $_personPhotosLoaded = false;

    var $_personPosts = false;

    var $_personPostsCount = false;

    var $_personPostsLoaded = false;

    var $_authorPublish = false;

    var $_authorPublishCount = false;

    var $_authorPublishLoaded = false;

    var $_editorPublish = false;

    var $_editorPublishCount = false;

    var $_editorPublishLoaded = false;

    var $_incomingRelations = false;

    var $_incomingRelationsCount = false;

    var $_incomingRelationsLoaded = false;

    var $_outgoingRelations = false;

    var $_outgoingRelationsCount = false;

    var $_outgoingRelationsLoaded = false;

    var $_extraCodeShopProducts = false;

    var $_shopProductsCount = false;

    var $_shopProductsLoaded = false;

    var $_noteShopProducts = false;

    var $personId = NULL;

    var $name = '';

    var $gender = 'F';

    var $isSingle = 1;

    var $birthDate = NULL;

    var $lastUpdatedDatetime = NULL;

    var $createdTs = false;

    var $religionId = NULL;

    var $portraitId = NULL;
    
    var $_mapperClass = 'Sample_Person_Mapper';
    
    /**
     * @var Sample_Person_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Person_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'portraitPersonPhoto', 1 => 'religion', 2 => 'tags', 3 => 'tagIds', 4 => 'personAlbums', 5 => 'personPhotos', 6 => 'personPosts', 7 => 'authorPublish', 8 => 'editorPublish', 9 => 'incomingRelations', 10 => 'outgoingRelations', 11 => 'extraCodeShopProducts', 12 => 'noteShopProducts', ]));
    }
    
 
    protected function listOwnLists() {
        
        return [ 'tags' => 'tags', 'personAlbums' => 'personAlbums', 'personPhotos' => 'personPhotos', 'personPosts' => 'personPosts', 'authorPublish' => 'authorPublish', 'editorPublish' => 'editorPublish', 'incomingRelations' => 'incomingRelations', 'outgoingRelations' => 'outgoingRelations', 'extraCodeShopProducts' => 'shopProducts', 'noteShopProducts' => 'shopProducts', ];
    }

    
 
    protected function listOwnAssociations() {
        return [ 'portraitPersonPhoto' => 'Sample_Person_Photo', 'religion' => 'Sample_Religion', 'tags' => 'Sample_Tag', 'personAlbums' => 'Sample_Person_Album', 'personPhotos' => 'Sample_Person_Photo', 'personPosts' => 'Sample_Person_Post', 'authorPublish' => 'Sample_Publish', 'editorPublish' => 'Sample_Publish', 'incomingRelations' => 'Sample_Relation', 'outgoingRelations' => 'Sample_Relation', 'extraCodeShopProducts' => 'Sample_Shop_Product', 'noteShopProducts' => 'Sample_Shop_Product', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'portraitPersonPhoto' => [
                'className' => 'Sample_Person_Photo',
                'mapperClass' => 'Sample_Person_Photo_Mapper',
                'otherModelIdInMethodsPrefix' => 'portrait',

                'caption' => new Ac_Lang_String('sample_person_portrait_person_photo'),
                'relationId' => '_portraitPersonPhoto',
                'referenceVarName' => '_portraitPersonPhoto',
            ],
            'religion' => [
                'className' => 'Sample_Religion',
                'mapperClass' => 'Sample_Religion_Mapper',

                'caption' => new Ac_Lang_String('sample_person_religion'),
                'relationId' => '_religion',
                'referenceVarName' => '_religion',
            ],
            'tags' => [
                'className' => 'Sample_Tag',
                'mapperClass' => 'Sample_Tag_Mapper',

                'caption' => new Ac_Lang_String('sample_person_tags'),
                'relationId' => '_tags',
                'countVarName' => '_tagsCount',
                'nnIdsVarName' => '_tagIds',
                'referenceVarName' => '_tags',
            ],
            'tagIds' => [
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Tag_Mapper',
                ],
                'showInTable' => false,
            ],
            'personAlbums' => [
                'className' => 'Sample_Person_Album',
                'mapperClass' => 'Sample_Person_Album_Mapper',

                'caption' => new Ac_Lang_String('sample_person_person_albums'),
                'relationId' => '_personAlbums',
                'countVarName' => '_personAlbumsCount',
                'referenceVarName' => '_personAlbums',
            ],
            'personPhotos' => [
                'className' => 'Sample_Person_Photo',
                'mapperClass' => 'Sample_Person_Photo_Mapper',

                'caption' => new Ac_Lang_String('sample_person_person_photos'),
                'relationId' => '_personPhotos',
                'countVarName' => '_personPhotosCount',
                'referenceVarName' => '_personPhotos',
            ],
            'personPosts' => [
                'className' => 'Sample_Person_Post',
                'mapperClass' => 'Sample_Person_Post_Mapper',

                'caption' => new Ac_Lang_String('sample_person_person_posts'),
                'relationId' => '_personPosts',
                'countVarName' => '_personPostsCount',
                'referenceVarName' => '_personPosts',
            ],
            'authorPublish' => [
                'className' => 'Sample_Publish',
                'mapperClass' => 'Sample_Publish_ImplMapper',
                'otherModelIdInMethodsPrefix' => 'author',

                'caption' => new Ac_Lang_String('sample_person_author_publish'),
                'relationId' => '_authorPublish',
                'countVarName' => '_authorPublishCount',
                'referenceVarName' => '_authorPublish',
            ],
            'editorPublish' => [
                'className' => 'Sample_Publish',
                'mapperClass' => 'Sample_Publish_ImplMapper',
                'otherModelIdInMethodsPrefix' => 'editor',

                'caption' => new Ac_Lang_String('sample_person_editor_publish'),
                'relationId' => '_editorPublish',
                'countVarName' => '_editorPublishCount',
                'referenceVarName' => '_editorPublish',
            ],
            'incomingRelations' => [
                'className' => 'Sample_Relation',
                'mapperClass' => 'Sample_Relation_Mapper',
                'otherModelIdInMethodsSingle' => 'incomingRelation',
                'otherModelIdInMethodsPlural' => 'incomingRelations',

                'caption' => new Ac_Lang_String('sample_person_incoming_relations'),
                'relationId' => '_incomingRelations',
                'countVarName' => '_incomingRelationsCount',
                'referenceVarName' => '_incomingRelations',
            ],
            'outgoingRelations' => [
                'className' => 'Sample_Relation',
                'mapperClass' => 'Sample_Relation_Mapper',
                'otherModelIdInMethodsSingle' => 'outgoingRelation',
                'otherModelIdInMethodsPlural' => 'outgoingRelations',

                'caption' => new Ac_Lang_String('sample_person_outgoing_relations'),
                'relationId' => '_outgoingRelations',
                'countVarName' => '_outgoingRelationsCount',
                'referenceVarName' => '_outgoingRelations',
            ],
            'extraCodeShopProducts' => [
                'className' => 'Sample_Shop_Product',
                'mapperClass' => 'Sample_Shop_Product_Mapper',
                'otherModelIdInMethodsPrefix' => 'extraCode',

                'caption' => new Ac_Lang_String('sample_person_extra_code_shop_products'),
                'relationId' => '_extraCodeShopProducts',
                'countVarName' => '_shopProductsCount',
                'referenceVarName' => '_extraCodeShopProducts',
            ],
            'noteShopProducts' => [
                'className' => 'Sample_Shop_Product',
                'mapperClass' => 'Sample_Shop_Product_Mapper',
                'otherModelIdInMethodsPrefix' => 'note',

                'caption' => new Ac_Lang_String('sample_person_note_shop_products'),
                'relationId' => '_noteShopProducts',
                'countVarName' => '_shopProductsCount',
                'referenceVarName' => '_noteShopProducts',
            ],
            'personId' => [
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_person_person_id'),
            ],
            'name' => [
                'maxLength' => '255',

                'caption' => new Ac_Lang_String('sample_person_name'),
            ],
            'gender' => [
                'controlType' => 'selectList',
                'valueList' => [
                    'F' => 'F',
                    'M' => 'M',
                ],

                'caption' => new Ac_Lang_String('sample_person_gender'),
            ],
            'isSingle' => [
                'dataType' => 'bool',
                'controlType' => 'selectList',
                'maxLength' => '1',
                'valueList' => [
                    0 => 'No',
                    1 => 'Yes',
                ],

                'caption' => new Ac_Lang_String('sample_person_is_single'),
            ],
            'birthDate' => [
                'dataType' => 'date',
                'controlType' => 'dateInput',

                'caption' => new Ac_Lang_String('sample_person_birth_date'),
                'internalDateFormat' => 'Y-m-d',
                'outputDateFormat' => 'Y-m-d',
            ],
            'lastUpdatedDatetime' => [
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_person_last_updated_datetime'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ],
            'createdTs' => [
                'dataType' => 'timestamp',
                'controlType' => 'dateInput',

                'caption' => new Ac_Lang_String('sample_person_created_ts'),
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ],
            'religionId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',

                'dummyCaption' => '',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Religion_Mapper',
                ],
                'objectPropertyName' => 'religion',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_person_religion_id'),
            ],
            'portraitId' => [
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',

                'dummyCaption' => '',
                'values' => [
                    'class' => 'Ac_Model_Values_Mapper',
                    'mapperClass' => 'Sample_Person_Photo_Mapper',
                ],
                'objectPropertyName' => 'portraitPersonPhoto',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_person_portrait_id'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }
        
    
    /**
     * @return Sample_Person_Photo 
     */
    function getPortraitPersonPhoto() {
        if ($this->_portraitPersonPhoto === false) {
            $this->mapper->loadPortraitPersonPhotosFor($this);
            
        }
        return $this->_portraitPersonPhoto;
    }
    
    /**
     * @param Sample_Person_Photo $portraitPersonPhoto 
     */
    function setPortraitPersonPhoto($portraitPersonPhoto) {
        if ($portraitPersonPhoto === false) $this->_portraitPersonPhoto = false;
        elseif ($portraitPersonPhoto === null) $this->_portraitPersonPhoto = null;
        else {
            if (!is_a($portraitPersonPhoto, 'Sample_Person_Photo')) trigger_error('$portraitPersonPhoto must be an instance of Sample_Person_Photo', E_USER_ERROR);
            if (!is_object($this->_portraitPersonPhoto) && !Ac_Util::sameObject($this->_portraitPersonPhoto, $portraitPersonPhoto)) { 
                $this->_portraitPersonPhoto = $portraitPersonPhoto;
            }
        }
    }
    
    function clearPortraitPersonPhoto() {
        $this->portraitPersonPhoto = null;
    }

    /**
     * @return Sample_Person_Photo  
     */
    function createPortraitPersonPhoto($values = array()) {
        $m = $this->getMapper('Sample_Person_Photo_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setPortraitPersonPhoto($res);
        return $res;
    }

    
        
    
    /**
     * @return Sample_Religion 
     */
    function getReligion() {
        if ($this->_religion === false) {
            $this->mapper->loadReligionFor($this);
            
        }
        return $this->_religion;
    }
    
    /**
     * @param Sample_Religion $religion 
     */
    function setReligion($religion) {
        if ($religion === false) $this->_religion = false;
        elseif ($religion === null) $this->_religion = null;
        else {
            if (!is_a($religion, 'Sample_Religion')) trigger_error('$religion must be an instance of Sample_Religion', E_USER_ERROR);
            if (!is_object($this->_religion) && !Ac_Util::sameObject($this->_religion, $religion)) { 
                $this->_religion = $religion;
            }
        }
    }
    
    function clearReligion() {
        $this->religion = null;
    }

    /**
     * @return Sample_Religion  
     */
    function createReligion($values = array()) {
        $m = $this->getMapper('Sample_Religion_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->setReligion($res);
        return $res;
    }

    

    function countTags() {
        if (is_array($this->_tags)) return count($this->_tags);
        if ($this->_tagsCount === false) {
            $this->mapper->loadAssocCountFor($this, '_tags');
        }
        return $this->_tagsCount;
        
    }

    function listTags() {
        if (!$this->_tagsLoaded) {
            $this->mapper->loadTagsFor($this);
        }
        return array_keys($this->_tags);
    }
    
    /**
     * @return bool
     */
    function isTagsLoaded() {
        return $this->_tagsLoaded;
    }
    
    /**
     * @return Sample_Tag 
     */
    function getTag($id) {
        if (!$this->_tagsLoaded) {
            $this->mapper->loadTagsFor($this);
        }
        
        if (!isset($this->_tags[$id])) trigger_error ('No such Tag: \''.$id.'\'', E_USER_ERROR);
        return $this->_tags[$id];
    }
    
    /**
     * @return Sample_Tag 
     */
    function getTagsItem($id) {
        return $this->getTag($id);
    }
    
    /**
     * @return Sample_Tag[] 
     */
    function getAllTags() {
        $res = [];
        foreach ($this->listTags() as $id)
            $res[] = $this->getTag($id);
        return $res;
    }
    
    /**
     * @param Sample_Tag $tag 
     */
    function addTag($tag) {
        if (!is_a($tag, 'Sample_Tag')) trigger_error('$tag must be an instance of Sample_Tag', E_USER_ERROR);
        $this->listTags();
        $this->_tags[] = $tag;
        
        if (is_array($tag->_people) && !Ac_Util::sameInArray($this, $tag->_people)) {
                $tag->_people[] = $this;
        }
        
    }

    /**
     * @return Sample_Tag  
     */
    function createTag($values = array()) {
        $m = $this->getMapper('Sample_Tag_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addTag($res);
        return $res;
    }
    

    function getTagIds() {
        if ($this->_tagIds === false) {
            $this->mapper->loadTagIdsFor($this);
        }
        return $this->_tagIds;
    }
    
    function setTagIds($tagIds) {
        if (!is_array($tagIds)) trigger_error('$tagIds must be an array', E_USER_ERROR);
        $this->_tagIds = $tagIds;
        $this->_tagsLoaded = false;
        $this->_tags = false; 
    }
    
    function clearTags() {
        $this->_tags = array();
        $this->_tagsLoaded = true;
        $this->_tagIds = false;
    }               

    function countPersonAlbums() {
        if (is_array($this->_personAlbums)) return count($this->_personAlbums);
        if ($this->_personAlbumsCount === false) {
            $this->mapper->loadAssocCountFor($this, '_personAlbums');
        }
        return $this->_personAlbumsCount;
        
    }

    function listPersonAlbums() {
        if (!$this->_personAlbumsLoaded) {
            $this->mapper->loadPersonAlbumsFor($this);
        }
        return array_keys($this->_personAlbums);
    }
    
    /**
     * @return bool
     */
    function isPersonAlbumsLoaded() {
        return $this->_personAlbumsLoaded;
    }
    
    /**
     * @return Sample_Person_Album 
     */
    function getPersonAlbum($id) {
        if (!$this->_personAlbumsLoaded) {
            $this->mapper->loadPersonAlbumsFor($this);
        }
        
        if (!isset($this->_personAlbums[$id])) trigger_error ('No such Person album: \''.$id.'\'', E_USER_ERROR);
        return $this->_personAlbums[$id];
    }
    
    /**
     * @return Sample_Person_Album 
     */
    function getPersonAlbumsItem($id) {
        return $this->getPersonAlbum($id);
    }
    
    /**
     * @return Sample_Person_Album[] 
     */
    function getAllPersonAlbums() {
        $res = [];
        foreach ($this->listPersonAlbums() as $id)
            $res[] = $this->getPersonAlbum($id);
        return $res;
    }
    
    /**
     * @param Sample_Person_Album $personAlbum 
     */
    function addPersonAlbum($personAlbum) {
        if (!is_a($personAlbum, 'Sample_Person_Album')) trigger_error('$personAlbum must be an instance of Sample_Person_Album', E_USER_ERROR);
        $this->listPersonAlbums();
        $this->_personAlbums[] = $personAlbum;
        
        $personAlbum->_person = $this;
        
    }

    /**
     * @return Sample_Person_Album  
     */
    function createPersonAlbum($values = array()) {
        $m = $this->getMapper('Sample_Person_Album_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addPersonAlbum($res);
        return $res;
    }
    

    function countPersonPhotos() {
        if (is_array($this->_personPhotos)) return count($this->_personPhotos);
        if ($this->_personPhotosCount === false) {
            $this->mapper->loadAssocCountFor($this, '_personPhotos');
        }
        return $this->_personPhotosCount;
        
    }

    function listPersonPhotos() {
        if (!$this->_personPhotosLoaded) {
            $this->mapper->loadPersonPhotosFor($this);
        }
        return array_keys($this->_personPhotos);
    }
    
    /**
     * @return bool
     */
    function isPersonPhotosLoaded() {
        return $this->_personPhotosLoaded;
    }
    
    /**
     * @return Sample_Person_Photo 
     */
    function getPersonPhoto($id) {
        if (!$this->_personPhotosLoaded) {
            $this->mapper->loadPersonPhotosFor($this);
        }
        
        if (!isset($this->_personPhotos[$id])) trigger_error ('No such Person photo: \''.$id.'\'', E_USER_ERROR);
        return $this->_personPhotos[$id];
    }
    
    /**
     * @return Sample_Person_Photo 
     */
    function getPersonPhotosItem($id) {
        return $this->getPersonPhoto($id);
    }
    
    /**
     * @return Sample_Person_Photo[] 
     */
    function getAllPersonPhotos() {
        $res = [];
        foreach ($this->listPersonPhotos() as $id)
            $res[] = $this->getPersonPhoto($id);
        return $res;
    }
    
    /**
     * @param Sample_Person_Photo $personPhoto 
     */
    function addPersonPhoto($personPhoto) {
        if (!is_a($personPhoto, 'Sample_Person_Photo')) trigger_error('$personPhoto must be an instance of Sample_Person_Photo', E_USER_ERROR);
        $this->listPersonPhotos();
        $this->_personPhotos[] = $personPhoto;
        
        $personPhoto->_person = $this;
        
    }

    /**
     * @return Sample_Person_Photo  
     */
    function createPersonPhoto($values = array()) {
        $m = $this->getMapper('Sample_Person_Photo_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addPersonPhoto($res);
        return $res;
    }
    

    function countPersonPosts() {
        if (is_array($this->_personPosts)) return count($this->_personPosts);
        if ($this->_personPostsCount === false) {
            $this->mapper->loadAssocCountFor($this, '_personPosts');
        }
        return $this->_personPostsCount;
        
    }

    function listPersonPosts() {
        if (!$this->_personPostsLoaded) {
            $this->mapper->loadPersonPostsFor($this);
        }
        return array_keys($this->_personPosts);
    }
    
    /**
     * @return bool
     */
    function isPersonPostsLoaded() {
        return $this->_personPostsLoaded;
    }
    
    /**
     * @return Sample_Person_Post 
     */
    function getPersonPost($id) {
        if (!$this->_personPostsLoaded) {
            $this->mapper->loadPersonPostsFor($this);
        }
        
        if (!isset($this->_personPosts[$id])) trigger_error ('No such Person post: \''.$id.'\'', E_USER_ERROR);
        return $this->_personPosts[$id];
    }
    
    /**
     * @return Sample_Person_Post 
     */
    function getPersonPostsItem($id) {
        return $this->getPersonPost($id);
    }
    
    /**
     * @return Sample_Person_Post[] 
     */
    function getAllPersonPosts() {
        $res = [];
        foreach ($this->listPersonPosts() as $id)
            $res[] = $this->getPersonPost($id);
        return $res;
    }
    
    /**
     * @param Sample_Person_Post $personPost 
     */
    function addPersonPost($personPost) {
        if (!is_a($personPost, 'Sample_Person_Post')) trigger_error('$personPost must be an instance of Sample_Person_Post', E_USER_ERROR);
        $this->listPersonPosts();
        $this->_personPosts[] = $personPost;
        
        $personPost->_person = $this;
        
    }

    /**
     * @return Sample_Person_Post  
     */
    function createPersonPost($values = array()) {
        $m = $this->getMapper('Sample_Person_Post_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addPersonPost($res);
        return $res;
    }
    

    function countAuthorPublish() {
        if (is_array($this->_authorPublish)) return count($this->_authorPublish);
        if ($this->_authorPublishCount === false) {
            $this->mapper->loadAssocCountFor($this, '_authorPublish');
        }
        return $this->_authorPublishCount;
        
    }

    function listAuthorPublish() {
        if (!$this->_authorPublishLoaded) {
            $this->mapper->loadAuthorPublishFor($this);
        }
        return array_keys($this->_authorPublish);
    }
    
    /**
     * @return bool
     */
    function isAuthorPublishLoaded() {
        return $this->_authorPublishLoaded;
    }
    
    /**
     * @return Sample_Publish 
     */
    function getAuthorPublish($id) {
        if (!$this->_authorPublishLoaded) {
            $this->mapper->loadAuthorPublishFor($this);
        }
        
        if (!isset($this->_authorPublish[$id])) trigger_error ('No such Publish: \''.$id.'\'', E_USER_ERROR);
        return $this->_authorPublish[$id];
    }
    
    /**
     * @return Sample_Publish 
     */
    function getAuthorPublishItem($id) {
        return $this->getAuthorPublish($id);
    }
    
    /**
     * @return Sample_Publish[] 
     */
    function getAllAuthorPublish() {
        $res = [];
        foreach ($this->listAuthorPublish() as $id)
            $res[] = $this->getAuthorPublish($id);
        return $res;
    }
    
    /**
     * @param Sample_Publish $authorPublish 
     */
    function addAuthorPublish($authorPublish) {
        if (!is_a($authorPublish, 'Sample_Publish')) trigger_error('$authorPublish must be an instance of Sample_Publish', E_USER_ERROR);
        $this->listAuthorPublish();
        $this->_authorPublish[] = $authorPublish;
        
        $authorPublish->_authorPerson = $this;
        
    }

    /**
     * @return Sample_Publish  
     */
    function createAuthorPublish($values = array()) {
        $m = $this->getMapper('Sample_Publish_ImplMapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addAuthorPublish($res);
        return $res;
    }
    

    function countEditorPublish() {
        if (is_array($this->_editorPublish)) return count($this->_editorPublish);
        if ($this->_editorPublishCount === false) {
            $this->mapper->loadAssocCountFor($this, '_editorPublish');
        }
        return $this->_editorPublishCount;
        
    }

    function listEditorPublish() {
        if (!$this->_editorPublishLoaded) {
            $this->mapper->loadEditorPublishFor($this);
        }
        return array_keys($this->_editorPublish);
    }
    
    /**
     * @return bool
     */
    function isEditorPublishLoaded() {
        return $this->_editorPublishLoaded;
    }
    
    /**
     * @return Sample_Publish 
     */
    function getEditorPublish($id) {
        if (!$this->_editorPublishLoaded) {
            $this->mapper->loadEditorPublishFor($this);
        }
        
        if (!isset($this->_editorPublish[$id])) trigger_error ('No such Publish: \''.$id.'\'', E_USER_ERROR);
        return $this->_editorPublish[$id];
    }
    
    /**
     * @return Sample_Publish 
     */
    function getEditorPublishItem($id) {
        return $this->getEditorPublish($id);
    }
    
    /**
     * @return Sample_Publish[] 
     */
    function getAllEditorPublish() {
        $res = [];
        foreach ($this->listEditorPublish() as $id)
            $res[] = $this->getEditorPublish($id);
        return $res;
    }
    
    /**
     * @param Sample_Publish $editorPublish 
     */
    function addEditorPublish($editorPublish) {
        if (!is_a($editorPublish, 'Sample_Publish')) trigger_error('$editorPublish must be an instance of Sample_Publish', E_USER_ERROR);
        $this->listEditorPublish();
        $this->_editorPublish[] = $editorPublish;
        
        $editorPublish->_editorPerson = $this;
        
    }

    /**
     * @return Sample_Publish  
     */
    function createEditorPublish($values = array()) {
        $m = $this->getMapper('Sample_Publish_ImplMapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addEditorPublish($res);
        return $res;
    }
    

    function countIncomingRelations() {
        if (is_array($this->_incomingRelations)) return count($this->_incomingRelations);
        if ($this->_incomingRelationsCount === false) {
            $this->mapper->loadAssocCountFor($this, '_incomingRelations');
        }
        return $this->_incomingRelationsCount;
        
    }

    function listIncomingRelations() {
        if (!$this->_incomingRelationsLoaded) {
            $this->mapper->loadIncomingRelationsFor($this);
        }
        return array_keys($this->_incomingRelations);
    }
    
    /**
     * @return bool
     */
    function isIncomingRelationsLoaded() {
        return $this->_incomingRelationsLoaded;
    }
    
    /**
     * @return Sample_Relation 
     */
    function getIncomingRelation($id) {
        if (!$this->_incomingRelationsLoaded) {
            $this->mapper->loadIncomingRelationsFor($this);
        }
        
        if (!isset($this->_incomingRelations[$id])) trigger_error ('No such Relation: \''.$id.'\'', E_USER_ERROR);
        return $this->_incomingRelations[$id];
    }
    
    /**
     * @return Sample_Relation 
     */
    function getIncomingRelationsItem($id) {
        return $this->getIncomingRelation($id);
    }
    
    /**
     * @return Sample_Relation[] 
     */
    function getAllIncomingRelations() {
        $res = [];
        foreach ($this->listIncomingRelations() as $id)
            $res[] = $this->getIncomingRelation($id);
        return $res;
    }
    
    /**
     * @param Sample_Relation $incomingRelation 
     */
    function addIncomingRelation($incomingRelation) {
        if (!is_a($incomingRelation, 'Sample_Relation')) trigger_error('$incomingRelation must be an instance of Sample_Relation', E_USER_ERROR);
        $this->listIncomingRelations();
        $this->_incomingRelations[] = $incomingRelation;
        
        $incomingRelation->_otherPerson = $this;
        
    }

    /**
     * @return Sample_Relation  
     */
    function createIncomingRelation($values = array()) {
        $m = $this->getMapper('Sample_Relation_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addIncomingRelation($res);
        return $res;
    }
    

    function countOutgoingRelations() {
        if (is_array($this->_outgoingRelations)) return count($this->_outgoingRelations);
        if ($this->_outgoingRelationsCount === false) {
            $this->mapper->loadAssocCountFor($this, '_outgoingRelations');
        }
        return $this->_outgoingRelationsCount;
        
    }

    function listOutgoingRelations() {
        if (!$this->_outgoingRelationsLoaded) {
            $this->mapper->loadOutgoingRelationsFor($this);
        }
        return array_keys($this->_outgoingRelations);
    }
    
    /**
     * @return bool
     */
    function isOutgoingRelationsLoaded() {
        return $this->_outgoingRelationsLoaded;
    }
    
    /**
     * @return Sample_Relation 
     */
    function getOutgoingRelation($id) {
        if (!$this->_outgoingRelationsLoaded) {
            $this->mapper->loadOutgoingRelationsFor($this);
        }
        
        if (!isset($this->_outgoingRelations[$id])) trigger_error ('No such Relation: \''.$id.'\'', E_USER_ERROR);
        return $this->_outgoingRelations[$id];
    }
    
    /**
     * @return Sample_Relation 
     */
    function getOutgoingRelationsItem($id) {
        return $this->getOutgoingRelation($id);
    }
    
    /**
     * @return Sample_Relation[] 
     */
    function getAllOutgoingRelations() {
        $res = [];
        foreach ($this->listOutgoingRelations() as $id)
            $res[] = $this->getOutgoingRelation($id);
        return $res;
    }
    
    /**
     * @param Sample_Relation $outgoingRelation 
     */
    function addOutgoingRelation($outgoingRelation) {
        if (!is_a($outgoingRelation, 'Sample_Relation')) trigger_error('$outgoingRelation must be an instance of Sample_Relation', E_USER_ERROR);
        $this->listOutgoingRelations();
        $this->_outgoingRelations[] = $outgoingRelation;
        
        $outgoingRelation->_person = $this;
        
    }

    /**
     * @return Sample_Relation  
     */
    function createOutgoingRelation($values = array()) {
        $m = $this->getMapper('Sample_Relation_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addOutgoingRelation($res);
        return $res;
    }
    

    function countExtraCodeShopProducts() {
        if (is_array($this->_extraCodeShopProducts)) return count($this->_extraCodeShopProducts);
        return 0;
        
    }

    function listExtraCodeShopProducts() {
        if (!is_array($this->_extraCodeShopProducts)) $this->_extraCodeShopProducts = array();
        return array_keys($this->_extraCodeShopProducts);
    }
    
    /**
     * @return bool
     */
    function isExtraCodeShopProductsLoaded() {
        return $this->_shopProductsLoaded;
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function getExtraCodeShopProduct($id) {
        
        if (!isset($this->_extraCodeShopProducts[$id])) trigger_error ('No such Shop product: \''.$id.'\'', E_USER_ERROR);
        return $this->_extraCodeShopProducts[$id];
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function getExtraCodeShopProductsItem($id) {
        return $this->getExtraCodeShopProduct($id);
    }
    
    /**
     * @return Sample_Shop_Product[] 
     */
    function getAllExtraCodeShopProducts() {
        $res = [];
        foreach ($this->listExtraCodeShopProducts() as $id)
            $res[] = $this->getExtraCodeShopProduct($id);
        return $res;
    }
    
    /**
     * @param Sample_Shop_Product $extraCodeShopProduct 
     */
    function addExtraCodeShopProduct($extraCodeShopProduct) {
        if (!is_a($extraCodeShopProduct, 'Sample_Shop_Product')) trigger_error('$extraCodeShopProduct must be an instance of Sample_Shop_Product', E_USER_ERROR);
        $this->listExtraCodeShopProducts();
        $this->_extraCodeShopProducts[] = $extraCodeShopProduct;
        
        
    }

    /**
     * @return Sample_Shop_Product  
     */
    function createExtraCodeShopProduct($values = array()) {
        $m = $this->getMapper('Sample_Shop_Product_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addExtraCodeShopProduct($res);
        return $res;
    }
    

    function countNoteShopProducts() {
        if (is_array($this->_noteShopProducts)) return count($this->_noteShopProducts);
        return 0;
        
    }

    function listNoteShopProducts() {
        if (!is_array($this->_noteShopProducts)) $this->_noteShopProducts = array();
        return array_keys($this->_noteShopProducts);
    }
    
    /**
     * @return bool
     */
    function isNoteShopProductsLoaded() {
        return $this->_shopProductsLoaded;
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function getNoteShopProduct($id) {
        
        if (!isset($this->_noteShopProducts[$id])) trigger_error ('No such Shop product: \''.$id.'\'', E_USER_ERROR);
        return $this->_noteShopProducts[$id];
    }
    
    /**
     * @return Sample_Shop_Product 
     */
    function getNoteShopProductsItem($id) {
        return $this->getNoteShopProduct($id);
    }
    
    /**
     * @return Sample_Shop_Product[] 
     */
    function getAllNoteShopProducts() {
        $res = [];
        foreach ($this->listNoteShopProducts() as $id)
            $res[] = $this->getNoteShopProduct($id);
        return $res;
    }
    
    /**
     * @param Sample_Shop_Product $noteShopProduct 
     */
    function addNoteShopProduct($noteShopProduct) {
        if (!is_a($noteShopProduct, 'Sample_Shop_Product')) trigger_error('$noteShopProduct must be an instance of Sample_Shop_Product', E_USER_ERROR);
        $this->listNoteShopProducts();
        $this->_noteShopProducts[] = $noteShopProduct;
        
        $noteShopProduct->_notePerson = $this;
        
    }

    /**
     * @return Sample_Shop_Product  
     */
    function createNoteShopProduct($values = array()) {
        $m = $this->getMapper('Sample_Shop_Product_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        $this->addNoteShopProduct($res);
        return $res;
    }
    
  
    
}

