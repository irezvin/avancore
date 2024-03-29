<?php

/**
 * @property Ac_Sql_Db $db Access to Db instance
 */
abstract class Ac_Model_Object extends Ac_Model_Data implements Ac_I_CollectionAwareObject {

    use Ac_Sql_WithDbFn;
    
    const ACTUAL_REASON_LOAD = 1;
    
    const ACTUAL_REASON_SAVE = 2;
    
    const OPERATION_NONE = 0;
    
    const OPERATION_LOAD = 1;
    
    const OPERATION_CREATE = 2;

    const OPERATION_UPDATE = 6;
    
    const OPERATION_DELETE = 8;
    
    /**
     * function onCreate()
     */
    const EVENT_ON_CREATE = 'onCreate';
    
    /**
     * function onListDataProperties(array & $dataProperties)
     */
    const EVENT_ON_LIST_DATA_PROPERTIES = 'onListDataProperties';

    /**
     * function onSetDefaults(array & $defaults, $full)
     */
    const EVENT_ON_SET_DEFAULTS = 'onSetDefaults';

    /**
     * function onAfterLoad()
     */
    const EVENT_AFTER_LOAD = 'onAfterLoad';
    
    /**
     * function onActual($reason = self::ACTUAL_REASON_LOAD)
     */
    const EVENT_ON_ACTUAL = 'onActual';

    /**
     * function onBeforeBeforeSave(& $result)
     */
    const EVENT_BEFORE_BEFORE_SAVE = 'onBeforeBeforeSave';

    /**
     * function onBeforeSave(& $result)
     */
    const EVENT_BEFORE_SAVE = 'onBeforeSave';

    /**
     * function onAfterSave(& $result)
     */
    const EVENT_AFTER_SAVE = 'onAfterSave';

    /**
     * function onSaveFailed()
     */
    const EVENT_ON_SAVE_FAILED = 'onSaveFailed';

    /**
     * function onCanDelete(& $result)
     */
    const EVENT_ON_CAN_DELETE = 'onCanDelete';

    /**
     * function onBeforeDelete(& $result)
     */
    const EVENT_BEFORE_DELETE = 'onBeforeDelete';

    /**
     * function onAfterDelete()
     */
    const EVENT_AFTER_DELETE = 'onAfterDelete';

    /**
     * function onDeleteFailed()
     */
    const EVENT_ON_DELETE_FAILED = 'onDeleteFailed';

    /**
     * function onCopy(Ac_Model_Object $copy)
     */
    const EVENT_ON_COPY = 'onCopy';

    /**
     * function onListNonCopiedFields(array & $fields)
     */
    const EVENT_ON_LIST_NON_COPIED_FIELDS = 'onListNonCopiedFields';

    /**
     * function onCompare(Ac_Model_Object $other, & $compareResult)
     */
    const EVENT_ON_COMPARE = 'onCompare';

    /**
     * function onListNonComparedFields(array & $fields)
     */
    const EVENT_ON_LIST_NON_COMPARED_FIELDS = 'onListNonComparedFields';

    /**
     * function onListDefaultComparedAssociations(array & $associations)
     */
    const EVENT_ON_LIST_DEFAULT_COMPARED_ASSOCIATIONS = 'onListDefaultComparedAssociations';
    
    /**
     * function onGetAssociations(array & $associations)
     */
    const EVENT_ON_GET_ASSOCIATIONS = 'onGetAssociations';
    
    /**
     * function onCleanup()
     */
    const EVENT_ON_CLEANUP = 'onCleanup';
    
    /**
     * In-memory id
     * @var int
     */
    protected $_instanceId = 0;
    
    var $_error = false;
    
    var $_oldValues = false;
    
    var $_otherValues = array();
    
    var $_mapperClass = false;
    
    var $_isImport = false;
    
    var $_isBeingStored = false;
    
    var $_isBeingCompared = false;
    
    var $_isDeleted = false;
    
    var $_hasDefaults = false;

    /**
     * Identifier under which the object is persisted in the storage
     * Set public for quicker access
     * @var mixed
     */
    var $_peIdentifier = null;
    
    protected $lastOperation = self::OPERATION_NONE;
    
    protected $associations = false;
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $mapper = false;
    
    /**
     * @var Ac_I_ObjectsCollection[]
     */
    protected $registeredCollections = array();
    
    protected $_magicGetLock = false;
    
    protected $_magicSetLock = false;
    
    /**
     * @deprecated since 0.4.2
     * @return Ac_Application
     */
    function getApplication() {
        return $this->getApp();
    }
    
    /**
     * @return Ac_Application
     */
    function getApp() {
        return $this->mapper->getApp();
    }
    
