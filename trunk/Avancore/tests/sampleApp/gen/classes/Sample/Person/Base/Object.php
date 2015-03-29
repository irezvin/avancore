<?php

class Sample_Person_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $_portraitPersonPhoto = false;
    public $_religion = false;
    public $_tags = false;
    public $_tagsCount = false;
    public $_tagsLoaded = false;
    public $_tagIds = false;
    public $_personAlbums = false;
    public $_personAlbumsCount = false;
    public $_personAlbumsLoaded = false;
    public $_personPhotos = false;
    public $_personPhotosCount = false;
    public $_personPhotosLoaded = false;
    public $_personPosts = false;
    public $_personPostsCount = false;
    public $_personPostsLoaded = false;
    public $_authorPublish = false;
    public $_authorPublishCount = false;
    public $_authorPublishLoaded = false;
    public $_editorPublish = false;
    public $_editorPublishCount = false;
    public $_editorPublishLoaded = false;
    public $_incomingRelations = false;
    public $_incomingRelationsCount = false;
    public $_incomingRelationsLoaded = false;
    public $_outgoingRelations = false;
    public $_outgoingRelationsCount = false;
    public $_outgoingRelationsLoaded = false;
    public $personId = NULL;
    public $name = '';
    public $gender = 'F';
    public $isSingle = 1;
    public $birthDate = NULL;
    public $lastUpdatedDatetime = NULL;
    public $createdTs = false;
    public $religionId = NULL;
    public $portraitId = NULL;
    
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
        return array ( 0 => 'portraitPersonPhoto', 1 => 'religion', 2 => 'tags', 3 => 'tagIds', 4 => 'personAlbums', 5 => 'personPhotos', 6 => 'personPosts', 7 => 'authorPublish', 8 => 'editorPublish', 9 => 'incomingRelations', 10 => 'outgoingRelations', 11 => 'personId', 12 => 'name', 13 => 'gender', 14 => 'isSingle', 15 => 'birthDate', 16 => 'lastUpdatedDatetime', 17 => 'createdTs', 18 => 'religionId', 19 => 'portraitId', );
    }
 
    protected function listOwnLists() {
        
        return array ( 'tags' => 'tags', 'personAlbums' => 'personAlbums', 'personPhotos' => 'personPhotos', 'personPosts' => 'personPosts', 'authorPublish' => 'authorPublish', 'editorPublish' => 'editorPublish', 'incomingRelations' => 'incomingRelations', 'outgoingRelations' => 'outgoingRelations', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'portraitPersonPhoto' => 'Sample_Person_Photo', 'religion' => 'Sample_Religion', 'tags' => 'Sample_Tag', 'personAlbums' => 'Sample_Person_Album', 'personPhotos' => 'Sample_Person_Photo', 'personPosts' => 'Sample_Person_Post', 'authorPublish' => 'Sample_Publish', 'editorPublish' => 'Sample_Publish', 'incomingRelations' => 'Sample_Relation', 'outgoingRelations' => 'Sample_Relation', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
            'portraitPersonPhoto' => array (
                'className' => 'Sample_Person_Photo',
                'mapperClass' => 'Sample_Person_Photo_Mapper',
                'otherModelIdInMethodsPrefix' => 'portrait',
                'caption' => 'Person photo',
                'relationId' => '_portraitPersonPhoto',
                'referenceVarName' => '_portraitPersonPhoto',
            ),
            'religion' => array (
                'className' => 'Sample_Religion',
                'mapperClass' => 'Sample_Religion_Mapper',
                'caption' => 'Religion',
                'relationId' => '_religion',
                'referenceVarName' => '_religion',
            ),
            'tags' => array (
                'className' => 'Sample_Tag',
                'mapperClass' => 'Sample_Tag_Mapper',
                'caption' => 'Tags',
                'relationId' => '_tags',
                'countVarName' => '_tagsCount',
                'nnIdsVarName' => '_tagIds',
                'referenceVarName' => '_tags',
            ),
            'tagIds' => array (
                'dataType' => 'int',
                'arrayValue' => true,
                'controlType' => 'selectList',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Tag_Mapper',
                ),
                'showInTable' => false,
            ),
            'personAlbums' => array (
                'className' => 'Sample_Person_Album',
                'mapperClass' => 'Sample_Person_Album_Mapper',
                'caption' => 'Person albums',
                'relationId' => '_personAlbums',
                'countVarName' => '_personAlbumsCount',
                'referenceVarName' => '_personAlbums',
            ),
            'personPhotos' => array (
                'className' => 'Sample_Person_Photo',
                'mapperClass' => 'Sample_Person_Photo_Mapper',
                'caption' => 'Person photos',
                'relationId' => '_personPhotos',
                'countVarName' => '_personPhotosCount',
                'referenceVarName' => '_personPhotos',
            ),
            'personPosts' => array (
                'className' => 'Sample_Person_Post',
                'mapperClass' => 'Sample_Person_Post_Mapper',
                'caption' => 'Person posts',
                'relationId' => '_personPosts',
                'countVarName' => '_personPostsCount',
                'referenceVarName' => '_personPosts',
            ),
            'authorPublish' => array (
                'className' => 'Sample_Publish',
                'mapperClass' => 'Sample_Publish_ImplMapper',
                'otherModelIdInMethodsPrefix' => 'author',
                'caption' => 'Publish',
                'relationId' => '_authorPublish',
                'countVarName' => '_authorPublishCount',
                'referenceVarName' => '_authorPublish',
            ),
            'editorPublish' => array (
                'className' => 'Sample_Publish',
                'mapperClass' => 'Sample_Publish_ImplMapper',
                'otherModelIdInMethodsPrefix' => 'editor',
                'caption' => 'Publish',
                'relationId' => '_editorPublish',
                'countVarName' => '_editorPublishCount',
                'referenceVarName' => '_editorPublish',
            ),
            'incomingRelations' => array (
                'className' => 'Sample_Relation',
                'mapperClass' => 'Sample_Relation_Mapper',
                'otherModelIdInMethodsSingle' => 'incomingRelation',
                'otherModelIdInMethodsPlural' => 'incomingRelations',
                'caption' => 'Relations',
                'relationId' => '_incomingRelations',
                'countVarName' => '_incomingRelationsCount',
                'referenceVarName' => '_incomingRelations',
            ),
            'outgoingRelations' => array (
                'className' => 'Sample_Relation',
                'mapperClass' => 'Sample_Relation_Mapper',
                'otherModelIdInMethodsSingle' => 'outgoingRelation',
                'otherModelIdInMethodsPlural' => 'outgoingRelations',
                'caption' => 'Relations',
                'relationId' => '_outgoingRelations',
                'countVarName' => '_outgoingRelationsCount',
                'referenceVarName' => '_outgoingRelations',
            ),
            'personId' => array (
                'dataType' => 'int',
                'maxLength' => '10',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Person Id',
            ),
            'name' => array (
                'maxLength' => '255',
                'caption' => 'Name',
            ),
            'gender' => array (
                'controlType' => 'selectList',
                'valueList' => array (
                    'F' => 'F',
                    'M' => 'M',
                ),
                'caption' => 'Gender',
            ),
            'isSingle' => array (
                'dataType' => 'bool',
                'controlType' => 'selectList',
                'maxLength' => '1',
                'valueList' => array (
                    0 => 'No',
                    1 => 'Yes',
                ),
                'caption' => 'Is Single',
            ),
            'birthDate' => array (
                'dataType' => 'date',
                'controlType' => 'dateInput',
                'caption' => 'Birth Date',
                'internalDateFormat' => 'Y-m-d',
                'outputDateFormat' => 'Y-m-d',
            ),
            'lastUpdatedDatetime' => array (
                'dataType' => 'dateTime',
                'controlType' => 'dateInput',
                'isNullable' => true,
                'caption' => 'Last Updated Datetime',
                'internalDateFormat' => 'Y-m-d H:i:s',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
            'createdTs' => array (
                'dataType' => 'timestamp',
                'controlType' => 'dateInput',
                'caption' => 'Created Ts',
                'internalDateFormat' => 'YmdHis',
                'outputDateFormat' => 'Y-m-d H:i:s',
            ),
            'religionId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Religion_Mapper',
                ),
                'objectPropertyName' => 'religion',
                'isNullable' => true,
                'caption' => 'Religion Id',
            ),
            'portraitId' => array (
                'dataType' => 'int',
                'controlType' => 'selectList',
                'maxLength' => '10',
                'dummyCaption' => '',
                'values' => array (
                    'class' => 'Ac_Model_Values_Records',
                    'mapperClass' => 'Sample_Person_Photo_Mapper',
                ),
                'objectPropertyName' => 'portraitPersonPhoto',
                'isNullable' => true,
                'caption' => 'Portrait Id',
            ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }
        
    
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
    function createPortraitPersonPhoto($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Photo_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
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
    function createReligion($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Religion_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
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
    function createTag($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Tag_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
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
    function createPersonAlbum($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Album_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
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
    function createPersonPhoto($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Photo_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
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
    function createPersonPost($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Person_Post_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
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
    function createAuthorPublish($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Publish_ImplMapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
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
    function createEditorPublish($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Publish_ImplMapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
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
    function createIncomingRelation($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Relation_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
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
    function createOutgoingRelation($values = array(), $isReference = false) {
        $m = $this->getMapper('Sample_Relation_Mapper');
        $res = $m->createRecord();
        if ($values) $res->bind($values);
        if ($isReference) $res->_setIsReference(true);
        $this->addOutgoingRelation($res);
        return $res;
    }
    
  
    
}

