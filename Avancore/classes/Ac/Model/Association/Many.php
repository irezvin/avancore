<?php

class Ac_Model_Association_Many extends Ac_Model_Association_Abstract {
    
    protected $isReferenced = false;   

    /**
     * @var string
     */
    protected $listDestObjectsMethod = false;

    /**
     * @var string
     */
    protected $countDestObjectsMethod = false;

    /**
     * @var string
     */
    protected $getDestObjectMethod = false;

    /**
     * @var string
     */
    protected $addDestObjectMethod = false;

    /**
     * @var string
     */
    protected $isDestLoadedMethod = false;

    /**
     * @var string
     */
    protected $loadedField = false;
    
    /**
     * @var string
     */
    protected $countField = false;

    /**
     * @param string $loadedField
     */
    function setLoadedField($loadedField) {
        if ($loadedField !== ($oldLoadedField = $this->loadedField)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->loadedField = $loadedField;
        }
    }

    /**
     * @return string
     */
    function getLoadedField() {
        if ($this->loadedField === false) {
            $rel = $this->getRelation(true);
            if ($rel) $this->loadedField = $rel->getSrcLoadedVarName();
            if (!$this->loadedField) $this->loadedField = null;
        }
        return $this->loadedField;
    }    

    /**
     * @param string $countField
     */
    function setCountField($countField) {
        if ($countField !== ($oldCountField = $this->countField)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->countField = $countField;
        }
    }

    /**
     * @return string
     */
    function getCountField() {
        if ($this->countField === false) {
            $rel = $this->getRelation(true);
            if ($rel) $this->countField = $rel->getSrcCountVarName();
            if (!$this->countField) $this->countField = null;
        }
        return $this->countField;
    }
    
    /**
     * @param string $listDestObjectsMethod
     */
    function setListDestObjectsMethod($listDestObjectsMethod) {
        if ($listDestObjectsMethod !== ($oldListDestObjectsMethod = $this->listDestObjectsMethod)) {
            $this->listDestObjectsMethod = $listDestObjectsMethod;
        }
    }

    /**
     * @return string
     */
    function getListDestObjectsMethod() {
        if ($this->listDestObjectsMethod === false) {
            $this->guessMethods();
        }
        return $this->listDestObjectsMethod;
    }

    /**
     * @param string $countDestObjectsMethod
     */
    function setCountDestObjectsMethod($countDestObjectsMethod) {
        if ($countDestObjectsMethod !== ($oldCountDestObjectsMethod = $this->countDestObjectsMethod)) {
            $this->countDestObjectsMethod = $countDestObjectsMethod;
        }
    }

    /**
     * @return string
     */
    function getCountDestObjectsMethod() {
        if ($this->countDestObjectsMethod === false) {
            $this->guessMethods();
        }
        return $this->countDestObjectsMethod;
    }

    /**
     * @param string $getDestObjectMethod
     */
    function setGetDestObjectMethod($getDestObjectMethod) {
        if ($getDestObjectMethod !== ($oldGetDestObjectMethod = $this->getDestObjectMethod)) {
            $this->getDestObjectMethod = $getDestObjectMethod;
        }
    }

    /**
     * @return string
     */
    function getGetDestObjectMethod() {
        if ($this->getDestObjectMethod === false) {
            $this->guessMethods();
        }
        return $this->getDestObjectMethod;
    }

    /**
     * @param string $addDestObjectMethod
     */
    function setAddDestObjectMethod($addDestObjectMethod) {
        if ($addDestObjectMethod !== ($oldAddDestObjectMethod = $this->addDestObjectMethod)) {
            $this->addDestObjectMethod = $addDestObjectMethod;
        }
    }

    /**
     * @return string
     */
    function getAddDestObjectMethod() {
        if ($this->addDestObjectMethod === false) {
            $this->guessMethods();
        }
        return $this->addDestObjectMethod;
    }

    /**
     * @param string $isDestLoadedMethod
     */
    function setIsDestLoadedMethod($isDestLoadedMethod) {
        if ($isDestLoadedMethod !== ($oldIsDestLoadedMethod = $this->isDestLoadedMethod)) {
            $this->isDestLoadedMethod = $isDestLoadedMethod;
        }
    }

    /**
     * @return string
     */
    function getIsDestLoadedMethod() {
        if ($this->isDestLoadedMethod === false) {
            $this->guessMethods();
        }
        return $this->isDestLoadedMethod;
    }    
    
    function listDestObjects($object) {
        if ($this->useMapperMethods && ($m = $this->listObjectsMethod)) {
            $res = $object->$m();
        } else {
            if ($this->canLoadDestObjects) {
                if (!$this->getIsDestLoaded($object)) $this->loadDestObjects($object);
                $f = $this->getInMemoryField();
                $res = array_keys($object->$f);
            } else {
                $f = $this->getInMemoryField();
                if (is_array($object->$f)) $res = array_keys($object->$f);
                    else $res = array();
            }
        }
        return $res;
    }
    
