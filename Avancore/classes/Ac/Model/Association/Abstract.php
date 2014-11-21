<?php

abstract class Ac_Model_Association_Abstract extends Ac_Prototyped implements Ac_I_ModelAssociation {
    
    protected $relationId = false;

    /**
     * @var Ac_Model_Relation
     */
    protected $relation = false;
    
    protected $inMemoryField = false;

    /**
     * @var string
     */
    protected $destInMemoryField = false;
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $mapper = false;
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $destMapper = false;
    
    /**
     * @var string
     */
    protected $destClass = false;
    
    /**
     * @var array
     */
    protected $fieldLinks = false;

    protected $id = false;
    
    protected $errorKey = false;

    /**
     * Whether target object is referenced by the source object and thus should be saved before it
     * @var bool
     */
    protected $isReferenced = false;

    /**
     * Use mapper methods for loading and retrieving src/dest objects
     * (is mainly used by generated code)
     * @var bool
     */
    protected $useMapperMethods = false;
    
    /**
     * Use model methods for retrieving/setting src/dest objects
     * @var bool
     */
    protected $useModelMethods = false;
    
    /**
     * @var string
     */
    protected $loadDestObjectsMapperMethod = false;

    /**
     * @var string
     */
    protected $loadSrcObjectsMapperMethod = false;

    /**
     * @var string
     */
    protected $getSrcObjectsMapperMethod = false;

    /**
     * @var string
     */
    protected $createDestObjectMethod = false;
    
    /**
     * @var string
     */
    protected $single = false;

    /**
     * @var string
     */
    protected $plural = false;
    
    protected $immutable = false;

    function __construct(array $prototype = array()) {
        $im = false;
        if (isset($prototype['immutable'])) {
            $im = true;
            $i = $prototype['immutable'];
            unset($prototype['immutable']);
        }
        parent::__construct($prototype);
        if ($im) $this->setImmutable($i);
    }
    
    protected static function immutableException($instance, $method) {
        return new Ac_E_InvalidUsage("Cannot {$method} on immutable Association");
    }
    
    function setId($id) {
        if ($id !== ($oldId = $this->id)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->id = $id;
        }
    }

    function getId() {
        return $this->id;
    }

    /**
     * @param bool $isReferenced
     */
    function setIsReferenced($isReferenced) {
        $this->isReferenced = $isReferenced;
    }

    /**
     * @return bool
     */
    function getIsReferenced() {
        return $this->isReferenced;
    }    

    function setFieldLinks(array $fieldLinks) {
        if ($fieldLinks !== ($oldFieldLinks = $this->fieldLinks)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->fieldLinks = $fieldLinks;
        }
    }

    /**
     * @return array
     */
    function getFieldLinks($dontThrow = false) {
        if ($this->fieldLinks === false) {
            $relation = $this->getRelation($dontThrow);
            $this->fieldLinks = $relation->getFieldLinks();
        }
        return $this->fieldLinks;
    }    

    function setErrorKey($errorKey) {
        $this->errorKey = $errorKey;
    }

    function getErrorKey() {
        if ($this->errorKey === false) {
            $this->errorKey = $this->id;
            if (!strlen($this->errorKey)) $this->errorKey = $this->getInMemoryField();
        }
        return $this->errorKey;
    }    
    
    function setMapper(Ac_Model_Mapper $mapper) {
        if ($mapper !== ($oldMapper = $this->mapper)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->mapper = $mapper;
        }
    }

    /**
     * @return Ac_Model_Mapper
     */
    function getMapper($dontThrow = false) {
        if ($this->mapper === false && $this->relation)
            $this->mapper = $this->relation->getSrcMapper();
        
        if (!$this->mapper && !$dontThrow) 
            throw new Ac_E_InvalidUsage(__CLASS__." cannot do anything without prior setMapper()");
        
        return $this->mapper;
    }    
    
    function setDestMapper(Ac_Model_Mapper $destMapper) {
        if ($destMapper !== ($oldDestMapper = $this->destMapper)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->destMapper = $destMapper;
        }
    }

    /**
     * @return Ac_Model_Mapper
     */
    function getDestMapper($dontThrow = false) {
        if ($this->destMapper === false && $this->relation)
            $this->destMapper = $this->relation->getDestMapper();
        
        if (!$this->destMapper && !$dontThrow) 
            throw new Ac_E_InvalidUsage(__CLASS__." cannot proceed without prior setDestMapper()");
        
        return $this->destMapper;
    }    

    /**
     * @param string $destClass
     */
    function setDestClass($destClass) {
        if ($destClass !== ($oldDestClass = $this->destClass)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->destClass = $destClass;
        }
    }

