<?php

class Ac_Model_Association_ManyToMany extends Ac_Model_Association_Many {
 
    /**
     * @var array
     */
    protected $fieldLinks2 = false;

    /**
     * @var string
     */
    protected $midTableName = false;

    /**
     * @var string
     */
    protected $midWhere = false;
    
    protected $idsField = false;
    
    /**
     * @var string
     */
    protected $loadDestIdsMapperMethod = false;
    
    /**
     * @var string
     */
    protected $getDestIdsMethod = false;

    /**
     * @var string
     */
    protected $setDestIdsMethod = false;

    /**
     * @var string
     */
    protected $clearDestObjectsMethod = false;
    
    function setIdsField($idsField) {
        if ($idsField !== ($oldIdsField = $this->idsField)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->idsField = $idsField;
        }
    }

    function getIdsField($dontThrow = false) {
        if ($this->idsField === false) {
            if ($rel = $this->getRelation($dontThrow)) 
                $this->idsField = $rel->getSrcNNIdsVarName();
            if (!$this->idsField && !$dontThrow) {
                throw new Ac_E_InvalidCall("Cannot ".__METHOD__." without proper getRelation()::srcNNIdsVarName()");
            }
        }
        return $this->idsField;
    }
    
    function setFieldLinks2(array $fieldLinks2) {
        if ($fieldLinks2 !== ($oldFieldLinks2 = $this->fieldLinks2)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->fieldLinks2 = $fieldLinks2;
        }
    }

    /**
     * @return array
     */
    function getFieldLinks2() {
        if ($this->fieldLinks2 === false) {
            if ($rel = $this->getRelation(true)) $this->fieldLinks2 = $rel->getFieldLinks2();
            else throw new Ac_E_InvalidUsage("setFieldLinks2() or setRelation(), otherwise ".get_class($this)." is unusable");
        }
        return $this->fieldLinks2;
    }

    /**
     * @param string $midTableName
     */
    function setMidTableName($midTableName) {
        if ($midTableName !== ($oldMidTableName = $this->midTableName)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->midTableName = $midTableName;
        }
    }

    /**
     * @return string
     */
    function getMidTableName() {
        if ($this->midTableName === false) {
            if ($rel = $this->getRelation(true)) $this->midTableName = $rel->getMidTableName();
            else throw new Ac_E_InvalidUsage("setMidTableName() or setRelation(), otherwise ".get_class($this)." is unusable");
        }
        return $this->midTableName;
    }

    /**
     * @param string $midWhere
     */
    function setMidWhere($midWhere) {
        if ($midWhere !== ($oldMidWhere = $this->midWhere)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->midWhere = $midWhere;
        }
    }

    /**
     * @return string
     */
    function getMidWhere() {
        if ($this->midWhere === false) {
            $rel = $this->getRelation(true);
            if ($rel) $this->midWhere = $rel->midWhere;
        }
        return $this->midWhere;
    }
    
    function beforeSave($object, &$errors) {
    }
    
    function afterSave($object, & $errors) {
        $res = null;
        
        if (!$object instanceof Ac_Model_Object) throw Ac_E_InvalidCall::wrongClass('object', $object, 'Ac_Model_Object');
        
        $f = $this->getInMemoryField();
        $val = false;
        if (strlen($f)) $val = $object->$f;
        
        $l = $this->getIdsField(true);
        $ids = false;
        if (strlen($l)) $ids = $object->$l;
        
        if (is_array($val) || is_array($ids)) {
            if (!$this->storeNN($object, $val, $ids, $errors)) $res = false;
        } else {
            
        }
    }
    
    protected function storeNN($object, $recordOrRecords, $ids, & $errors) {
        $fieldLinks = $this->getFieldLinks();
        $fieldLinks2 = $this->getFieldLinks2();
        $midTableName = $this->getMidTableName();
        $errorKey = $this->getErrorKey();
        $midWhere = $this->getMidWhere();
        $mapper = $this->getMapper();
        
        $res = true;
        if ($recordOrRecords !== false && !is_null($recordOrRecords)) {
            $ids = array();
            if (is_array($recordOrRecords)) $r = $recordOrRecords;
                else $r = array(& $recordOrRecords);
                
            foreach (array_keys($r) as $k) {
                $rec = $r[$k];
                if ((!$rec->isPersistent() || $rec->getChanges())) {
                    if (!$rec->store()) {
                        $errors[$errorKey][$k] = $rec->getErrors();
                        $res = false;
                    }
                }
                if (count($fieldLinks2) == 1) {
                    $ff = array_values($fieldLinks2);
                    $ids[] = $rec->{$ff[0]}; 
                } else {
                    $rc = array();
                    foreach ($fieldLinks2 as $s => $d) {
                        $rc[$s] = $rec->$d;
                    }
                    $ids[implode('-', $rc)] = $rc; // this will guarantee the uniqueness of multi-field values
                }
            }
        }
        if ($res && is_array($ids)) {
            if (count($fieldLinks2) == 1) {
                $ids = array_unique($ids); //TODO: check why sometimes we receive duplicate IDs...
            } else {
                $ids = array_values($ids);
            }
            $rows = array();
            $rowProto = array();
            if (is_array($midWhere)) $rowProto = $midWhere;
            foreach ($fieldLinks as $s => $d) $rowProto[$d] = $object->$s;
            $f = array_keys($fieldLinks2);
            if (count($f) == 1) {
                foreach ($ids as $id) {
                    $row = $rowProto;
                    $row[$f[0]] = $id;
                    $rows[] = $row;                 
                }
            } else {
                foreach ($ids as $id) {
                    $row = $rowProto;
                    $rows[] = array_merge($row, $id);                   
                }
            }
            $mapper->peReplaceNNRecords($object, $rowProto, $rows, $midTableName, $errors);
            if ($errors) {
                $errors[$errorKey] = $errors;
                return $res;
            }
        }
        return $res;
    }
    
