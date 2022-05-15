<?php
/**
 * @property Sample $app Access to App instance (via Mapper)
 */
class Sample_Perk_Base_Object extends Ac_Model_Object {


    var $_hasDefaults = true;

    var $_tags = false;

    var $_tagsCount = false;

    var $_tagsLoaded = false;

    var $_tagIds = false;

    var $perkId = NULL;

    var $name = '';
    
    var $_mapperClass = 'Sample_Perk_Mapper';
    
    /**
     * @var Sample_Perk_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Sample_Perk_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), [ 0 => 'tags', 1 => 'tagIds', ]));
    }
    
 
    protected function listOwnLists() {
        
        return [ 'tags' => 'tags', ];
    }

    
 
    protected function listOwnAssociations() {
        return [ 'tags' => 'Sample_Tag', ];
    }

    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'tags' => [
                'className' => 'Sample_Tag',
                'mapperClass' => 'Sample_Tag_Mapper',

                'caption' => new Ac_Lang_String('sample_perk_tags'),
                'idsPropertyName' => 'tagIds',
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
                'assocPropertyName' => 'tags',
            ],
            'perkId' => [
                'dataType' => 'int',
                'maxLength' => '11',
                'attribs' => [
                    'size' => '6',
                ],

                'caption' => new Ac_Lang_String('sample_perk_perk_id'),
            ],
            'name' => [
                'maxLength' => '45',
                'isNullable' => true,

                'caption' => new Ac_Lang_String('sample_perk_name'),
            ],
        ];
    
        return $pi;
                
    }
    

    function hasUniformPropertiesInfo() { return true; }

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
        
        if (is_array($tag->_perks) && !Ac_Util::sameInArray($this, $tag->_perks)) {
                $tag->_perks[] = $this;
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
  
    
}