    /**
     * @return string
     */
    function getDestClass() {
        if ($this->destClass === false) {
            $this->destClass = $this->getDestMapper()->recordClass;
        }
        return $this->destClass;
    }    
    
    function setInMemoryField($inMemoryField) {
        if ($inMemoryField !== ($oldInMemoryField = $this->inMemoryField)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->inMemoryField = $inMemoryField;
        }
    }

    function getInMemoryField() {
        if ($this->inMemoryField === false) {
            $rel = $this->getRelation(true);
            if ($rel) $this->inMemoryField = $rel->getSrcVarName();
                else throw Ac_E_InvalidUsage("setInMemoryField() or setRelation(), otherwise ".get_class($this)." is unusable");
        }
        return $this->inMemoryField;
    }    
    
    /**
     * @param string $destInMemoryField
     */
    function setDestInMemoryField($destInMemoryField) {
        if ($destInMemoryField !== ($oldDestInMemoryField = $this->destInMemoryField)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->destInMemoryField = $destInMemoryField;
        }
    }

    /**
     * @return string
     */
    function getDestInMemoryField() {
        if ($this->destInMemoryField === false) {
            $rel = $this->getRelation(true);
            if ($rel) $this->destInMemoryField = $rel->getDestVarName();
            if (!$this->destInMemoryField) $this->destInMemoryField = null;
        }
        return $this->destInMemoryField;
    }    
    
    /**
     * @var string
     */
    protected $backReferenceField = false;

    /**
     * @param string $backReferenceField
     */
    function setBackReferenceField($backReferenceField) {
        if ($backReferenceField !== ($oldBackReferenceField = $this->backReferenceField)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->backReferenceField = $backReferenceField;
        }
    }

    /**
     * @return string
     */
    function getBackReferenceField() {
        if ($this->backReferenceField === false) {
            $rel = $this->getRelation(true);
            if ($rel) $this->backReferenceField = $rel->getDestVarName();
                else $this->backReferenceField = null;
        }
        return $this->backReferenceField;
    }    
    
    function checkAssociationLoaded($object) {
        $res = $object->{$this->inMemoryField} !== false;
        return $res;
    }

    function setRelationId($relationId) {
        if ($relationId !== ($oldRelationId = $this->relationId)) {
            if ($this->relation) throw new Ac_E_InvalidCall("Cannot ".__METHOD__." after setRelation()");
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->relationId = $relationId;
        }
    }

    function getRelationId() {
        return $this->relationId;
    }
    
    function setRelation(Ac_Model_Relation $relation) {
        if ($relation !== ($oldRelation = $this->relation)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            if ($this->relationId !== false) throw new Ac_E_InvalidCall("Cannot ".__METHOD__." after setRelationId()");
            $this->relation = $relation;
        }
    }

    /**
     * @return Ac_Model_Relation
     */
    function getRelation($dontThrow = false) {
        if ($this->relation === false) {
            if ($this->relationId) {
                if ($mapper = $this->getMapper()) {
                    $this->relation = $mapper->getRelation($this->relationId);
                } elseif (!$dontThrow) {
                    throw new Ac_E_InvalidCall("Cannot ".__METHOD__." without prior ::setMapper()");
                }
            } elseif (!$dontThrow) {
                throw new Ac_E_InvalidCall("Cannot ".__METHOD__." without prior ::setRelationId()");
            }
        }
        return $this->relation;
    }
    
    protected function storeReferencing(Ac_Model_Object $object, $recordOrRecords, & $errors) {
        $res = true;
        
        $errorKey = $this->getErrorKey();
        $fieldLinks = $this->getFieldLinks();

        if (is_array($recordOrRecords)) $r = $recordOrRecords;
            else $r = array($recordOrRecords);
            
        foreach (array_keys($r) as $k) {
            $rec = $r[$k];
            foreach ($fieldLinks as $sf => $df) $rec->$df = $object->$sf;
            if ($rec->getChanges() && !$rec->_isDeleted) {
                if (!$rec->store()) {
                    $errors[$errorKey][$k] = $rec->getErrors();
                    $res = false;
                }
            }
        }
        
        return $res;
    }
    
