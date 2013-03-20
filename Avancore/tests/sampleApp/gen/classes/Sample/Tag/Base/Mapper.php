<?php

class Sample_Tag_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'tagId'; 

    var $recordClass = 'Sample_Tag'; 

    var $tableName = '#__tags'; 

    var $id = 'Sample_Tag_Mapper'; 

    var $columnNames = array ( 'tagId', 'title', 'titleM', 'titleF', ); 

    var $defaults = array (
              'tagId' => NULL,
              'title' => NULL,
              'titleM' => NULL,
              'titleF' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'tagId';
    
    /**
     * @return Sample_Tag 
     */ 
    function factory ($className = false) {
        $res = parent::factory($className);
        return $res;
    }
    
    /**
     * @return Sample_Tag 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Tag 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Tag 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Tag 
     */ 
    function loadSingleRecord($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount);
    }

        
    function getTitleFieldName() {
        return 'title';   
    }
                
    function _getRelationPrototypes() {
        return Ac_Util::m(parent::_getRelationPrototypes(), array (
              '_people' => array (
                  'srcMapperClass' => 'Sample_Tag_Mapper',
                  'destMapperClass' => 'Sample_People_Mapper',
                  'srcVarName' => '_people',
                  'srcNNIdsVarName' => '_peopleIds',
                  'srcCountVarName' => '_peopleCount',
                  'destVarName' => '_tags',
                  'destCountVarName' => '_tagsCount',
                  'destNNIdsVarName' => '_tagIds',
                  'fieldLinks' => array (
                      'tagId' => 'tagId',
                  ),
                  'srcIsUnique' => false,
                  'destIsUnique' => false,
                  'midTableName' => '#__people_tags',
                  'fieldLinks2' => array (
                      'personId' => 'personId',
                  ),
              ),
        ));
        
    }
        
    function _doGetInfoParams() {
        return array (
              'singleCaption' => 'Tag',
              'pluralCaption' => 'Tags',
              'hasUi' => false,
        );
    }
    
        
    function _doGetUniqueIndexData() {
        return array (
              'PRIMARY' => array (
                  'tagId',
              ),
              'Index_2' => array (
                  'title',
              ),
        );
    }
        
    /**
     * @return Sample_Tag 
     */
    function loadByTagId ($tagId) {
        $recs = $this->loadRecordsByCriteria(''.$this->database->NameQuote('tagId').' = '.$this->database->Quote($tagId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Tag 
     */
    function loadByTitle ($title) {
        $recs = $this->loadRecordsByCriteria(''.$this->database->NameQuote('title').' = '.$this->database->Quote($title).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) one or more tags of given one or more people 
     * @param Sample_Tag|array $people     
     * @return Sample_Tag|array of Sample_Tag objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_people');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads one or more tags of given one or more people 
     * @param Sample_People|array $people of Sample_Tag objects
     
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_people');
        return $rel->loadSrc($people); 
    }

    /**
     * Loads one or more people of given one or more tags 
     * @param Sample_Tag|array $tags     
     */
    function loadPeopleFor($tags) {
        $rel = $this->getRelation('_people');
        return $rel->loadDest($tags); 
    }

    
}