    /**
     * @return Ae_Sql_Db
     * @deprecated 
     */
    function getSqlDb() {
        return $this->getDb();
    }
    
    function getDb() {
        return $this->mapper->getDb();
    }
    
    // -------- template and to-be-overridden methods --------
    
    function doOnCreate() {
    }
    
    protected function listOwnProperties() {
        return $this->listOwnDataProperties();
    }
    
    protected function listOwnDataProperties() {
        return $this->mapper->listDataProperties();
    }
    
    function doAfterLoad() {
    }
    
    function doOnActual($reason = Ac_Model_Object::ACTUAL_REASON_LOAD) {
    }
    
    function doBeforeSave() {
    }
    
    function doAfterSave() {
    }
    
    function doOnSaveFailed() {
    }
    
    function doOnCanDelete() {
    }
    
    function doBeforeDelete() {
    }
    
    function doAfterDelete() {
    }
    
    function doOnDeleteFailed() {
    }
    
    /**
     * @param Ac_Model_Object $copy
     */
    function doOnCopy($copy) {
    }
    
    protected function doOnGetAssociations(array & $associations) {
    }
    
    function doListNonCopiedFields() {
        $m = $this->mapper;
        $res = $m->listGeneratedFields();
        return $res;
    }
    
    /**
     * Template method that should do extra comparison (especially with associations) with other object.
     * This method will be called only when standard implementation (Ac_Model_Object::equals) already had successfully compared 
     * this object's fields to other one's.
     *
     * @param Ac_Model_Object $otherObject
     * @return mixed|bool The method should return FALSE if comparison fails (otherwise objec will be considered matching) 
     */
    function doOnExtraCompare ($otherObject) {
    }
    
    function doListNonComparedFields() {
        $m = $this->mapper;
        $res = $m->listGeneratedFields();
        return $res;
    }
    
    /**
     * Should return list of associations (names of properties) that should be compared by default, in order of comparison. 
     * The list can be recursive, i.e. ('assoc1', 'assoc2', 'assoc3' => array('assoc3.1', 'assoc3.2', ...))
     * 
     * @return array
     */
    function doListDefaultComparedAssociations() {
        return array();
    }
    
    protected function doOnCleanup() {
    }
    
    final function canDelete() {
        $result = $this->doOnCanDelete();
        $this->triggerEvent(self::EVENT_ON_CAN_DELETE, array (& $result));
        $res  = $result !== false;
        return $res;
    }
    
    function _describeIndex($indexName, $indexFields = false) {
        if ($indexFields === false) {
            $m = $this->mapper;
            $indexFields = $m->listUniqueIndexFields($indexName);
        }
        $r = array();
        foreach ($indexFields as $f) {
            if (!$this->hasProperty($f)) continue;
            $p = $this->getPropertyInfo($f, true);
            $r[] = "'".($p->caption? $p->caption : $f)."'";
        }
        return implode(", ", $r);
    }
    
    function _checkOwnFields() {
        parent::_checkOwnFields();
        $dbp = $this->checkPresence(true);
        if ($dbp) {
            $m = $this->mapper;
            foreach ($dbp as $indexName => $identifiers) {
                $ff = $m->listUniqueIndexFields($indexName);
                $fn = current($ff);
                $px = count($ff) > 1? AC_SUCH_VALUES_OF_FIELD_MULTIPLE : AC_SUCH_VALUES_OF_FIELD_SINGLE;
                $this->_errors[$fn]['index'][$indexName] = sprintf(AC_RECORD_BY_INDEX_ALREADY_EXISTS, $px, $this->_describeIndex($indexName, $ff));
            }
        }
    }
    
    // Models that have same properties use common validator (stored in the mapper). This preserves memory and time required for initial metadata retrieval. 
    function _createValidator() {
        if ($this->hasUniformPropertiesInfo()) {
            $m = $this->mapper;
            $res = $m->getCommonValidator();
            $res->model = $this;
        } else {
            $res = new Ac_Model_Validator($this);
        }
        return $res;
    }
    
    function _memorizeFields() {
        $this->_oldValues = $this->getDataFields();
    }
    
    function getModelObjectInstanceId() {
        return $this->_instanceId;
    }
    