    function countDestObjects($object) {
        if ($this->useMapperMethods && ($m = $this->countDestObjectsMethod)) {
            $res = $object->$m();
        } else {
            $f = $this->getInMemoryField();
            if (($v = $object->$f) !== false) {
                $res = count($v);
            }
            else {
                if ($this->canLoadDestObjects) {
                    $c = $this->getCountField();
                    if (strlen($c)) {
                        if ($object->$c === false) {
                            $rel = $this->getRelation();
                            $rel->loadDestCount($record);
                        }
                        $res = $object->$c;
                    } else {
                        $res = count($this->listDestObjects($object));
                    }
                } else {
                    $c = $this->getCountField();
                    if (strlen($c) && $object->$c !== false) $res = $object->$c;
                        else $res = count($this->listDestObjects($object));
                }
            }
        }
        return $res;
    }
    
    function getIsDestLoaded($object) {
        if ($this->useMapperMethods && ($m = $this->isDestLoadedMethod)) {
            $res = $object->$m();
        } else {
            $l = $this->getLoadedField();
            if ($l) $res = (bool) $object->$l;
            else {
                $f = $this->getInMemoryField();
                $res = is_array($object->$f);
            }
        }
        return $res;
    }
    
    function getDestObject($object, $key) {
        if ($this->useMapperMethods && ($m = $this->getDestObjectMethod)) {
            $res = $object->$m($key);
        } else {
            if ($this->canLoadDestObjects && !$this->getIsDestLoaded($object)) 
                $this->loadDestObjects($object);
            $f = $this->getInMemoryField();
            if (!isset($object->{$f}[$key])) {
                throw Ac_E_InvalidCall::noSuchItem($this->getSingle(), $key);
            }
            $res = $object->{$f}[$key];
        }
        return $res;
    }
    
    function addDestObject($object, $destObject) {
        if ($this->useMapperMethods && ($m = $this->addDestObjectMethod)) {
            $res = $object->$m($destObject);
        } else {
            $ok = $this->checkDestType($destObject, $ex);
            if (!$ok) throw $ex;
            $this->listDestObjects($object);
            $f = $this->getInMemoryField();
            $object->{$f}[] = $destObject;
            $backField = $this->getBackReferenceField();
            if ($backField) $destObject->$backField = $this;
            
        }
        return $res;
    }
    
    protected function doAssignDestObject($object, $destObject) {
        $this->addDestObject($object, $destObject);
    }
    
    protected function getGuessMap() {
        return array_merge(parent::getGuessMap(), array(
            'listDestObjectsMethod' => 'list{Plural}',
            'countDestObjectsMethod' => 'count{Plural}',
            'getDestObjectMethod' => 'get{Single}',
            'addDestObjectMethod' => 'add{Single}',
            'isDestLoadedMethod' => 'is{Plural}Loaded',
        ));
    }
    
    protected function getMethodImplMap() {
        return array_merge(parent::getMethodImplMap(), array(
            'listDestObjectsMethod' => 'listDestObjects',
            'countDestObjectsMethod' => 'countDestObjects',
            'getDestObjectMethod' => 'getDestObject',
            'addDestObjectMethod' => 'addDestObject',
            'isDestLoadedMethod' => 'getIsDestLoaded',
        ));
    }
    
    protected function genPropMap() {
        parent::genPropMap();
        $c = $this->getCountField();
        $l = $this->getLoadedField();
        if (strlen($c)) $this->propMap['model'][$c] = "_assoc_{$this->id}_countField";
        if (strlen($l)) $this->propMap['model'][$l] = "_assoc_{$this->id}_loadedField";
    }
    
    protected function genModelMeta() {
        parent::genModelMeta();
        $c = $this->getCountField();
        $s = $this->getSingle();
        $this->modelMeta['onListLists'][$s] = $this->getPlural();
        if (strlen($c)) 
            $this->modelMeta['onGetPropertiesInfo'][$s]['countVarName'] = $c;
    }
    
    /**
     * @return string
     */
    function getSingle($dontThrow = false) {
        if ($this->single === false) {
            $this->single = Ac_Cg_Inflector::pluralToSingular($this->getPlural($dontThrow));
        }
        return $this->single;
    }

    /**
     * @return string
     */
    function getPlural($dontThrow = false) {
        if ($this->plural === false) {
            $this->plural = $this->getId();
            if (!strlen($this->plural) && !$dontThrow) {
                throw Ac_E_InvalidCall("Cannot ".__METHOD__." without setId()");
            }
        }
        return $this->plural;
    }
    
    function model_onListLists(& $meta) {
        Ac_Util::ms($meta, $this->modelMeta['onListLists']);
    }
    
//    function getObjectPropertyName() {
//        return $this->getPlural();
//    }
    
}