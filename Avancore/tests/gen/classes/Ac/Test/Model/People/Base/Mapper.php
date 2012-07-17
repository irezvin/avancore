<?php

Ac_Dispatcher::loadClass('Ac_Model_Mapper');

class Ac_Test_Model_People_Base_Mapper extends Ac_Model_Mapper {
    
    /**
     * @return Ac_Test_Model_People 
     */ 
    function & factory ($className = false) {
        $res = & parent::factory($className);
        return $res;
    }
    
    /**
     * @return Ac_Test_Model_People 
     */ 
    function & reference ($values = array()) {
        $res = & parent::reference($values);
        return $res;
    }
    
    /**
     * @return Ac_Test_Model_People 
     */ 
    function & loadRecord ($id) {
        $res = & parent::loadRecord($id);
        return $res;
    }
    
    function Ac_Test_Model_People_Base_Mapper () {
        parent::Ac_Model_Mapper('#__people', 'Ac_Test_Model_People', 'personId');
    }
                
    function _getRelationPrototypes() {
        return array (
              '_orientation' => array (  
                  'srcMapperClass' => 'Ac_Test_Model_People_Mapper',
                  'destMapperClass' => 'Ac_Test_Model_Orientation_Mapper',
                  'srcVarName' => '_orientation',
                  'destVarName' => '_people',
                  'destCountVarName' => '_peopleCount',
                  'fieldLinks' => array (    
                      'sexualOrientationId' => 'sexualOrientationId',
                  ),
                  'srcIsUnique' => false,
                  'destIsUnique' => true,
                  'srcOutgoing' => true,
              ),
              '_tags' => array (  
                  'srcMapperClass' => 'Ac_Test_Model_People_Mapper',
                  'destMapperClass' => 'Ac_Test_Model_Tag_Mapper',
                  'srcVarName' => '_tags',
                  'srcNNIdsVarName' => '_tagIds',
                  'srcCountVarName' => '_tagsCount',
                  'destVarName' => '_people',
                  'destCountVarName' => '_peopleCount',
                  'destNNIdsVarName' => '_peopleIds',
                  'fieldLinks' => array (    
                      'personId' => 'personId',
                  ),
                  'srcIsUnique' => false,
                  'destIsUnique' => false,
                  'midTableName' => '#__people_tags',
                  'fieldLinks2' => array (    
                      'tagId' => 'tagId',
                  ),
              ),
              '_outgoingRelations' => array (  
                  'srcMapperClass' => 'Ac_Test_Model_People_Mapper',
                  'destMapperClass' => 'Ac_Test_Model_Relation_Mapper',
                  'srcVarName' => '_outgoingRelations',
                  'srcCountVarName' => '_outgoingRelationsCount',
                  'destVarName' => '_person',
                  'fieldLinks' => array (    
                      'personId' => 'personId',
                  ),
                  'srcIsUnique' => true,
                  'destIsUnique' => false,
              ),
              '_incomingRelations' => array (  
                  'srcMapperClass' => 'Ac_Test_Model_People_Mapper',
                  'destMapperClass' => 'Ac_Test_Model_Relation_Mapper',
                  'srcVarName' => '_incomingRelations',
                  'srcCountVarName' => '_incomingRelationsCount',
                  'destVarName' => '_otherPerson',
                  'fieldLinks' => array (    
                      'personId' => 'otherPersonId',
                  ),
                  'srcIsUnique' => true,
                  'destIsUnique' => false,
              ),
        );
    }
        
    function _doGetInfoParams() {
        return array (
              'singleCaption' => 'People',
              'pluralCaption' => 'People',
              'hasUi' => false,
        );
    }
    
        
    function _doGetUniqueIndexData() {
        return array (
              'PRIMARY' => array (  
                  'personId',
              ),
        );
    }
            
    function getAutoincFieldName() {
        return 'personId';
    }
        
