<?php

abstract class Ac_Model_Association_Abstract extends Ac_Prototyped implements Ac_I_ModelAssociation, Ac_I_Mixable_Shared {
    
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
    
    
    /**
     * whether Association allows to load destination object(s)
     * @var bool
     */
    protected $canLoadDestObjects = true;

    /**
     * whether Association allows to load source objects
     * @var bool
     */
    protected $canLoadSrcObjects = true;

    /**
     * whether Association allows to create destination objects
     * @var bool
     */
    protected $canCreateDestObject = true;
    
    protected $immutable = false;
    
    protected $methodsGuessed = false;
    
    // ---- mixable support ----
    
    protected $methodMap = false;
    
    protected $propMap = false;
    
    protected $modelMeta = false;
    
    protected $mapperMeta = false;
    
    protected $mixableInit = false;
    
    protected $mapperData = array();
        
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
    
    protected static function wmc($mixin) {
        return Ac_E_InvalidCall::wrongClass('mixin', $mixin, array('Ac_Model_Object', 'Ac_Model_Mapper'));
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

    /**
     * Sets whether Association allows to load destination object(s)
     * @param bool $canLoadDestObjects
     */
    function setCanLoadDestObjects($canLoadDestObjects) {
        if ($canLoadDestObjects !== ($oldCanLoadDestObjects = $this->canLoadDestObjects)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->canLoadDestObjects = $canLoadDestObjects;
        }
    }

    /**
     * Returns whether Association allows to load destination object(s)
     * @return bool
     */
    function getCanLoadDestObjects() {
        return $this->canLoadDestObjects;
    }

    /**
     * Sets whether Association allows to load source objects
     * @param bool $canLoadSrcObjects
     */
    function setCanLoadSrcObjects($canLoadSrcObjects) {
        if ($canLoadSrcObjects !== ($oldCanLoadSrcObjects = $this->canLoadSrcObjects)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->canLoadSrcObjects = $canLoadSrcObjects;
        }
    }

    /**
     * Returns whether Association allows to load source objects
     * @return bool
     */
    function getCanLoadSrcObjects() {
        return $this->canLoadSrcObjects;
    }

    /**
     * Sets whether Association allows to create destination objects
     * @param bool $canCreateDestObject
     */
    function setCanCreateDestObject($canCreateDestObject) {
        if ($canCreateDestObject !== ($oldCanCreateDestObject = $this->canCreateDestObject)) {
            if ($this->immutable) throw self::immutableException($this, __METHOD__);
            $this->canCreateDestObject = $canCreateDestObject;
        }
    }

    /**
     * Returns whether Association allows to create destination objects
     * @return bool
     */
    function getCanCreateDestObject() {
        return $this->canCreateDestObject;
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
        if ($this->canLoadSrcObjects) {
            if ($this->useMapperMethods && ($m = $this->getSrcObjectsMapperMethod)) {
                $res = $this->getMapper()->$m($destObjects);
            } else {
                $rel = $this->getRelation();
                $res = $rel->getSrc($destObjects);
            }
        } else {
            $res = array();
        }
        return $res;
    }

    function loadDestObjects($srcObjects) {
        if ($this->canLoadDestObjects) {
            if ($this->useMapperMethods && ($m = $this->loadSrcObjectsMapperMethod)) {
                $res = $this->getMapper()->$m($srcObjects);
            } else {
                $rel = $this->getRelation();
                $res = $rel->loadDest($srcObjects);
            }
        } else {
            $res = array();
        }
        return $res;
    }
    
    function loadSrcObjects($srcObjects) {
        if ($this->canLoadSrcObjects) {
            if ($this->useMapperMethods && ($m = $this->loadDestObjectsMapperMethod)) {
                $res = $this->getMapper()->$m($srcObjects);
            } else {
                $rel = $this->getRelation();
                $res = $rel->loadDest($srcObjects);
            }
        } else {
            $res = array();
        }
        return $res;
    }
    
    function createDestObject($object, $values = array(), $isReference = false) {
        if (!$this->canCreateDestObject) 
            throw new Ac_E_InvalidCall("Cannot createDestObject when \$canCreateDestObject === FALSE");
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
        $res = array(
            'loadDestObjectsMapperMethod' => 'load{Plural}For',
            'loadSrcObjectsMapperMethod' => 'loadFor{Plural}',
            'getSrcObjectsMapperMethod' => 'getOf{Plural}',
            'createDestObjectMethod' => 'create{Single}',
        );
        if (!$this->canLoadDestObjects) $res['loadDestObjectsMapperMethod'] = null;
        if (!$this->canLoadSrcObjects) $res['loadSrcObjectsMapperMethod'] = null;
        if (!$this->canCreateDestObject) $res['createDestObjectsMethod'] = null;
        return $res;
    }
    
    protected function getMethodImplMap() {
        return array(
            'getSrcObjectsMapperMethod' => 'getSrcObjects',
            'loadSrcObjectsMapperMethod' => 'loadDestObjects',
            'loadDestObjectsMapperMethod' => 'loadSrcObjects',
            'createDestObjectMethod' => 'createDestObject',
        );
    }
    
    protected function genMethodMap() {
        if (!$this->methodsGuessed) $this->guessMethods();
        $this->methodMap = array(
            'mapper' => array(),
            'model' => array(),
        );
        foreach (($imap = $this->getMethodImplMap()) as $myProp => $myImplMethod) {
            $methodName = strtolower($this->$myProp);
            if (strlen($methodName)) {
                if (strpos($myProp, 'Mapper') !== false) {
                    $this->methodMap['mapper'][$methodName] = $myImplMethod;
                } else {
                    $this->methodMap['model'][$methodName] = $myImplMethod;
                }
            }
        }
    }
    
    protected function genPropMap() {
        $this->propMap = array(
            'model' => array(
                $this->getInMemoryField() => "_assoc_{$this->id}_inMemoryField",
            ),
            'mapper' => array(
                
            ),
        );
    }
    
    function getObjectPropertyName() {
        return $this->getSingle();
    }
    
    protected function genModelMeta() {
        $dc = $this->getDestClass();
        $im = $this->getInMemoryField();
        $s = $this->getObjectPropertyName();
        $this->modelMeta = array(
            'onListAssociations' => array($s => $dc),
            'onListProperties' => array($s),
            'onGetPropertiesInfo' => array(
                $s => array(
                    'className' => $dc,
                    'mapperClass' => $this->getDestMapper()->getId(),
                    'relationId' => $im,
                    'referenceVarName' => $im,
                ),
            ),
        );
    }
    
    protected function guessMethods() {
        $this->methodsGuessed = true;
        $tr = array(
            '{single}' => Ac_Util::lcFirst($s = $this->getSingle()),
            '{Single}' => ucfirst($s),
            '{plural}' => Ac_Util::lcFirst($p = $this->getPlural()),
            '{Plural}' => ucfirst($p),
        );
        foreach ($this->getGuessMap() as $k => $v) {
            if ($this->$k === false) $this->$k = is_null($v)? null : strtr($v, $tr);
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
    
    function getMixableId() {
        return $this->id;
    }
    
    function listMixinProperties(Ac_I_Mixin $mixin) {
        if ($this->propMap === false) $this->genPropMap();
        if ($mixin instanceof Ac_Model_Object) {
            $res = array_keys($this->propMap['model']);
        } elseif ($mixin instanceof Ac_Model_Mapper) {
            $res = array_keys($this->propMap['mapper']);
        } else {
            $res = array();
        }
        return $res;
    }
    
    function listMixinMethods(Ac_I_Mixin $mixin) {
        if ($this->methodMap === false) $this->genMethodMap();
        if ($mixin instanceof Ac_Model_Object) {
            $res = array_keys($this->methodMap['model']);
        } elseif ($mixin instanceof Ac_Model_Mapper) {
            $res = array_keys($this->methodMap['mapper']);
        } else {
            $res = array();
        }
        return $res;
    }
    
    protected function initMixable() {
        if (!$this->methodMap) $this->genMethodMap();
        if (!$this->propMap) $this->genPropMap();
        if (!$this->modelMeta) $this->genModelMeta();
    }
    
    function registerMixin(Ac_I_Mixin $mixin) {
        if ($mixin instanceof Ac_Model_Object) {
            if (!$this->mixableInit) $this->initMixable();
            foreach ($this->propMap['model'] as $varName) $mixin->setExtraData(false, $varName);
        } elseif ($mixin instanceof Ac_Model_Mapper) {
            if (!$this->mapper) $this->setMapper ($mixin);
            elseif ($this->mapper !== $mixin) {
                throw new Ac_E_InvalidUsage("get_class($this): can mix with only one mapper (\$this->getMapper()) at a time");
            }
            
            // Add my relation
            $mixin->addRelation($this->getInMemoryField(), $this->getRelation());
            // Add my association
            $mixin->addAssociations(array($this));
            
            if (!$this->mixableInit) $this->initMixable();
            $this->mapperData = array();
            foreach ($this->propMap['mapper'] as $varName) {
                $this->mapperData[$varName] = false;
            }
        } else {
            throw self::wmc($mixin);            
        }
        $this->observeMixin($mixin);
    }
    
    function unregisterMixin(Ac_I_Mixin $mixin) {
        $this->unobserveMixin($mixin);
    }
    
    function callMixinMethod(Ac_I_Mixin $mixin, $method, array $arguments = array()) {
        $c = false;
        $method = strtolower($method);
        if ($mixin instanceof Ac_Model_Object) {
            $mm = $this->methodMap['model'];
            if (isset($mm[$method])) $c = array($this, $mm[$method]);
            array_unshift($arguments, $mixin);
        } elseif ($mixin instanceof Ac_Model_Mapper) {
            $mm = $this->methodMap['mapper'];
            if (isset($mm[$method])) $c = array($this, $mm[$method]);
        } else {
            throw self::wmc($mixin);
        }
        if ($c) $res = call_user_func_array($c, $arguments);
            else throw new Ac_E_InvalidCall("No implementation for ".get_class($mixin)."::{$method}() in ".get_class($this)."#'{$this->id}'");
        return $res;
    }
    
    function & getMixinProperty(Ac_I_Mixin $mixin, $property) {
        $res = null;
        if ($mixin instanceof Ac_Model_Object) {
            $mm = $this->propMap['model'];
            if (array_key_exists($property, $mm)) {
                $res = & $mixin->getExtraData($mm[$property]);
            } else {
                throw Ac_E_InvalidCall::noSuchProperty($mixin, $property);
            }
        } elseif ($mixin instanceof Ac_Model_Mapper) {
            $mm = $this->propMap['mapper'];
            if (array_key_exists($property, $mm)) $res = $this->mapperData[$mm];
                else throw Ac_E_InvalidCall::noSuchProperty($mixin, $property);
        } else {
            throw self::wmc($mixin);
        }
        return $res;
    }
    
    function setMixinProperty(Ac_I_Mixin $mixin, $property, $value) {
        if ($mixin instanceof Ac_Model_Object) {
            $mm = $this->propMap['model'];
            if (array_key_exists($property, $mm)) {
                $mixin->setExtraData($value, $mm[$property]);
            } else {
                throw Ac_E_InvalidCall::noSuchProperty($mixin, $property);
            }
        } elseif ($mixin instanceof Ac_Model_Mapper) {
            $mm = $this->propMap['mapper'];
            if (array_key_exists($property, $mm)) 
                $this->mapperData[$mm[$property]] = $value;
            else throw Ac_E_InvalidCall::noSuchProperty($mixin, $property);
        } else {
            throw self::wmc($mixin);
        }
    }
    
    function issetMixinProperty(Ac_I_Mixin $mixin, $property) {
        $res = false;
        if ($mixin instanceof Ac_Model_Object) {
            $mm = $this->propMap['model'];
            if (array_key_exists($property, $mm)) {
                $res = $mixin->getExtraData($mm[$property]) !== null;
            }
        } elseif ($mixin instanceof Ac_Model_Mapper) {
            $mm = $this->propMap['mapper'];
            if (array_key_exists($property, $mm)) {
                $res = isset($this->mapperData[$mm[$property]]);
            }
        } else {
            throw self::wmc($mixin);
        }
        return $res;
    }
    
    function unsetMixinProperty(Ac_I_Mixin $mixin, $property) {
        if ($mixin instanceof Ac_Model_Object) {
            $mm = $this->propMap['model'];
            if (array_key_exists($property, $mm)) {
                $mixin->setExtraData(null, $mm[$property]);
            }
        } elseif ($mixin instanceof Ac_Model_Mapper) {
            $mm = $this->propMap['mapper'];
            if (array_key_exists($property, $mm)) {
                $this->mapperData[$mm[$property]] = null;
            }
        } else {
            throw self::wmc($mixin);
        }
    }
    
    protected function observeMixin(Ac_I_Mixin $mixin) {
        if ($mixin instanceof Ac_Model_Object) {
            $prefix = 'model_';
        } elseif ($mixin instanceof Ac_Model_Mapper) {
            $prefix = 'mapper_';
        } else {
            throw self::wmc($mixin);
        }
        $handlers = Ac_Event::listEventHandlers(get_class($this), $prefix, true);
        foreach ($handlers as $event => $method) {
            $mixin->addEventListener(array($this, $method), $event);
        }
    }
    
    protected function unobserveMixin(Ac_I_Mixin $mixin) {
        $mixin->deleteEventListener($this);
    }
    
    function mapper_onAfterCreateRecord($record) {
        $record->addMixable($this, 'assoc_'.$this->id);
    }
    
    function model_onListAssociations(& $meta) {
        $meta = array_merge($meta, $this->modelMeta['onListAssociations']);
    }
    
    function model_onListProperties(& $meta) {
        $meta = array_merge($meta, $this->modelMeta['onListProperties']);
    }
    
    function model_onGetPropertiesInfo(& $meta) {
        Ac_Util::ms($meta, $this->modelMeta['onGetPropertiesInfo']);
    }
    
}