<?php

class Sample_Perk_Base_Object extends Ac_Model_Object {

    public $_hasDefaults = true;
    public $_tags = false;
    public $_tagsCount = false;
    public $_tagsLoaded = false;
    public $_tagIds = false;
    public $perkId = NULL;
    public $name = '';
    
    var $_mapperClass = 'Sample_Perk_Mapper';
    
    /**
     * @var Sample_Perk_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Perk_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
 
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), array ( 0 => 'tags', 1 => 'tagIds', )));
    }
 
    protected function listOwnLists() {
        
        return array ( 'tags' => 'tags', );
    }

    
 
    protected function listOwnAssociations() {
        return array ( 'tags' => 'Sample_Tag', );
    }

    protected function getOwnPropertiesInfo() {
    	static $pi = false; if ($pi === false) $pi = array (
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
            'perkId' => array (
                'dataType' => 'int',
                'maxLength' => '11',
                'attribs' => array (
                    'size' => '6',
                ),
                'caption' => 'Perk Id',
            ),
            'name' => array (
                'maxLength' => '45',
                'isNullable' => true,
                'caption' => 'Name',
            ),
        );
    
        return $pi;
                
    }

    function hasUniformPropertiesInfo() { return true; }

    function tracksChanges() { return true; }

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
        
        if (is_array($tag->_perks) && !Ac_Util::sameInArray($this, $tag->_perks)) {
                $tag->_perks[] = $this;
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
  
    
}