    function __construct($mapperOrMapperClass = null) {
        
        if (is_array($mapperOrMapperClass)) {
            $prototype = $mapperOrMapperClass;
            if (isset($prototype['mapper'])) $mapperOrMapperClass = $prototype['mapper'];
            elseif (isset($prototype['mapperClass'])) $mapperOrMapperClass = $prototype['mapperClass'];
            else $mapperOrMapperClass = null;
        } else {
            $prototype = array();
        }
        
        $this->_instanceId = uniqid();

        if (is_null($mapperOrMapperClass) && strlen($this->_mapperClass)) $mapperOrMapperClass = $this->_mapperClass;
        
        if ($mapperOrMapperClass instanceof Ac_Model_Mapper) {
            $mapper = $mapperOrMapperClass;
        } else {
            $mapper = Ac_Model_Mapper::getMapper($mapperOrMapperClass);
        }
        
        if (!$mapper) throw new Exception("Cannot determine \$mapper for ".get_class($this));
        
        if (!strlen($this->_mapperClass)) $this->_mapperClass = $mapper->getId();
        
        $this->mapper = $mapper;
     
        parent::__construct($prototype);
        
        $this->notifyObjectCollections(Ac_I_LifecycleAwareCollection::STAGE_CREATED);
        $this->mapper->registerRecord($this);
        
        if (!$this->_hasDefaults) {
            foreach ($mapper->getDefaults() as $k => $v) $this->$k = $v;
        }
        
        $this->_memorizeFields();
        
        $this->setDefaults();
        $this->doOnCreate();
        $this->triggerEvent(self::EVENT_ON_CREATE);
        
    }
    
    function hasPublicVars() {
        return true;
    }
    
    protected function intAssignMetaCaching() {
        $this->metaClassId = $this->mapper->getId();
        parent::intAssignMetaCaching();
    }
    
    /**
     * @param mixed $pkOrRow null, primary key or associative array with row 
     * @param bool $isRow Whether to treat first parameter as an associative row
     */
    function load ($pkOrRow = null, $isRow = false) {
    
        if ($isRow) {
            
            $row = $pkOrRow;
            $this->_otherValues = array();
            $hyData = $this->mapper->peConvertForLoad($this, $row);
            
            if (isset($hyData['_peIdentifier'])) $this->_peIdentifier = $hyData['_peIdentifier'];
                else $this->_peIdentifier = false;

            foreach ($this->listDataProperties() as $propName) {
                if (array_key_exists($propName, $hyData)) {
                    $this->$propName = $hyData[$propName];
                    unset($hyData[$propName]);
                }
            }
            // now go for the mixables with prefixed values
            if ($this->mixables) {
                foreach ($hyData as $propName => $value) {
                    $path = explode('::', $propName, 2);
                    if (isset($path[1]) && isset($this->mixables[$path[0]])) {
                        $this->$propName = $value;
                        unset($hyData[$propName]);
                    }
                }
            }
            $this->_otherValues = $hyData;
            
            //$this->_peIdentifier = $this->mapper->getIdentifier($this);
            $this->doAfterLoad();
            $this->triggerEvent(self::EVENT_AFTER_LOAD);
            $this->doOnActual(self::ACTUAL_REASON_LOAD);
            $this->notifyObjectCollections(Ac_I_LifecycleAwareCollection::STAGE_LOADED);
            $this->_memorizeFields();
            $this->lastOperation = self::OPERATION_LOAD;
            $res = true;
            
        } else {
        
            $primaryKey = $pkOrRow;
    
            $res = false;

            if ($primaryKey !== null || $this->isPersistent() || !is_null($primaryKey = $this->mapper->calcIdentifier($this))) {
                if ($primaryKey === null) $primaryKey = $this->mapper->getIdentifier($this);
                if ($hyData = $this->mapper->peLoad($this, $primaryKey)) {
                    $this->setDefaults(true);
                    $res = $this->load($hyData, true);
                }
            }
        
        }
        
        return $res;
    }
        
    protected function notifyObjectCollections($lifecycleStage) {
        $hadMapper = false;
        foreach ($this->registeredCollections as $c) {
            if ($c === $this->mapper) $hadMapper = true;
            if ($c instanceof Ac_I_LifecycleAwareCollection) 
                $c->notifyCollectionObjectStage($this, Ac_I_LifecycleAwareCollection::STAGE_LOADED);
            else
                $c->actualizeRegisteredObject($this);
        }
        if (!$hadMapper) {
            $this->mapper->notifyCollectionObjectStage(
                $this, Ac_I_LifecycleAwareCollection::STAGE_LOADED
            );
        }
    }
    
    protected function getHyData() {
        $res = array();
        foreach ($this->listDataProperties() as $propName) {
            $res[$propName] = $this->$propName;
        }
        if ($this->_peIdentifier !== false) $res['_peIdentifier'] = $this->_peIdentifier;
            else $res['_peIdentifier'] = $this->getIdentifier();
        return $res;
    }
    
