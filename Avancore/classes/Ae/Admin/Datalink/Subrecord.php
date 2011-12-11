<?php

Ae_Dispatcher::loadClass('Ae_Admin_Datalink');

class Ae_Admin_Datalink_Subrecord extends Ae_Admin_Datalink {
    
    var $_relationId = false;
    
    var $_mapperClass = false;
    
    /**
     * @var Ae_Model_Relation
     */
    var $_relation = false;
    
    /**
     * @var Ae_Model_Record
     */
    var $_parentRecord = false;
   
    function Ae_Admin_Datalink_Subrecord($params = array()) {
        parent::Ae_Admin_Datalink($params);
        if (
               isset($params['mapperClass']) && strlen($params['mapperClass'])
            && isset($params['relationId']) && strlen($params['relationId'])
        ) $this->setRelationId($params['mapperClass'], $params['relationId']);
    }
    
    function getCacheId() {
        if (is_a($this->_parentRecord, 'Ae_Model_Object')) return $this->_parentRecord->getPrimaryKey();
            else return false;
    }
    
    /**
     * @param Ae_Model_Relation $relation
     */
    function setRelation(& $relation) {
        $this->_relation = & $relation;
        $this->_mapperClass = false;
        $this->_relationId = false;
    }
    
    function setRelationId($mapperClass, $relationId) {
        $this->_relation = false;
        $this->_mapperClass = $mapperClass;
        $this->_relationId = $relationId; 
    }
    
    function setParentRecord(& $record) {
        $this->_parentRecord = & $record;
    }
    
    /**
     * @return Ae_Model_Relation
     */
    function getRelation() {
        if ($this->_relation === false) {
            if (!strlen($this->_mapperClass) || !strlen($this->_relationId)) trigger_error ("call setRelationId() first", E_USER_ERROR);
            $mapper = & Ae_Dispatcher::getMapper($this->_mapperClass);
            $this->_relation = & $mapper->getRelation($this->_relationId);
        } else {
            if (is_array($this->_relation)) {
                Ae_Dispatcher::loadClass('Ae_Model_Relation');
                $c = $this->_relation;
                $this->_relation = new Ae_Model_Relation($c);
            }
        }
        return $this->_relation;
    }
    
    /**
     * @return Ae_Model_Record
     */
    function getRecord() {
        if ($this->_parentRecord === false) {
            $this->_parentRecord = null;
            if ($this->_manager) {
                $this->_parentRecord = & $this->_manager->getRecord();
                if (!$this->_parentRecord) var_dump("No record in manager ".$this->_manager->_instanceId);
            } else {
                var_dump("No manager");
            }
        }
        return $this->_parentRecord;
    }
    
    function setRecordDefaults(& $record) {
        if (($rec = & $this->getRecord()) && ($rel = & $this->getRelation())) {
            if (!$rel->midTableName) {
                foreach ($rel->fieldLinks as $srcField => $destField) {
                    $record->$destField = $rec->$srcField;
                }
            } else {
                // We would have some difficulties with mid-tables... 
            }
        }
    }
    
    function doAfterBindRecord(& $record, & $requestData) {
        $this->setRecordDefaults($record);
    }
    
    function getSqlCriteria() {
        $res = false;
        if (($rel = & $this->getRelation()) && ($rec = & $this->getRecord()) ) {
            $a = array(& $rec);
            $res = $rel->getCritForDestOfSrc($a, 't');
        } else {
        }
        return $res;
    }
   
    function getSqlExtraJoins() {
        $res = false;
        /*if ($this->_relation) {
            $res = $this->_relation->getDestJoin('t', 'sub');
        }*/
        return $res;
    }
    
   /**
    * @param Ae_Form $form
    */
   function onManagerFormCreated(& $form) {
        if (($rec = & $this->getRecord()) && ($rel = & $this->getRelation())) {
            if (!$rel->midTableName) {
                $f = Ae_Util::array_values($rel->fieldLinks);
                foreach ($form->listControls() as $c) {
                    if (in_array($c, $f)) {
                        $con = & $form->getControl($c);
                        $con->readOnly = true;
                    }
                }
            } else {
                // We would have some difficulties with mid-tables... 
            }
        }
   }
            
   /**
    * @param array $columns
    */
   function onManagerColumnsPreset(& $columns) {
        if (($rec = & $this->getRecord()) && ($rel = & $this->getRelation())) {
            if (!$rel->midTableName) {
                $f = Ae_Util::array_values($rel->fieldLinks);
                foreach ($f as $fn) {
                    if (isset($columns[$fn])) unset($columns[$fn]);
                }
            }
        }
   }
   
}

?>