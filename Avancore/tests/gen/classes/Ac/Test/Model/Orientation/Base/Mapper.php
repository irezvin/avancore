<?php

Ac_Dispatcher::loadClass('Ac_Model_Mapper');

class Ac_Test_Model_Orientation_Base_Mapper extends Ac_Model_Mapper {
    
    /**
     * @return Ac_Test_Model_Orientation 
     */ 
    function & factory ($className = false) {
        $res = & parent::factory($className);
        return $res;
    }
    
    /**
     * @return Ac_Test_Model_Orientation 
     */ 
    function & reference ($values = array()) {
        $res = & parent::reference($values);
        return $res;
    }
    
    /**
     * @return Ac_Test_Model_Orientation 
     */ 
    function & loadRecord ($id) {
        $res = & parent::loadRecord($id);
        return $res;
    }
    
    function Ac_Test_Model_Orientation_Base_Mapper () {
        parent::Ac_Model_Mapper('#__orientation', 'Ac_Test_Model_Orientation', 'sexualOrientationId');
    }
        
    function getTitleFieldName() {
        return 'title';   
    }
                
    function _getRelationPrototypes() {
        return array (
              '_people' => array (  
                  'srcMapperClass' => 'Ac_Test_Model_Orientation_Mapper',
                  'destMapperClass' => 'Ac_Test_Model_People_Mapper',
                  'srcVarName' => '_people',
                  'srcCountVarName' => '_peopleCount',
                  'destVarName' => '_orientation',
                  'fieldLinks' => array (    
                      'sexualOrientationId' => 'sexualOrientationId',
                  ),
                  'srcIsUnique' => true,
                  'destIsUnique' => false,
              ),
        );
    }
        
    function _doGetInfoParams() {
        return array (
              'singleCaption' => 'Orientation',
              'pluralCaption' => 'Orientation',
              'hasUi' => false,
        );
    }
    
        
    function _doGetUniqueIndexData() {
        return array (
              'PRIMARY' => array (  
                  'sexualOrientationId',
              ),
        );
    }
            
    function getAutoincFieldName() {
        return 'sexualOrientationId';
    }
        
    /**
     * @return Ac_Test_Model_Orientation     
     */
    function & loadBySexualOrientationId ($sexualOrientationId) {
        $recs = $this->loadRecordsByCriteria('`sexualOrientationId` = '.$this->database->Quote($sexualOrientationId).'');
        if (count($recs)) $res = & $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) one or more orientation of given one or more people 
     * @param Ac_Test_Model_Orientation|array $people     
     * @return array of Ac_Test_Model_Orientation objects  
     */
    function & getOfPeople($people) {
        $rel = & $this->getRelation('_people');
        $res = & $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads one or more orientation of given one or more people 
     * @param Ac_Test_Model_People|array $people of Ac_Test_Model_Orientation objects
     
     */
    function loadForPeople($people) {
        $rel = & $this->getRelation('_people');
        return $rel->loadSrc($people); 
    }

    /**
     * Loads one or more people of given one or more orientation 
     * @param Ac_Test_Model_Orientation|array $orientation     
     */
    function loadPeopleFor($orientation) {
        $rel = & $this->getRelation('_people');
        return $rel->loadDest($orientation); 
    }

    
}

?>