    // formerly _legacyStore
    protected function saveOwnRow() {
        $hyData = $this->getHyData();
        $hyData = $this->mapper->peConvertForSave($this, $hyData);
        $res = (bool) $this->mapper->peSave($this, $hyData, $exists, $error, $newData);
            if (is_array($newData)) foreach ($newData as $k => $v) $this->$k = $v;
        if (!$res) {
            if ($error !== false) {
                $this->_errors['_store']['db'] = $error;
                $this->_checked = true; // otherwise next getErrors() will trigger check() which will clean this error message
            }
        } else {
            $this->_memorizeFields();
        }
        $this->notifyObjectCollections(Ac_I_LifecycleAwareCollection::STAGE_SAVED);
        return $res;
    }
    
    function forget() {
        foreach ($this->registeredCollections as $c) $c->unregisterObject($this);
    }
    
    function save() {
        if (!$this->_isBeingStored) { // we have to prevent recursion while saving complex in-memory record graphs
            $this->_isBeingStored = true;
            $this->intResetReferences();
            
            $this->triggerEvent(self::EVENT_BEFORE_BEFORE_SAVE);
            
            $beforeSaveResult = $this->doBeforeSave();
            $isNew = !$this->isPersistent();
            
            $this->triggerEvent(self::EVENT_BEFORE_SAVE, array(& $beforeSaveResult));
            if ($beforeSaveResult !== false) {
                $res = true;
                
                foreach ($this->getAssociations() as $assoc) {
                    if ($assoc->beforeSave($this, $this->_errors) === false) $res = false;
                }
                $res = $res && $this->saveOwnRow();
                $this->_peIdentifier = $res? $this->mapper->getIdentifier($this) : null;
                if ($res) {
                    foreach ($this->getAssociations() as $assoc) {
                        if ($assoc->afterSave($this, $this->_errors) === false) $res = false;
                    }
                }
                
                if ($res) {
            
                    $this->lastOperation = $isNew? self::OPERATION_CREATE : self::OPERATION_UPDATE;
                    
                    $this->_isBeingStored = false;
                    $afterSaveResult = $this->doAfterSave();
                    $this->triggerEvent(self::EVENT_AFTER_SAVE, array(& $afterSaveResult));
                    if ($afterSaveResult === false) $res = false;
                    if ($res !== false) {
                        $this->doOnActual(self::ACTUAL_REASON_SAVE);
                    }
                    $this->notifyObjectCollections(Ac_I_LifecycleAwareCollection::STAGE_SAVED);
                } else {
                    $this->doOnSaveFailed();
                    $this->triggerEvent(self::EVENT_ON_SAVE_FAILED);
                }
            }
            else {
                $res = false;
            }
        } else {
            $res = true;
        }
        return $res;
    }
    
    /**
     * @deprecated
     */
    function store() {
        return $this->save();
    }
    
