<?php

abstract class Ac_Model_Object extends Ac_Model_Data {

    const ACTUAL_REASON_LOAD = 1;
    
    const ACTUAL_REASON_SAVE = 2;
    
    const OPERATION_NONE = 0;
    
    const OPERATION_LOAD = 1;
    
    const OPERATION_CREATE = 2;

    const OPERATION_UPDATE = 6;
    
    const OPERATION_DELETE = 8;
    
    /**
     * If $model::tracksChanges() returns true or Ac_Model_Object::CHANGES_BEFORE_SAVE, 
     * changes will be destroyed immediately after database operation 
     * (and before doAfterSave() method)  
     */
    const CHANGES_BEFORE_SAVE = 1;
    
    /**
     * If $model::tracksChanges() returns AC_CHANGES_AFTER_SAVE, old values will be available in 
     * doAfterSave() method 
     */
    const CHANGES_AFTER_SAVE = 2;

    /**
     * function onCreate()
     */
    const EVENT_ON_CREATE = 'onCreate';

    /**
     * function onAfterLoad()
     */
    const EVENT_AFTER_LOAD = 'onAfterLoad';
    
    /**
     * function onActual($reason = self::ACTUAL_REASON_LOAD)
     */
    const EVENT_ON_ACTUAL = 'onActual';

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
     * function onDeleteFailed()
     */
    const EVENT_ON_DELETE_FAILED = 'onDeleteFailed';

    /**
     * function onBeforeDelete(& $result)
     */
    const EVENT_BEFORE_DELETE = 'onBeforeDelete';

    /**
     * function onAfterDelete()
     */
    const EVENT_AFTER_DELETE = 'onAfterDelete';

    /**
     * function onCanDelete(& $result)
     */
    const EVENT_ON_CAN_DELETE = 'onCanDelete';

    /**
     * function onCopy(Ac_Model_Object $copy)
     */
    const EVENT_ON_COPY = 'onCopy';

    /**
     * function onCompare(Ac_Model_Object $other, & $compareResult)
     */
    const EVENT_ON_COMPARE = 'onCompare';

    /**
     * function onListDefaultComparedAssociations(array & $associations)
     */
    const EVENT_ON_LIST_DEFAULT_COMPARED_ASSOCIATIONS = 'onListDefaultComparedAssociations';

    /**
     * function onListNonCopiedFields(array & $fields)
     */
    const EVENT_ON_LIST_NON_COPIED_FIELDS = 'onListNonCopiedFields';

    /**
     * function onListNonComparedFields(array & $fields)
     */
    const EVENT_ON_LIST_NON_COMPARED_FIELDS = 'onListNonComparedFields';

    /**
     * function onSetDefaults(array & $defaults, $full)
     */
    const EVENT_ON_SET_DEFAULTS = 'onSetDefaults';
    
    /**
     * function onCleanup()
     */
    const EVENT_ON_CLEANUP = 'onCleanup';
    
    /**
     * function onListDataProperties(array & $dataProperties)
     */
    const EVENT_ON_LIST_DATA_PROPERTIES = 'onListDataProperties';
    
    /**
     * In-memory id
     * @var int
     */
    var $_imId = 0;
    
    /**
     * Name of primary key column
     * @var string
     */
    var $_pk = false;
    
    var $_error = false;
    
    var $_oldValues = false;
    
    var $_otherValues = array();
    
    var $_mapperClass = false;
    
    var $_isImport = false;
    
    var $_isReference = false;
    
    var $_isBeingStored = false;
    
    var $_isBeingCompared = false;
    
    var $_isDeleted = false;
    
    var $_hasDefaults = false;

    /**
     * Original primary key; is used (to check record persistance and correctly update and delete record) only if $this->tracksPk() returns TRUE.
     * @var mixed
     */
    var $_origPk = null;
    
