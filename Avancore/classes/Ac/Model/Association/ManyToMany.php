<?php

class Ac_Model_Association_ManyToMany extends Ac_Model_Association_ModelObject {
 
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

    function setIdsField($idsField) {
        if ($idsField !== ($oldIdsField = $this->idsField)) {
            if ($this->idsField !== false) throw Ac_E_InvalidCall::canRunMethodOnce($this, __METHOD__);
            $this->idsField = $idsField;
        }
    }

    function getIdsField() {
        if ($this->idsField === false) {
            if ($rel = $this->getRelation(false)) 
                $this->idsField = $rel->getSrcNNIdsVarName();
        }
        return $this->idsField;
    }
    
    function setFieldLinks2(array $fieldLinks2) {
        if ($fieldLinks2 !== ($oldFieldLinks2 = $this->fieldLinks2)) {
            if ($this->fieldLinks2 !== false) throw Ac_E_InvalidCall::canRunMethodOnce($this, __METHOD__);
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
            if ($this->midTableName !== false) throw Ac_E_InvalidCall::canRunMethodOnce($this, __METHOD__);
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
            if ($this->midWhere !== false) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __METHOD__);
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
        
        $l = $this->getIdsField();
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
    
}