    function delete() {
        if (!$this->isPersistent()) {
            throw new Ac_E_InvalidUsage(
                "Attempt to delete non-persistent record (".get_class($this)
                ." of ".$this->mapper->getId()."); check with isPersistent() first");
        }
        if ($this->_isDeleted) return true;
            else $this->_isDeleted = true;
        $deleteResult = $this->doBeforeDelete();
        $this->triggerEvent(self::EVENT_BEFORE_DELETE, array(& $deleteResult));
        if ($deleteResult !== false) {
            $res = (bool) $this->mapper->peDelete($this, $this->getHyData(), $error);
            if ($res) {
                $this->lastOperation = self::OPERATION_DELETE;
            	$this->_peIdentifier = null;
                $this->doAfterDelete();
                $this->triggerEvent(self::EVENT_AFTER_DELETE);
                $this->notifyObjectCollections(Ac_I_LifecycleAwareCollection::STAGE_DELETED);
            } else {
                $this->_errors['_delete']['db'] = $error;
            }
        } else {
            $res = false;
            $this->doOnDeleteFailed();
            $this->triggerEvent(self::EVENT_ON_DELETE_FAILED);
        }
        if (!$res) $this->_isDeleted = false;
        return $res;
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    function getMapper($mapperClass = false) {
        $res = null;
        if ($mapperClass === false) {
            $mapperClass = $this->_mapperClass;
            if ($this->mapper) $res = $this->mapper;
        }
        if (!$res) {
            if (!$mapperClass) trigger_error (__FILE__."::".__FUNCTION__." - mapperClass not specified", E_USER_ERROR);
            $res = Ac_Model_Mapper::getMapper($mapperClass);
        }
        return $res;
    }
    
    /**
     * @param bool $newValues: return new values instead of old ones
     * @param bool $strict Whether to do a strict comparison to check for changes
     * @return array (fieldName => oldValue) for changed fields
     */
    function getChanges($newValues = false, $field = false, $strict = true) {
        $res = array();
        foreach ($this->_oldValues as $fieldName => $fieldValue) {
            if ($strict? $this->$fieldName !== $fieldValue : $this->$fieldName != $fieldValue) $res[$fieldName] = $newValues? $this->$fieldName : $fieldValue;
        }
        if ($field !== false) {
            if (array_key_exists($field, $res)) $res = $res[$field];
            else $res = false;
        }
        return $res;
    }
    
    function isChanged($field, $strict = true) {
        return array_key_exists($field, $this->_oldValues) && ($strict? ($this->_oldValues[$field] !== $this->{$field}) : ($this->_oldValues[$field] != $this->{$field}));
    }
    
    function reset($revert = false) {
        $this->setDefaults(true);
        if ($revert && $this->_oldValues) {
            foreach ($this->_oldValues as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->_peIdentifier = null;
            $this->_memorizeFields();
        }
        $stage = $revert && $this->isPersistent()? 
            Ac_I_LifecycleAwareCollection::STAGE_REVERTED : Ac_I_LifecycleAwareCollection::STAGE_CREATED;
        $this->notifyObjectCollections($stage);
    }
    
    /**
     * @deprecated
     * use Ac_Model_Object::listDataProperties instead
     * @return array
     */
    function listPublicProperties() {
        return $this->listDataProperties();
    }

    /**
     * TODO: either leave listPublicProperties or leave listDataProperties
     * @return array
     */
    final function listDataProperties() {
        $c = $this->metaCacheMode > self::META_CACHE_NONE;
        
        if ($c && isset(self::$metaCache[$mc = $this->metaClassId])
            && isset(self::$metaCache[$mc][__FUNCTION__]))
            return self::$metaCache[$mc][__FUNCTION__];
        
        $res = $this->listOwnDataProperties();
        
        $this->triggerEvent(self::EVENT_ON_LIST_DATA_PROPERTIES, array(& $res));
        
        if ($c) self::$metaCache[$mc][__FUNCTION__] = $res;
        return $res;
    }
    
    /**
     * Returns unique per-app ID of current model object
     * @return string
     */
    function getIdentifier() {
        if ($this->_peIdentifier === false) {
            $res = $this->mapper->getIdentifier($this);
        } else {
            $res = $this->_peIdentifier;
        }
        return $res;
    }
    
    function getPeIdenifier() {
        return $this->_peIdentifier;
    }

    /**
     * @deprecated
     * @see Ac_Model_Object::getIdentifier()
     */
    function getPrimaryKey() {
        return $this->getIdentifier();
    }
    
    /**
     * @deprecated
     * Use isPersistent() to check if record has been saved
     */
    function hasFullPrimaryKey() {
        return $this->isPersistent() || $this->mapper->calcIdentifier($this) !== null;
    }
    
    function matchesIdentifier($idOrIds) {
        if (!is_array($idOrIds)) $idOrIds = array($idOrIds); 
        if ($this->_peIdentifier !== null) $pk = $this->_peIdentifier;
        	else $pk = $this->mapper->getIdentifier($this);
        foreach ($idOrIds as $k) if ($pk == $k) return true;
        return false;        
    }
    
    /**
     * @deprecated
     * Use matchesIdentifier()
     */
    function matchesPk($oneOrMorePks) {
        if (!is_array($oneOrMorePks)) $oneOrMorePks = array($oneOrMorePks); 
        if ($this->_peIdentifier !== null) $pk = $this->_peIdentifier;
        	else $pk = $this->mapper->getIdentifier($this);
        foreach ($oneOrMorePks as $k) if ($pk == $k) return true;
        return false;        
    }
    
    function matchesFields($fields, $strict = false) {
        if ($strict) {
            foreach ($fields as $f => $v) if (($v != null && !isset($this->$f)) || $this->$f != $v) return false;
        } else {
            foreach ($fields as $f => $v) if (($v != null && !isset($this->$f)) || $this->$f !== $v) return false;
        }
        return true;
    }
    
    /**
     * Returns values of specified fields
     * @param bool|string|array $fieldNames Name(s) of fields to retrieve. All fields are returned by default.
     * @return array ($fieldName => $fieldValue)
     */
    function getDataFields($fieldNames = false) {
        if ($fieldNames === false) {
            $fieldNames = $this->listDataProperties();
        }
        elseif (!is_array($fieldNames)) $fieldNames = array($fieldNames);
        $res = array();
        foreach ($fieldNames as $fn) 
            $res[$fn] = $this->$fn;
        return $res;
    }
    
    /**
     * Checks record's presence in the database using all known "unique" indices. Since some "unique" indices can be not backed by the database, 
     * arrays of found PKs are returned for each index.
     *  
     * @param bool $dontReturnOwnKey If row with same PK as one of current instance is found, don't add it's PK to resultset
     * @param bool $checkNewRecords Whether to check in-memory newly created records
     * @return array($indexName => array($pk1, $pk2...))
     */    
    function checkPresence($dontReturnOwnKey = false, $checkNewRecords = false) {
       return $this->mapper->checkRecordPresence($this, $dontReturnOwnKey, array(), array(), $checkNewRecords); 
    }
    
    function isPersistent() {
        $res = ($this->_peIdentifier !== null);
        return $res;
    }
    
    function forcePersistent($persistent = true) {
        if ($persistent) {
            $this->_peIdentifier = false;
            if (($id = $this->mapper->getIdentifier($this)) === null) {
                throw new Exception("Cannot force-persistent an object without present identifier");
            }
            $this->_peIdentifier = $id;
        } else {
            $this->_peIdentifier = null;
        }
    }
    
    function _getCompleteUniqueIndices() {
        $m = $this->mapper;
        $res = array();
        foreach ($m->listUniqueIndices() as $idx) {
            $d = array();
            foreach(($flds = $m->listUniqueIndexFields($idx)) as $f) {
                if (($this->$f !== false) && ($this->$f !== null)) $d[$f] = $this->$f; 
            }
            if (count($d) == count($flds)) $res[$idx] = $d; 
        }
        return $res;
    }
    
    // +---------------------- cloning and comparison support methods ----------------------+ 
    
    /**
     * @return Ac_Model_Object
     */
    function copy() {
        $m = $this->mapper;
        $copy = $m->createRecord();
        $flds = array_diff($this->listDataProperties(), $this->doListNonCopiedFields());
        foreach ($flds as $v) {
            $copy->$v = $this->$v;
        }
        $this->doOnCopy($copy);
        $this->triggerEvent(self::EVENT_ON_COPY, array($copy));
        return $copy;
    }
    
    /**
     * Compares this objects' fields with other object. Excludes $this->doListNonComparedFields() from the comparison.
     * Base implementation of this method compares classes and data fields of other object. 
     *
     * @param Ac_Model_Object $otherObject
     * @param array|false $assocList List and order of associations to compare. 'False' means default (doListDefaultComparedAssociations()). Can be recursive, i.e. ('assoc1', 'assoc2', 'assoc3' => array('assoc3.1', 'assoc3.2', ...)) 
     */
    function equals($otherObject, $assocList = false) {
        
        // prevent recursion
        if ($this->_isBeingCompared) return true;
        
        $this->_isBeingCompared = true;
        $res = false;
        if (is_a($otherObject, get_class($this))) {
            $srcFields = $this->getDataFields();
            $destFields = $otherObject->getDataFields();
            $res = true;
            $fields = array_diff(array_keys($srcFields), $this->doListNonComparedFields());
            $this->triggerEvent(self::EVENT_ON_LIST_NON_COMPARED_FIELDS, array(& $fields));
            foreach ($fields as $k) {
                $v = $srcFields[$k];
                if (!array_key_exists($k, $destFields) || ($destFields[$k] != $v)) {
                    $res = false;
                    break; 
                }
            }
            if ($assocList === false) {
                $assocList = $this->doListDefaultComparedAssociations();
                $this->triggerEvent(self::EVENT_ON_LIST_DEFAULT_COMPARED_ASSOCIATIONS, array(& $assocList));
            }
            
            if (is_array($assocList)) foreach ($assocList as $assocName => $subList) {
                if (!is_array($subList)) {
                    $assocName = $subList;
                    $subList = false;
                }
                if (!($c = $this->_compareAssociation($otherObject, $assocName, $subList))) {
                    $res = false; 
                    break;
                }
            }
            if ($res) {
                if ($this->doOnExtraCompare($otherObject) === false) {
                    $res = false;
                }
                $this->triggerEvent(self::EVENT_ON_COMPARE, array($otherObject, & $res));
            }
        }
        $this->_isBeingCompared = false;
        return $res;
    }
    
    /**
     * Returns true if both associations are 'equal' (refer to same number of objects for which Ac_Model_Object::equals() returns true), false otherwise
     * 
     * @param Ac_Model_Object $otherObject
     * @param string $assocName Name of association property
     * @param array|false subAssocList List and order of associations to compare in associated objects (is passed as second parameter to Ac_Model_Object::equals() call). False means 'use default'
     */
    function _compareAssociation($otherObject, $assocName, $subAssocList = false) {
        $c1 = get_class($this);
        $c2 = get_class($otherObject);
        
        $c1 = get_class($this);
        $c2 = get_class($otherObject);
        
        $a1 = $this->getAssoc($assocName);
        $a2 = $otherObject->getAssoc($assocName);
        
        $res = false;
        if ($a1 && $a2) {
            if (!is_array($a1) && !is_array($a2)) {
                $arr1 = array($a1);
                $arr2 = array($a2);
            } elseif (is_array($a1) && is_array($a2)) {
                $arr1 = $a1;
                $arr2 = $a2;
            } else {
                $c1 = get_class($this);
                $c2 = get_class($otherObject);
                trigger_error ("Association '{$assocName}' is of different cardinality for two compared objects (of {$c1} and {$c2} classes)", E_USER_WARNING);
            }
            if (isset($arr1) && isset($arr2)) {
                if (count($arr1) == count($arr2)) {
                    $found = true;
                    foreach (array_keys($arr1) as $k1) {
                        $found = false;
                        foreach (array_keys($arr2) as $k2) {
                            if ($arr1[$k1]->equals($arr2[$k2], $subAssocList)) {
                                $found = true;
                                unset($arr2[$k2]);
                            }
                        }
                        if (!$found || !count($arr2)) break;
                    }
                    $res = $found;
                } else {
                    // If associations have different number of objects, they cannot be equal at all
                    $res = false;
                }
            }
        } elseif (!$a1 && !$a2) {
            $res = true;
        }
        return $res;
    }

    protected function cleanupReferences($otherObject) {
        foreach (array_keys($vars = get_object_vars($this)) as $k) {
            if (is_object($this->$k) && Ac_Util::sameObject($this->$k, $otherObject)) {
                $this->$k = null;
            } elseif (is_array($this->$k)) {
                $tmp = $this->$k;
                $loaded = $k.'Loaded';
                foreach (array_keys($tmp) as $kk) {
                    if (is_object($tmp[$kk]) && $tmp[$kk] === $otherObject) {
                        unset($tmp[$kk]);
                        if (array_key_exists($loaded, $vars)) {
                            if ($otherObject instanceof Ac_Model_Object && $otherObject->isPersistent()) {
                                $this->$loaded = false;
                            }
                        }
                    }
                }
                $this->$k = $tmp;
            }
        }
    }
    
    function cleanupMembers() {
        $this->doOnCleanup();
        $this->triggerEvent(self::EVENT_ON_CLEANUP);
        $vars = array_intersect_key(get_class_vars(get_class($this)), get_object_vars($this));
        foreach ($this->registeredCollections as $c) {
            $c->unregisterObject($this);
        }
        foreach (array_keys($vars) as $k) if (isset($this->$k)) {
            if (is_array($this->$k)) {
                $tmp = $this->$k;
                foreach (array_keys($tmp) as $kk) {
                    if (is_a($tmp[$kk], 'Ac_Model_Object')) {
                        $o = $tmp[$kk];
                        $o->cleanupReferences($this);
                        unset($o);
                        unset($tmp[$kk]);
                    }
                }
                unset($tmp);
            }
            if (is_a($this->$k, 'Ac_Model_Object')) {
                $o = $this->$k;
                $o->cleanupReferences($this);
                unset($o);
            }
            if (is_object($this->$k)) $this->$k = $vars[$k];      
        }
    }
    
    final function setDefaults($full = false) {
        $this->_isDeleted = false;
        $defs = $this->mapper->getDefaults($full);
        $cv = get_class_vars(get_class($this));
        // Take defaults from class vars since they can be overridden there
        foreach (array_intersect_key($cv, $defs) as $k => $v) {
            $defs[$k] = $v;
        }
        $this->doOnSetDefaults($defs, $full);
        $this->triggerEvent(self::EVENT_ON_SET_DEFAULTS, array(& $defs, $full));
        foreach ($defs as $k => $v) {
            $this->$k = $v;
        }
    }
    
    protected function doOnSetDefaults(array & $defaults, $full = false) {
        foreach ($defaults as $k => $v) {
            if ($v === 'CURRENT_TIMESTAMP') $defaults[$k] = date('Y-m-d H:i:s');
        }
    }
    
    function __isset($name) {
        if (in_array($name, $this->listFields())) $res = true; // TODO: check if this is right (in PHP isset(NULL) is FALSE)
        elseif (strpos($name, '[') !== false) {
            $res = $this->hasProperty($name);
        } else $res = parent::__isset($name);
        return $res;
    }
    
    function & __get($name) {
        
        if ($name === 'app') return $this->getApp();
        if ($name === 'db') return $this->getDb();
        
        $tmp = $this->_magicGetLock;
        $this->_magicGetLock = $name;
        if (in_array($name, $this->listFields()) || strpos($name, '[') !== false) {
            $res = $this->getField($name);
        } else {
            $res = & parent::__get($name);
        }
        $this->_magicGetLock = $tmp;
        return $res;
    }
    
    function _getSingleFieldItem($head) {
        if ($this->_magicGetLock === $head) {
            if (Ac_Accessor::methodExists($this, $m = 'get'.$head)) return $this->$m();
            return Ac_Mixin::__get($head);
        }
        return parent::_getSingleFieldItem($head);
    }
    
    function _setOwnSingleFieldItem($head, $value) {
        if ($this->_magicSetLock === $head) {
            if (Ac_Accessor::methodExists($this, $m = 'set'.$head)) return $this->$m($value);
            return Ac_Mixin::__set($head, $value);
        }
        return parent::_setOwnSingleFieldItem($head, $value);
    }
    
    function __set($name, $value) {
        $tmp = $this->_magicSetLock;
        $this->_magicSetLock = $name;
        if (in_array($name, $this->listFields()) || strpos($name, '[') !== false) {
            $this->setField($name, $value);
        }
        else {
            parent::__set($name, $value);
        }
        $this->_magicSetLock = $tmp;
    }
    
    protected function notifyFieldChanged($field) {
    }
    
    protected function intResetReferences() {
        $fkData = $this->mapper->getFkFieldsData();
        foreach ($fkData as $fieldName => $details) {
            $null = false;
            if (is_null($fieldName)) {
                $null = true;
            } else {
                foreach ($details['relations'] as $relId => $inf) {
                    $objectFieldName = $inf['varName'];
                    $c = false;
                    if ($this->isChanged($fieldName, false) && strlen($objectFieldName)) {
                        $c = true;
                        $this->$objectFieldName = false;
                    }
                    // set nullable empty foreign keys to null
                    if ($c || $this->isChanged($fieldName, true)) {
                        if ((is_null($this->$fieldName) || !strlen($this->$fieldName)) && $details['isNullable']) {
                            $this->$fieldName = null;
                            $null = true;
                        }
                    }
                    if ($null) {
                        if ($inf['otherFields']) {
                            foreach ($inf['otherFields'] as $otherField) {
                                if (isset($fkData[$otherField])) {
                                    $other = $fkData[$otherField];
                                    if ($other['isNullable'] && !$other['isRestricted'])
                                        $this->$otherField = null;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    final function getAssociations() {
        if ($this->associations === false) {
            $this->associations = $this->mapper->getAssociations();
            $this->doOnGetAssociations($this->associations);
            $this->triggerEvent(self::EVENT_ON_GET_ASSOCIATIONS, array(& $this->associations));
        }
        return $this->associations;
    }
    
    /**
     * @return array
     */
    final function listAssociationObjects() {
        if ($this->associations === false) $this->getAssociations();
        return array_keys($this->associations);
    }

    /**
     * @return Ac_Model_Association_Abstract
     */
    final function getAssociationObject($id, $dontThrow = false) {
        $res = null;
        if ($this->associations === false) $this->getAssociations();
        if (isset($this->associations[$id])) $res = $this->associations[$id];
        if (!$res && !$dontThrow) 
            throw Ac_E_InvalidCall::noSuchItem ('association', $id, 'listAssociationsObjects');
        return $res;
    }
    
    function addAssociations($associations) {
        if ($this->associations === false) $this->getAssociations();
        foreach ($associations as $k => $v) {
            if (!$v) {
                if (!is_numeric($k)) unset($this->associations[$k]);
                unset($associations[$k]);
            }
        }
        $instances = Ac_Prototyped::factoryCollection($associations, 'Ac_I_ModelAssociation', array(), 'id', true);
        foreach ($instances as $i) if ($i instanceof Ac_Model_Association_ModelObject) 
            $i->setMapper($this->mapper);
        Ac_Util::ms($this->associations, $instances);
    }
    
    function __call($method, $arguments) {
        if (!strncmp('_store', $method, 6)) {
            return true;
        }
        return parent::__call($method, $arguments);
    }
    
    function getRegisteredObjectCollections() {
        return $this->registeredCollections;
}

    function isObjectCollectionRegistered(Ac_I_ObjectsCollection $collection) {
        return in_array($collection, $this->registeredCollections, true);
    }
    
    function notifyUnregisteredFromCollection(Ac_I_ObjectsCollection $collection) {
        if (false !== $k = array_search($collection, $this->registeredCollections, false)) {
            unset($this->registeredCollections[$k]);
        }
    }
    
    function notifyRegisteredInCollection(Ac_I_ObjectsCollection $collection) {
        if (false === array_search($collection, $this->registeredCollections, true)) {
            $this->registeredCollections[] = $collection;
        }
    }
    
}