    /**
     * @return Ac_Test_Model_People     
     */
    function & loadByPersonId ($personId) {
        $recs = $this->loadRecordsByCriteria('`personId` = '.$this->database->Quote($personId).'');
        if (count($recs)) $res = & $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) several people of given one or more orientation 
     * @param Ac_Test_Model_People|array $orientation     
     * @return Ac_Test_Model_People|array of Ac_Test_Model_People objects  
     */
    function & getOfOrientation($orientation) {
        $rel = & $this->getRelation('_orientation');
        $res = & $rel->getSrc($orientation); 
        return $res;
    }
    
    /**
     * Loads several people of given one or more orientation 
     * @param Ac_Test_Model_Orientation|array $orientation of Ac_Test_Model_People objects
     
     */
    function loadForOrientation($orientation) {
        $rel = & $this->getRelation('_orientation');
        return $rel->loadSrc($orientation); 
    }

    /**
     * Loads several orientation of given one or more people 
     * @param Ac_Test_Model_People|array $people     
     */
    function loadOrientationFor($people) {
        $rel = & $this->getRelation('_orientation');
        return $rel->loadDest($people); 
    }


    /**
     * Returns (but not loads!) one or more people of given one or more tags 
     * @param Ac_Test_Model_People|array $tags     
     * @return Ac_Test_Model_People|array of Ac_Test_Model_People objects  
     */
    function & getOfTags($tags) {
        $rel = & $this->getRelation('_tags');
        $res = & $rel->getSrc($tags); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more tags 
     * @param Ac_Test_Model_Tag|array $tags of Ac_Test_Model_People objects
     
     */
    function loadForTags($tags) {
        $rel = & $this->getRelation('_tags');
        return $rel->loadSrc($tags); 
    }

    /**
     * Loads one or more tags of given one or more people 
     * @param Ac_Test_Model_People|array $people     
     */
    function loadTagsFor($people) {
        $rel = & $this->getRelation('_tags');
        return $rel->loadDest($people); 
    }


    /**
     * Returns (but not loads!) one or more people of given one or more relations 
     * @param Ac_Test_Model_People|array $outgoingRelations     
     * @return array of Ac_Test_Model_People objects  
     */
    function & getOfOutgoingRelations($outgoingRelations) {
        $rel = & $this->getRelation('_outgoingRelations');
        $res = & $rel->getSrc($outgoingRelations); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more relations 
     * @param Ac_Test_Model_Relation|array $outgoingRelations of Ac_Test_Model_People objects
     
     */
    function loadForOutgoingRelations($outgoingRelations) {
        $rel = & $this->getRelation('_outgoingRelations');
        return $rel->loadSrc($outgoingRelations); 
    }

    /**
     * Loads one or more relations of given one or more people 
     * @param Ac_Test_Model_People|array $people     
     */
    function loadOutgoingRelationsFor($people) {
        $rel = & $this->getRelation('_outgoingRelations');
        return $rel->loadDest($people); 
    }


    /**
     * Returns (but not loads!) one or more people of given one or more relations 
     * @param Ac_Test_Model_People|array $incomingRelations     
     * @return array of Ac_Test_Model_People objects  
     */
    function & getOfIncomingRelations($incomingRelations) {
        $rel = & $this->getRelation('_incomingRelations');
        $res = & $rel->getSrc($incomingRelations); 
        return $res;
    }
    
    /**
     * Loads one or more people of given one or more relations 
     * @param Ac_Test_Model_Relation|array $incomingRelations of Ac_Test_Model_People objects
     
     */
    function loadForIncomingRelations($incomingRelations) {
        $rel = & $this->getRelation('_incomingRelations');
        return $rel->loadSrc($incomingRelations); 
    }

    /**
     * Loads one or more relations of given one or more people 
     * @param Ac_Test_Model_People|array $people     
     */
    function loadIncomingRelationsFor($people) {
        $rel = & $this->getRelation('_incomingRelations');
        return $rel->loadDest($people); 
    }

    
}

?>