    function checkIdsLoaded($object) {
        $ids = $this->getIdsField(true);
        if ($ids !== false) $res = $object->{$ids} !== false;
            else $res = null;
        return $res;
    }
 
    /**
     * @param string $loadDestIdsMapperMethod
     */
    function setLoadDestIdsMapperMethod($loadDestIdsMapperMethod) {
        if ($loadDestIdsMapperMethod !== ($oldloadDestIdsMapperMethod = $this->loadDestIdsMapperMethod)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->loadDestIdsMapperMethod = $loadDestIdsMapperMethod;
        }
    }

    /**
     * @param string $getDestIdsMethod
     */
    function setGetDestIdsMethod($getDestIdsMethod) {
        if ($getDestIdsMethod !== ($oldGetDestIdsMethod = $this->getDestIdsMethod)) {
            $this->getDestIdsMethod = $getDestIdsMethod;
        }
    }

    /**
     * @return string
     */
    function getGetDestIdsMethod() {
        if ($this->getDestIdsMethod === false) {
            $this->guessMethods();
        }
        return $this->getDestIdsMethod;
    }

    /**
     * @param string $setDestIdsMethod
     */
    function setSetDestIdsMethod($setDestIdsMethod) {
        if ($setDestIdsMethod !== ($oldSetDestIdsMethod = $this->setDestIdsMethod)) {
            $this->setDestIdsMethod = $setDestIdsMethod;
        }
    }

    /**
     * @return string
     */
    function getSetDestIdsMethod() {
        if ($this->setDestIdsMethod === false) {
            $this->guessMethods();
        }
        return $this->setDestIdsMethod;
    }

    /**
     * @param string $clearDestObjectsMethod
     */
    function setClearDestObjectsMethod($clearDestObjectsMethod) {
        if ($clearDestObjectsMethod !== ($oldClearDestObjectsMethod = $this->clearDestObjectsMethod)) {
            $this->clearDestObjectsMethod = $clearDestObjectsMethod;
        }
    }

    /**
     * @return string
     */
    function getClearDestObjectsMethod() {
        if ($this->clearDestObjectsMethod === false) {
            $this->guessMethods();
        }
        return $this->clearDestObjectsMethod;
    }    
    
    

    /**
     * @return string
     */
    function getloadDestIdsMapperMethod() {
        if ($this->loadDestIdsMapperMethod === false) {
            $this->guessMethods();
        }
        return $this->loadDestIdsMapperMethod;
    }
   
    function loadDestIds($srcObjects) {
        if ($this->useMapperMethods && ($m = $this->loadDestIdsMapperMethod)) {
            $res = $this->getMapper()->$m($srcObjects);
        } else {
            $rel = $this->getRelation();
            $res = $rel->loadDestNNIds($srcObjects); 
        }
        return $res;
    }
    
    function getDestIds($object) {
        if ($this->useModelMethods && ($m = $this->getDestIdsMethod)) {
            $res = $object->$m();
        } else {
            $if = $this->getIdsField();
            if ($object->$if === false) {
                $this->loadDestIds($object);
            }
            $res = $object->$if;
        }
        return $res;
    }
    
    function setDestIds($object, array $ids) {
        if ($this->useModelMethods && ($m = $this->setDestIdsMethod)) {
            $res = $object->$m($ids);
        } else {
            $if = $this->getIdsField();
            $l = $this->getLoadedField();
            $f = $this->getInMemoryField();
            if (strlen($l)) $object->$l = false;
            if (strlen($f)) $object->$f = false;
            $object->$if = $ids;
        }
        return $res;
    }
   
    function clearDestObjects($object) {
        if ($this->useModelMethods && ($m = $this->clearDestObjectsMethod)) {
            $res = $object->$m($ids);
        } else {
            $if = $this->getIdsField(true);
            $l = $this->getLoadedField();
            $f = $this->getInMemoryField();
            if (strlen($f)) $object->$f = array();
            if (strlen($if)) $object->$if = false;
            if (strlen($l)) $object->$l = true;
        }
        return $res;
    }
   
    protected function getGuessMap() {
        return array_merge(parent::getGuessMap(), array(
            'loadDestIdsMapperMethod' => 'load{Single}IdsFor',
            'getDestIdsMethod' => 'get{Single}Ids',
            'setDestIdsMethod' => 'set{Single}Ids',
            'clearDestObjectsMethod' => 'clear{Plural}',
        ));
    }
    
    protected function genPropMap() {
        parent::genPropMap();
        $ids = $this->getIdsField();
        if (strlen($ids)) $this->propMap['model'][$ids] = "_assoc_{$this->id}_ids";
    }
    
    protected function getMethodImplMap() {
        return array_merge(parent::getMethodImplMap(), array(
            'loadDestIdsMapperMethod' => 'loadDestIds',
            'getDestIdsMethod' => 'getDestIds',
            'setDestIdsMethod' => 'setDestIds',
            'clearDestObjectsMethod' => 'clearDestObjects',
        ));
    }
    
    protected function genModelMeta() {
        parent::genModelMeta();
        $s = $this->getSingle();
        $ids = $this->getIdsField(true);
        if (strlen($ids)) {
            $this->modelMeta['onListProperties'][] = 'tagIds';
            $this->modelMeta['onGetPropertiesInfo'][$s]['nnIdsVarName'] = $ids;
            $this->modelMeta['onGetPropertiesInfo'][$ids] = array(
                'arrayValue' => true,
            );
        }
    }
    
}
