<?php

Ac_Dispatcher::loadClass('Ac_Model_Mapper');

class Ac_Test_Model_Tag_Base_Mapper extends Ac_Model_Mapper {
    
    /**
     * @return Ac_Test_Model_Tag 
     */ 
    function & factory ($className = false) {
        $res = & parent::factory($className);
        return $res;
    }
    
    /**
     * @return Ac_Test_Model_Tag 
     */ 
    function & reference ($values = array()) {
        $res = & parent::reference($values);
        return $res;
    }
    
    /**
     * @return Ac_Test_Model_Tag 
     */ 
    function & loadRecord ($id) {
        $res = & parent::loadRecord($id);
        return $res;
    }
    
    function Ac_Test_Model_Tag_Base_Mapper () {
        parent::Ac_Model_Mapper('#__tags', 'Ac_Test_Model_Tag', 'tagId');
    }
        
    function getTitleFieldName() {
        return 'title';   
    }
                
    function _getRelationPrototypes() {
        return array (
              '_people' => array (  
                  'srcMapperClass' => 'Ac_Test_Model_Tag_Mapper',
                  'destMapperClass' => 'Ac_Test_Model_People_Mapper',
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
        );
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
            
    function getAutoincFieldName() {
        return 'tagId';
    }
        
    /**
     * @return Ac_Test_Model_Tag     
     */
    function & loadByTagId ($tagId) {
        $recs = $this->loadRecordsByCriteria('`tagId` = '.$this->database->Quote($tagId).'');
        if (count($recs)) $res = & $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Ac_Test_Model_Tag     
     */
    function & loadByTitle ($title) {
        $recs = $this->loadRecordsByCriteria('`title` = '.$this->database->Quote($title).'');
        if (count($recs)) $res = & $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) one or more tags of given one or more people 
     * @param Ac_Test_Model_Tag|array $people     
     * @return Ac_Test_Model_Tag|array of Ac_Test_Model_Tag objects  
     */
    function & getOfPeople($people) {
        $rel = & $this->getRelation('_people');
        $res = & $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads one or more tags of given one or more people 
     * @param Ac_Test_Model_People|array $people of Ac_Test_Model_Tag objects
     
     */
    function loadForPeople($people) {
        $rel = & $this->getRelation('_people');
        return $rel->loadSrc($people); 
    }

    /**
     * Loads one or more people of given one or more tags 
     * @param Ac_Test_Model_Tag|array $tags     
     */
    function loadPeopleFor($tags) {
        $rel = & $this->getRelation('_people');
        return $rel->loadDest($tags); 
    }

    
}

?>