    protected $lastOperation = self::OPERATION_NONE;
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $mapper = false;
    
    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->mapper->getApplication();
    }
    
    /**
     * @return Ae_Sql_Db
     * @deprecated 
     */
    function getSqlDb() {
        return $this->getDb();
    }
    
    function getDb() {
        return $this->getMapper()->getDb();
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
    
    function doOnDeleteFailed() {
    }
    
    function doBeforeDelete() {
    }
    
    function doAfterDelete() {
    }
    
    function doOnCanDelete() {
    }
    
    function doOnCreate() {
    }
    
    function doListNonCopiedFields() {
        $m = $this->getMapper();
        $res = $this->getMapper()->listPkFields();
        return $res;
    }
    
    function doListNonComparedFields() {
        $m = $this->getMapper();
        $res = $this->getMapper()->listPkFields();
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
    
    /**
     * @param Ac_Model_Object $copy
     */
    function doOnCopy($copy) {
    }
    
    final function canDelete() {
        $result = $this->doOnCanDelete();
        $this->triggerEvent(self::EVENT_ON_CAN_DELETE, array (& $result));
        $res  = $result !== false;
        return $res;
    }
    
    function getExcludeList() {
        $vars = array_keys(get_object_vars($this));
        foreach ($vars as $i => $var) if ($var{0} == "_") unset($vars[$i]);
        return $vars;
    }
    
    function _describeIndex($indexName, $indexFields = false) {
        if ($indexFields === false) {
            $m = $this->getMapper();
            $indexFields = $m->listUniqueIndexFields($indexName);
        }
        $r = array();
        foreach ($indexFields as $f) {
            if (!$this->hasProperty($f)) continue;
            $p = $this->getPropertyInfo($f);
            $r[] = "'".($p->caption? $p->caption : $f)."'";
        }
        return implode(", ", $r);
    }
    
    function _checkOwnFields() {
        parent::_checkOwnFields();
        $dbp = $this->checkDatabasePresence(true);
        if ($dbp) {
            $m = $this->getMapper();
            foreach ($dbp as $indexName => $pks) {
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
            $m = $this->getMapper();
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
    
    function __construct($mapperOrMapperClass = null) {
        
        if (is_array($mapperOrMapperClass)) {
            $prototype = $mapperOrMapperClass;
            if (isset($prototype['mapper'])) $mapperOrMapperClass = $prototype['mapper'];
            elseif (isset($prototype['mapperClass'])) $mapperOrMapperClass = $prototype['mapperClass'];
            else $mapperOrMapperClass = null;
        } else {
            $prototype = array();
        }
        
        static $imId = 0;
        $this->_imId = $imId++;

        if (is_null($mapperOrMapperClass) && strlen($this->_mapperClass)) $mapperOrMapperClass = $this->_mapperClass;
        
        if ($mapperOrMapperClass instanceof Ac_Model_Mapper) {
            $mapper = $mapperOrMapperClass;
        } else {
            $mapper = Ac_Model_Mapper::getMapper($mapperOrMapperClass);
        }
        
        if (!$mapper) throw new Exception("Cannot determine \$mapper for ".get_class($this));
        
        $this->mapper = $mapper;
        $this->mapper->registerRecord($this);
        
        if (!strlen($this->_mapperClass)) $this->_mapperClass = $mapper->getId();
        
        $this->_pk = $mapper->pk;
        $this->_tableName = $mapper->tableName;
     
        parent::__construct($prototype);
        
        if (!$this->_hasDefaults) {
            foreach ($mapper->getDefaults() as $k => $v) $this->$k = $v;
        }
        
        if ($this->tracksChanges()) $this->_memorizeFields();
        
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
     * @param mixed oid null, primary key or associative array with row 
     * @param bool $isRow Whether to treat first parameter as an associative row
     */
    function load ($oid = null, $isRow = false) {
        
        if ($isRow) {
            $this->_otherValues = array();
            $hyData = $this->mapper->peConvertForLoad($this, $oid);
            foreach ($this->listDataProperties() as $propName) {
                if (array_key_exists($propName, $hyData)) {
                    $this->$propName = $oid[$propName];
                    unset($oid[$propName]);
                }
            }
            $row = $oid;
            $this->_otherValues = $oid;
            $res = true;
        } else {
            if (!$this->isPersistent() && $this->isReference()) {
                $res = $this->_loadReference();
            } else {
                $res = $this->_legacyLoad($oid, $row);
            }
        }
        if ($this->tracksPk()) {
            $this->_origPk = $res? $this->getPrimaryKey() : null;
        }
        $this->doAfterLoad();
        $this->triggerEvent(self::EVENT_AFTER_LOAD);
        if ($res) $this->doOnActual(self::ACTUAL_REASON_LOAD);
        if ($this->isPersistent()) {
            $m = $this->getMapper();
            $m->notifyKeyAssigned($this);
        }
        if ($this->tracksChanges()) $this->_memorizeFields();
        
        if ($res) $this->lastOperation = self::OPERATION_LOAD;
        
        return $res;
    }
    
    function _legacyLoad($oid, & $row) {
        $k = $this->_pk;

        if ($oid !== null) {
            $this->$k = $oid;
        }

        $oid = $this->$k;
        
        if ($oid === null) {
            return false;
        }
        
        $this->reset();

        if ($oid !== null) {
            $this->$k = $oid;
        }
        
        $props = $this->listDataProperties();
        
        if ($hyData = $this->mapper->peLoad($this, $this->getPrimaryKey())) {
            $row = & $hyData;
            $hyData = $this->mapper->peConvertForLoad($this, $hyData);
            foreach ($props as $propName) {
                if (array_key_exists($propName, $hyData)) {
                    $this->$propName = $hyData[$propName];
                }
            }
            $res = true;
        } else {
            $res = false;
        }
        
        return $res;
    }
    
    protected function getHyData() {
        $res = array();
        foreach ($this->listDataProperties() as $propName) {
            $res[$propName] = $this->$propName;
        }
        return $res;
    }
    
    function _legacyStore() {
        $k = $this->_pk;
        $mapper = $this->getMapper();
        $tpk = $this->tracksPk();
        $hyData = $this->getHyData();
        $error = false;
        
        if ($this->isPersistent()) {
            
            $hyData[$k] = $tpk? $this->_origPk : $this->$k;
            $hyData = $this->mapper->peConvertForSave($this, $hyData);
            $res = (bool) $this->mapper->peSave($this, $hyData, true, $error, $newData);
            if (is_array($newData)) foreach ($newData as $k => $v) $this->$k = $v;
            
        } else {
            
            $skipKey = ($aif = $mapper->getAutoincFieldName()) == $k;
            if (array_key_exists($k, $hyData) && !is_null($hyData[$k])) $skipKey = false;
            if ($skipKey) unset($hyData[$k]);
            
            $hyData = $this->mapper->peConvertForSave($this, $hyData);
            $res = $this->mapper->peSave($this, $hyData, false, $error, $newData);
            if ($res) {
                if (is_array($newData)) foreach ($newData as $k => $v) $this->$k = $v;
                $res = true;
            } else {
                $res = false;
            }
            
        }
        
        if ($tpk) {
            if ($this->_origPk != $this->$k) {
                $this->mapper->notifyKeyAssigned($this, $this->_origPk);
            }
        }

        if (!$res) {
            if ($error !== false) {
                $this->_errors['_store']['db'] = $error;
                $this->_checked = true; // otherwise next getErrors() will trigger check() which will clean this error message
            }
        } else {
            $mapper->notifyKeyAssigned($this);
            if (($t = $this->tracksChanges()) && ($t !== self::CHANGES_AFTER_SAVE)) $this->_memorizeFields();
        }
        return $res;
    }
    
    function forget() {
        $m = $this->getMapper();
        $m->forget($this);
    }
    
    function store() {
        if (!$this->_isBeingStored) { // we have to prevent recursion while saving complex in-memory record graphs
            $this->_isBeingStored = true;
            $this->intResetReferences();
            $beforeSaveResult = $this->doBeforeSave();
            if ($this->_isReference && !$this->isPersistent()) $this->_loadReference();
            $isNew = !$this->isPersistent();
            
            $this->triggerEvent(self::EVENT_BEFORE_SAVE, array(& $beforeSaveResult));
            if ($beforeSaveResult !== false) {
                $res = true;
                $res = $res && ($this->_storeReferencedRecords() !== false);
                $res = $res && $this->_legacyStore();
                if ($this->tracksPk()) $this->_origPk = $res? $this->getPrimaryKey() : null;
                $res = $res && ($this->_storeReferencingRecords() !== false);
                $res = $res && ($this->_storeNNRecords() !== false);
                if ($res) {
            
                    $this->lastOperation = $isNew? self::OPERATION_CREATE : self::OPERATION_UPDATE;
                    
                    $this->_isBeingStored = false;
                    $afterSaveResult = $this->doAfterSave();
                    $this->triggerEvent(self::EVENT_AFTER_SAVE, array(& $afterSaveResult));
                    if ($afterSaveResult === false) $res = false;
                    if ($res !== false) {
                        $this->doOnActual(self::ACTUAL_REASON_SAVE);
                    }
                    if (($t = $this->tracksChanges()) && ($t === self::CHANGES_AFTER_SAVE)) 
                        $this->_memorizeFields();
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
    
    function delete() {
        if ($this->_isDeleted) return true;
            else $this->_isDeleted = true;
        if ($this->_isReference && !$this->isPersistent()) $this->_loadReference();
        $deleteResult = $this->doBeforeDelete();
        $this->triggerEvent(self::EVENT_BEFORE_DELETE, array(& $deleteResult));
        if ($deleteResult !== false) {
            if ($res = $this->_legacyDelete()) {
                $this->lastOperation = self::OPERATION_DELETE;
            	if ($this->tracksPk()) $this->_origPk = null;
                $this->doAfterDelete();
                $this->triggerEvent(self::EVENT_AFTER_DELETE);
                $this->mapper->forget($this);
            }
        } else {
            $res = false;
            $this->doOnDeleteFailed();
            $this->triggerEvent(self::EVENT_ON_DELETE_FAILED);
        }
        if (!$res) $this->_isDeleted = false;
        return $res;
    }
    
    function _legacyDelete() {
        $hyData = $this->getHyData();
        $res = (bool) $this->mapper->peDelete($this, $hyData, $error);
        if ($res) {
        } else {
            $this->_error = $error;
        }
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
    
    function getBindIgnore() {
        return '';
    }
    
    function bind($array, $ignore = '') {
        if (is_array($ignore)) $ignore = array_merge($ignore, $this->getBindIgnore());
            else $ignore .= $this->getBindIgnore();
        parent::bind($array, $ignore);
        $this->doOnBind($array, $ignore);
    }
    
    /**
     * Template method that should return TRUE if current record should remember old values of the fields
     * @return bool
     */
    function tracksChanges() {
        return true;
    }
    
    /**
     * Template method that should return TRUE if record's primary key can be updated 
     * (for example, for records with non-autoincremental primary key fields).
     * 
     * Standard implementation checks whether primary key is autoincrement field.  
     * 
     * @return bool
     */
    function tracksPk() {
    	return false;
    }
    
    /**
     * if $this->tracksChages(): returns array (fieldName => oldValue) for changed fields
     * @param bool $newValues: return new values instead of old ones
     * @return mixed Array or TRUE if record does not track changes
     */
    function getChanges($newValues = false, $field = false, $strict = true) {
        if (!$this->tracksChanges()) return true;
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
        if (!$this->tracksChanges()) return true;
        return array_key_exists($field, $this->_oldValues) && ($strict? ($this->_oldValues[$field] !== $this->{$field}) : ($this->_oldValues[$field] != $this->{$field}));
    }
    
    function getError() {
        if ($errors = $this->getErrors()) return Ac_Util::implode_r(";\n", $errors);
        if ($this->_error) return $this->_error;
    }
    
    function reset($revert = false) {
        $this->setDefaults(true);
        if ($revert && $this->_oldValues) {
            foreach ($this->_oldValues as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->_origPk = null;
            $this->_memorizeFields();
        }
        if (!$this->isPersistent()) $this->mapper->memorize($this);
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
    
    protected function listOwnDataProperties() {
        return $this->mapper->getColumnNames();
    }
    
    function getPrimaryKey() {
        return $this->{$this->_pk};
    }
    
    function hasFullPrimaryKey() {
        return $this->{$this->_pk} !== false && !is_null($this->{$this->_pk});
    }
    
    function matchesPk($oneOrMorePks) {
        if (!is_array($oneOrMorePks)) $oneOrMorePks = array($oneOrMorePks); 
        if ($this->tracksPk() && ($this->_origPk !== null)) $pk = $this->_origPk;
        	else $pk = $this->getPrimaryKey();
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
     * Checks record's presence in the database using all known "unique" indices. Since some "unique" indices can be not backed by the database, arrays of found PKs are
     * returned for each index.
     *  
     * @param bool $dontReturnOwnKey If row with same PK as one of current instance is found, don't add it's PK to resultset
     * @param bool $checkNewRecords Whether to check in-memory newly created records
     * @return array($indexName => array($pk1, $pk2...))
     * @see Ac_Model_Mapper::checkRecordUniqueness
     */    
    function checkDatabasePresence($dontReturnOwnKey = false, $checkNewRecords = false) {
       $mapper = $this->getMapper();
       return $mapper->checkRecordPresence($this, $dontReturnOwnKey, array(), array(), $checkNewRecords); 
    }
    
    function isPersistent() {
        if ($this->tracksPk()) $res = ($this->_origPk !== null);
    	else $res = (($this->{$this->_pk}) !== false) && (($this->{$this->_pk}) !== null);
        return $res;
    }
    
    function isReference() {
        return $this->_isReference;
    }
    
    function _setIsReference($isReference = true) {
        $this->_isReference = $isReference;
    }
    
    function _loadReference() {
        $res = false;
        $iData = $this->_getCompleteUniqueIndices();
        $where = array();
        foreach ($iData as $idx => $f) $where = array_merge($where, $f);
        
        if (count($where)) {
            $m = $this->getMapper();
            $s = $getSqlDb();
            $r = $m->loadRecordsByCriteria($c = $s->valueCriterion($where));
            if (count($r) == 1) {
                $res = true;
                $rec = $r[0];
                
                /*
                 * Often the reference is changed before it's loaded (especially when it's loaded from the store() method).
                 * We have not to overwrite fields that already were modified by an application. When record tracks its changes, we can 
                 * rely on isChanged() method; otherwise we assume that if field has default value, it was not changed (not too accurate).
                 */ 
                if ($this->tracksChanges()) {
                    foreach ($this->listDataProperties() as $v) {
                        if (!$this->isChanged($v)) $this->$v = $rec->$v;
                    }
                } else {
                    
                    // we have to rely on object_vars of prototype record instead of class_vars since onCreate() method can alter default values
                    $defaultVars = get_object_vars($m->getPrototype());
                    
                    foreach ($this->listDataProperties() as $v) {
                        if ($this->$v === $defaultVars[$v]) $this->$v = $rec->$v;
                    }
                }
            }
            if ($this->isPersistent()) $m->notifyKeyAssigned($this);
        }
        return $res;
    }
    
    function canDereference() {
        $res = count($this->_getCompleteUniqueIndices()) > 0;
        return $res;
    }
    
    function _getCompleteUniqueIndices() {
        $m = $this->getMapper();
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
    
    /**
     * Saves records that are referenced by current record and are linked to it in-memory 
     */
    function _storeReferencedRecords() {
    }
    
    /**
     * Saves records that are referencing current record and are linked to it in-memory
     */
    function _storeReferencingRecords() {
    }
    
    function _storeNNRecords() {
    }
    
    /**
     * Stores referenced records and populates this record foreign keys.
     */
    function _autoStoreReferenced($recordOrRecords, $fieldLinks, $errorKey) {
        $res = true;
        if ($recordOrRecords !== false && !is_null($recordOrRecords)) {
            if (is_array($recordOrRecords)) $r = $recordOrRecords;
                else $r = array(& $recordOrRecords);
            foreach (array_keys($r) as $k) {
                $rec = $r[$k];
                if ((!$rec->isPersistent() || $rec->getChanges())) {
                    if (!$rec->store()) {
                        $this->_errors[$errorKey][$k] = $rec->getErrors();
                        $res = false;
                    }
                }
                foreach ($fieldLinks as $sf => $df) $this->$sf = $rec->$df; 
            }
        }
        return $res;
    }
    
    /**
     * Populates referencing records' foreign keys from this record keys and stores them
     */
    function _autoStoreReferencing($recordOrRecords, $fieldLinks, $errorKey) {
        $res = true;
        if ($recordOrRecords !== false && !is_null($recordOrRecords)) {
            if (is_array($recordOrRecords)) $r = $recordOrRecords;
                else $r = array(& $recordOrRecords);
            foreach (array_keys($r) as $k) {
                $rec = $r[$k];
                foreach ($fieldLinks as $sf => $df) $rec->$df = $this->$sf;
                if ($rec->getChanges() && !$rec->_isDeleted) {
                    if (!$rec->store()) {
                        $this->_errors[$errorKey][$k] = $rec->getErrors();
                        $res = false;
                    }
                }
            }
        }
        return $res;
    }
    
    function _autoStoreNNRecords(& $recordOrRecords, $ids, $fieldLinks, $fieldLinks2, $midTableName, $errorKey, $midWhere = false) {
        $res = true;
        if ($recordOrRecords !== false && !is_null($recordOrRecords)) {
            $ids = array();
            if (is_array($recordOrRecords)) $r = $recordOrRecords;
                else $r = array(& $recordOrRecords);
                
            foreach (array_keys($r) as $k) {
                $rec = $r[$k];
                if ((!$rec->isPersistent() || $rec->getChanges())) {
                    if (!$rec->store()) {
                        $this->_errors[$errorKey][$k] = $rec->getErrors();
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
            foreach ($fieldLinks as $s => $d) $rowProto[$d] = $this->$s;
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
            $this->mapper->peReplaceNNRecords($this, $rowProto, $rows, $midTableName, $errors);
            if ($errors) {
                $this->_errors[$errorKey] = $errors;
                return $res;
            }
        }
        return $res;
    }
    
    // +---------------------- cloning and comparison support methods ----------------------+ 
    
    /**
     * @return Ac_Model_Object
     */
    function copy($asReference = null, $withPk = false) {
        $m = $this->getMapper();
        $copy = $m->createRecord();
        $flds = array_diff($this->listDataProperties(), $this->doListNonCopiedFields());
        if (!$asReference && !$withPk) $flds = array_diff($flds, $m->listPkFields());
        if ($withPk) $flds = array_merge($flds, $m->listPkFields());
        foreach ($flds as $v) {
            $copy->$v = $this->$v;
        }
        //$copy->_isReference = $this->_isReference;
        $copy->_setIsReference(is_null($asReference)? $this->isReference() : $asReference);
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

    function cleanupReferences($otherObject) {
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
        $this->triggerEvent(self::EVENT_ON_CLEANUP);
        $vars = get_class_vars(get_class($this));
        $m = $this->getMapper();
        $m->forget($this);
        foreach (get_class_vars(get_class($this)) as $k => $v) if (isset($this->$k)) {
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
        $res = in_array($name, $this->listFields());
        return $res;
    }
    
    function __get($name) {
        if (in_array($name, $this->listFields())) $res = $this->getField($name);
        elseif (!in_array($name, array_diff(array_keys(Ac_Util::getPublicVars($this)), array_keys(get_object_vars($this))))) {
            return $this->$name;
        } else {
            $rc = new ReflectionClass(get_class($this));
            $p = $rc->getProperty($name);
            if ($p->isPrivate()) $m = 'private';
            elseif ($p->isProtected()) $m = 'protected';
            else throw new Exception("Assertion: something is wrong in ".__METHOD__);
            throw new Ac_E_InvalidCall("Cannot access {$m} property '$name' in class ".get_class($this));
        }
    }
    
    function __set($name, $value) {
        if (in_array($name, $this->listFields())) $res = $this->setField($name, $value);
        elseif (!in_array($name, array_diff(array_keys(Ac_Util::getPublicVars($this)), array_keys(get_object_vars($this))))) {
            $this->$name = $value;
        } else {
            $rc = new ReflectionClass(get_class($this));
            $p = $rc->getProperty($name);
            if ($p->isPrivate()) $m = 'private';
            elseif ($p->isProtected()) $m = 'protected';
            else throw new Exception("Assertion: something is wrong in ".__METHOD__);
            throw new Ac_E_InvalidCall("Cannot access {$m} property '$name' in class ".get_class($this));
        }
    }
    
    protected function notifyFieldChanged($field) {
    }
    
    protected function intResetReferences() {
        $fkData = $this->getMapper()->getFkFieldsData();
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
                        if (!strlen($this->$fieldName) && $details['isNullable']) {
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
    
}