    protected function storeReferenced(Ac_Model_Object $object, $recordOrRecords, & $errors) {
        $res = true;
        
        $errorKey = $this->getErrorKey();
        $fieldLinks = $this->getFieldLinks();
        
        if (is_array($recordOrRecords)) $r = $recordOrRecords;
            else $r = array($recordOrRecords);
            
        foreach (array_keys($r) as $k) {
            $rec = $r[$k];
            if ((!$rec->isPersistent() || $rec->getChanges())) {
                if (!$rec->store()) {
                    $errors[$errorKey][$k] = $rec->getErrors();
                    $res = false;
                }
            }
            foreach ($fieldLinks as $sf => $df) $object->$sf = $rec->$df; 
        }
        
        return $res;
    }
     
    /**
     * @param Ac_Model_Object $object
     */
    function beforeSave($object, & $errors) {
        $res = null;
        
        if ($this->isReferenced) {
            
            if (!$object instanceof Ac_Model_Object) throw Ac_E_InvalidCall::wrongClass('object', $object, 'Ac_Model_Object');

            $f = $this->getInMemoryField();
            if (($val = $object->$f)) {
                if (!$this->storeReferenced($object, $val, $errors)) $res = false;
            }
            
        }
        
        return $res;        
    }
   
    /**
     * @param Ac_Model_Object $object
     */
    function afterSave($object, & $errors) {
        $res = null;
        
        if (!$this->isReferenced) {
        
            if (!$object instanceof Ac_Model_Object) throw Ac_E_InvalidCall::wrongClass('object', $object, 'Ac_Model_Object');

            $f = $this->getInMemoryField();
            if (($val = $object->$f)) {
                if (!$this->storeReferencing($object, $val, $errors)) $res = false;
            }
            
        }
        
        return $res;        
    }

    /**
     * @param string $loadDestObjectsMapperMethod
     */
    function setLoadDestObjectsMapperMethod($loadDestObjectsMapperMethod) {
        if ($loadDestObjectsMapperMethod !== ($oldLoadDestObjectsMapperMethod = $this->loadDestObjectsMapperMethod)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->loadDestObjectsMapperMethod = $loadDestObjectsMapperMethod;
        }
    }

    /**
     * @return string
     */
    function getLoadDestObjectsMapperMethod() {
        if ($this->loadDestObjectsMapperMethod === false) {
            $this->guessMethods();
        }
        return $this->loadDestObjectsMapperMethod;
    }

    /**
     * @param string $loadSrcObjectsMapperMethod
     */
    function setLoadSrcObjectsMapperMethod($loadSrcObjectsMapperMethod) {
        if ($loadSrcObjectsMapperMethod !== ($oldLoadSrcObjectsMapperMethod = $this->loadSrcObjectsMapperMethod)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->loadSrcObjectsMapperMethod = $loadSrcObjectsMapperMethod;
        }
    }

    /**
     * @return string
     */
    function getLoadSrcObjectsMapperMethod() {
        if ($this->loadSrcObjectsMapperMethod === false) {
            $this->guessMethods();           
        }
        return $this->loadSrcObjectsMapperMethod;
    }

    /**
     * @param string $getSrcObjectsMapperMethod
     */
    function setGetSrcObjectsMapperMethod($getSrcObjectsMapperMethod) {
        if ($getSrcObjectsMapperMethod !== ($oldGetSrcObjectsMapperMethod = $this->getSrcObjectsMapperMethod)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->getSrcObjectsMapperMethod = $getSrcObjectsMapperMethod;
        }
    }

    /**
     * @return string
     */
    function getGetSrcObjectsMapperMethod() {
        if ($this->getSrcObjectsMapperMethod === false) {
            $this->guessMethods();
        }
        return $this->getSrcObjectsMapperMethod;
    }

    /**
     * @param string $createDestObjectMethod
     */
    function setCreateDestObjectMethod($createDestObjectMethod) {
        if ($createDestObjectMethod !== ($oldCreateDestObjectMethod = $this->createDestObjectMethod)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->createDestObjectMethod = $createDestObjectMethod;
        }
    }

    /**
     * @return string
     */
    function getCreateDestObjectMethod() {
        if ($this->createDestObjectMethod === false) {
            $this->guessMethods();
        }
        return $this->createDestObjectMethod;
    }

    /**
     * @param bool $useMapperMethods
     */
    function setUseMapperMethods($useMapperMethods) {
        if ($useMapperMethods !== ($oldUseMapperMethods = $this->useMapperMethods)) {
            $this->useMapperMethods = $useMapperMethods;
        }
    }

    /**
     * @return bool
     */
    function getUseMapperMethods() {
        return $this->useMapperMethods;
    }    

    /**
     * @param bool $useModelMethods
     */
    function setUseModelMethods($useModelMethods) {
        if ($useModelMethods !== ($oldUseModelMethods = $this->useModelMethods)) {
            $this->useModelMethods = $useModelMethods;
        }
    }

    /**
     * @return bool
     */
    function getUseModelMethods() {
        return $this->useModelMethods;
    }    
    
    function getSrcObjects($destObjects) {
        if ($this->useMapperMethods && ($m = $this->getSrcObjectsMapperMethod)) {
            $res = $this->getMapper()->$m($destObjects);
        } else {
            $rel = $this->getRelation();
            $res = $rel->getSrc($destObjects);
        }
        return $res;
    }

    function loadDestObjects($destObjects) {
        if ($this->useMapperMethods && ($m = $this->loadSrcObjectsMapperMethod)) {
            $res = $this->getMapper()->$m($srcObjects);
        } else {
            $rel = $this->getRelation();
            $res = $rel->loadSrc($srcObjects);
        }
        return $res;
    }
    
    function loadSrcObjects($srcObjects) {
        if ($this->useMapperMethods && ($m = $this->loadDestObjectsMapperMethod)) {
            $res = $this->getMapper()->$m($srcObjects);
        } else {
            $rel = $this->getRelation();
            $res = $rel->loadDest($srcObjects);
        }
        return $res;
    }
    
    function createDestObject($object, $values = array(), $isReference = false) {
        if ($this->useModelMethods && ($m = $this->createDestObjectMethod)) {
            $res = $object->$m($values, $isReference);
        } else {
            $m = $object->getDestMapper();
            $res = $m->createRecord();
            if ($values) $res->bind($values);
            if ($isReference) $res->_setIsReference(true);
            $this->doAssignDestObject($object, $res);
        }
        return $res;
    }
    
    abstract protected function doAssignDestObject($object, $destObject); 

    function checkDestType($destObject, & $ex = null) {
        $ex = null;
        $ok = false;
        $dc = $this->getDestClass();
        if (is_object($destObject) && $destObject instanceof $dc) {
            $ok = true;
        } elseif ($destObject === false || $destObject === null) $ok = true;
        if (!$ok) $ex = Ac_E_InvalidCall::wrongType ('destObject', $destObject, array('null', 'false', $dc));
        return $ex;
    }

    /**
     * @param string $single
     */
    function setSingle($single) {
        if ($single !== ($oldSingle = $this->single)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->single = $single;
        }
    }

    /**
     * @return string
     */
    function getSingle($dontThrow = false) {
        if ($this->single === false) {
            if ($this->plural) $this->single = Ac_Cg_Inflector::pluralToSingular($this->plural);
            else {
                $this->single = $this->getId();
            }
            if (!strlen($this->single) && !$dontThrow) {
                throw Ac_E_InvalidCall("Cannot ".__METHOD__." without setId()");
            }
        }
        return $this->single;
    }

    /**
     * @param string $plural
     */
    function setPlural($plural) {
        if ($plural !== ($oldPlural = $this->plural)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->plural = $plural;
        }
    }

    /**
     * @return string
     */
    function getPlural() {
        if ($this->plural === false) {
            $this->plural = Ac_Cg_Inflector::singularToPlural($this->getSingle());
        }
        return $this->plural;
    }
    
    /**
     * @return array (methodPropertyName => methodTemplate)
     * 
     * methodTemplate may contain placeholder like '{single}', '{plural}', '{Single}', '{Plural}'
     */
    protected function getGuessMap() {
        return array(
            'loadDestObjectsMapperMethod' => 'load{Plural}For',
            'loadSrcObjectsMapperMethod' => 'loadFor{Plural}',
            'getSrcObjectsMapperMethod' => 'getOf{Plural}',
            'createDestObjectMethod' => 'create{Single}',
        );
    }
    
    protected function guessMethods() {
        $tr = array(
            '{single}' => Ac_Util::lcFirst($s = $this->getSingle()),
            '{Single}' => ucfirst($s),
            '{plural}' => Ac_Util::lcFirst($p = $this->getPlural()),
            '{Plural}' => ucfirst($p),
        );
        foreach ($this->getGuessMap() as $k => $v) {
            if ($this->$k === false) $this->$k = strtr($v, $tr);
        }
    }
    
    function getMethodNames() {
        $res = Ac_Accessor::getObjectProperty($this, array_keys($this->getGuessMap()));
        return $res;
    }
    
    function setImmutable($immutable) {
        if ($this->immutable && !$immutable) 
            throw new Ac_E_InvalidCall("Cannot remove immutable status from an immutable Association");
        $this->immutable = (bool) $immutable;
    }
    
    function getImmutable() {
        return $this->immutable;
    }
    
    function __clone() {
        $this->immutable = false;
    }

}