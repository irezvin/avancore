<?php

/**
 * If $model::tracksChanges() returns true or AC_CHANGES_BEFORE_SAVE, changes will be destroyed immediately after database operation 
 * (and before afterSave() method)  
 */
define('AC_CHANGES_BEFORE_SAVE', 1);

/**
 * If $model::tracksChanges() returns true or AC_CHANGES_AFTER_SAVE, old values will be available in afterSave() method 
 */
define('AC_CHANGES_AFTER_SAVE', 2);

class Ac_Model_Object extends Ac_Model_Data {

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

    /**
     * Original primary key; is used (to check record persistance and correctly update and delete record) only if $this->tracksPk() returns TRUE.
     * @var mixed
     */
    var $_origPk = null;
    
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
     */
    function getSqlDb() {
        return $this->getMapper()->getSqlDb();
    }
    
    function doAfterLoad() {
    }
    
    function doBeforeSave() {
    }
    
    function doAfterSave() {
    }
    
    function doOnSaveFailed() {
    }
    
    function doBeforeDelete() {
    }
    
    function doAfterDelete() {
    }
    
    function canDelete() {
        // TODO: implement this method with respect to the associations
        return true;
    }
    
    function getExcludeList() {
        $vars = array_keys(get_object_vars($this));
        foreach ($vars as $i => $var) if ($var{0} == "_") unset($vars[$i]);
        return $vars;
    }
    
    function doOnCreate() {
        $this->setDefaultFields();
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
        $this->_oldValues = array();
        foreach (get_object_vars($this) as $varName => $value) {
            if ($varName{0} !== '_') $this->_oldValues[$varName] = $value; 
        }
    }
    
    function __construct($mapperOrMapperClass = null) {
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
        
        if (!strlen($this->_mapperClass)) $this->_mapperClass = $mapper->getId();
        
        $this->_pk = $mapper->pk;
        $this->_tableName = $mapper->tableName;
        $this->doOnCreate();
        
        if ($this->tracksChanges()) $this->_memorizeFields();
    }
    
    /**
     * @param mixed oid null, primary key or associative array with row 
     * @param bool $isRow Whether to treat first parameter as an associative row
     */
    function load ($oid = null, $isRow = false) {
        
        if ($isRow) {
            $k = $this->_pk;
            $this->_otherValues = array();
            $hyData = $this->mapper->peConvertForLoad($oid);
            foreach ($this->listOwnProperties() as $propName) {
                if (array_key_exists($propName, $hyData)) {
                    $this->$propName = $oid[$propName];
                    unset($oid[$propName]);
                }
            }
            $this->_otherValues = $oid;
            $this->doAfterLoad();
            $res = true;
        } else {
            if (!$this->isPersistent() && $this->isReference()) {
                $res = $this->_loadReference();
            } else {
                $res = $this->_legacyLoad($oid, $prefix);
            }
            $this->doAfterLoad();
        }
        if ($this->tracksPk()) {
            $this->_origPk = $res? $this->getPrimaryKey() : null;
        }
        if ($this->isPersistent()) {
            $m = $this->getMapper();
            $m->forget($this);
        }
        if ($this->tracksChanges()) $this->_memorizeFields();
        return $res;
    }
    
    function _legacyLoad($oid, $prefix) {
        $k = $this->_pk;

        if ($oid !== null) {
            $this->$k = $oid;
        }

        $oid = $this->$k;
        
        if ($oid === null) {
            return false;
        }
        
        $this->reset();
        
        $props = $this->listOwnProperties();
        
        if ($hyData = $this->mapper->peLoad($this->getPrimaryKey())) {
            $hyData = $this->mapper->peConvertForLoad($hyData);
            foreach ($this->listOwnProperties() as $propName) {
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
        $sf = $this->_storesFalseValues(); 
        $res = array();
        foreach ($this->_listSavedMembers() as $propName) {
            $val = $this->$propName;  
            if ($sf || ($val !== false)) {
                $res[$propName] = $val;
            }
        }
        return $res;
    }
    
    function _legacyStore() {
        $k = $this->_pk;
        $mapper = $this->getMapper();
        $kv = array();
        $tpk = $this->tracksPk();
        $hyData = $this->getHyData();
        
        if ($this->isPersistent()) {
            
            $hyData[$k] = $tpk? $this->_origPk : $this->$k;
            $hyData = $this->mapper->peConvertForSave($hyData);
            $res = (bool) $this->mapper->peSave($hyData, true, $error);
            if ($res) $this->mapper->markUpdated();
        
        } else {
            
            $skipKey = ($aif = $mapper->getAutoincFieldName()) == $k;
            if ($skipKey) unset($hyData[$k]);
            
            $hyData = $this->mapper->peConvertForSave($hyData);
            $newData = $this->mapper->peSave($hyData, false, $error);
            if ($newData) {
                $this->mapper->markUpdated();
                if ($aif) $this->$k = $newData[$k];
                $res = true;
            } else {
                $res = false;
            }
            
        }

        if (!$res) {
            $this->_error = strtolower(get_class($this))."::store failed <br />".$error;
        } else {
            $mapper->forget($this);
            if (($t = $this->tracksChanges()) && ($t !== AC_CHANGES_AFTER_SAVE)) $this->_memorizeFields();
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
            if (($this->doBeforeSave() !== false)) {
                if ($this->_isReference && !$this->isPersistent()) $this->_loadReference();
                $res = true;
                
                $res = $res && ($this->_storeUpstandingRecords() !== false);
                $res = $res && $this->_legacyStore();
                $res = $res && ($this->_storeDownstandingRecords() !== false);
                $res = $res && ($this->_storeNNRecords() !== false);
                if ($res) {
                    $this->_isBeingStored = false;
                    if ($this->doAfterSave() === false) $res = false;
                    if (($t = $this->tracksChanges()) && ($t === AC_CHANGES_AFTER_SAVE)) $this->_memorizeFields();
                } else {
                    $this->doOnSaveFailed();
                }
            }
            else {
                $res = false;
            }
            if ($this->tracksPk()) $this->_origPk = $res? $this->getPrimaryKey() : null;
        } else {
            $res = true;
        }
        return $res;
    }
    
    function delete() {
        if ($this->_isDeleted) return true;
            else $this->_isDeleted = true;
        if ($this->_isReference && !$this->isPersistent()) $this->_loadReference();
        if ($this->doBeforeDelete() !== false) {
            if ($res = $this->_legacyDelete()) {
            	if ($this->tracksPk()) $this->_origPk = null;
                $this->doAfterDelete();
            }
        } else {
            $res = false;
        }
        if (!$res) $this->_isDeleted = false;
        return $res;
    }
    
    function _legacyDelete() {
        $hyData = $this->getHyData();
        $res = (bool) $this->mapper->peDelete($hyData, $error);
        if ($res) {
            $this->mapper->markUpdated();
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
        $res = parent::bind($array, $ignore);
        $this->doOnBind($array, $ignore);
    }
    
    /**
     * Template method that should return TRUE if current record should remember old values of the fields
     * @return bool
     */
    function tracksChanges() {
        return false;
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
    function getChanges($newValues = false, $field = false, $nonStrict = false) {
        if (!$this->tracksChanges()) return true;
        $res = array();
        foreach ($this->_oldValues as $fieldName => $fieldValue) {
            if ($nonStrict? $this->$fieldName != $fieldValue : $this->$fieldName !== $fieldValue) $res[$fieldName] = $newValues? $this->$fieldName : $fieldValue;
        }
        if ($field !== false) {
            if (array_key_exists($field, $res)) $res = $res[$field];
            else $res = false;
        }
        return $res;
    }
    
    function isChanged($field) {
        if (!$this->tracksChanges()) return true;
        return array_key_exists($field, $this->_oldValues) && ($this->_oldValues[$field] !== $this->{$field});
    }
    
/*    function getErrors($key = false) {
        if ($this->_bound && !$this->_checked) {
            if (!($checkResult = $this->check())) {
                // $this->_errors['_error'] = 'Check() failed';
            }
            $this->_checked = true;
        }
        if ($this->_error) $this->_errors['_error'] = $this->_error;
        $res = $this->_errors;
        if ($key !== false) {
            if (isset($res[$key])) $res = $res[$key];
                else $res = false;
        }
        return $res;
    }
*/
    function getCommonErrors() {
        $res = array();
        if (is_array($this->_errors)) {
            foreach ($this->_errors as $k => $e) {
                if (is_numeric($k)) $res[$k] = $e;
            }
        }
        return $res;
    }
    
    function getError() {
        if ($errors = $this->getErrors()) return Ac_Util::implode_r(";\n", $errors);
        if ($this->_error) return $this->_error;
    }
    
    function reset() {
        $vars = get_class_vars(get_class($this));
        foreach ($this->listOwnProperties() as $propName) if (isset($vars[$propName])) $this->$propName = $vars[$propName];
        $m = $this->getMapper();
        $this->setDefaultFields();
        $m->memorize($this);
        $this->_origPk = null;
    }
    
    function listProperties() {
        
        $res = array();
        foreach (array_keys(get_class_vars(get_class($this))) as $v) {
            if ($v{0} !== '_') $res[] = $v; 
        }
        return $res;
        
        //return $this->listOwnFields();
    }

    function _listOwnPublicVars() {
        $res = array();
        foreach (Ac_Util::getPublicVars($this) as $f => $v) if ($f{0} !== '_') $res[] = $f;
        return $res;
    }
    
    function _listSavedMembers() {
        $res = array_intersect($this->_listOwnPublicVars(), $this->getMapper()->getColumnNames());
        return $res;
    }
    
    function listPublicProperties() {
        return $this->_listOwnPublicVars();
    }
    
    function makeHtmlSafe($quote_style=ENT_QUOTES, $exclude_keys='') {
        foreach (get_object_vars( $this ) as $k => $v) {
                if (is_array( $v ) || is_object( $v ) || $v == NULL || substr( $k, 1, 1 ) == '_' ) {
                        continue;
                }
                if (is_string( $exclude_keys ) && $k == $exclude_keys) {
                        continue;
                } else if (is_array( $exclude_keys ) && in_array( $k, $exclude_keys )) {
                        continue;
                }
                $this->$k = htmlspecialchars( $v, $quote_style );
        }
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
     * @param bool $returnFalseFields Whether to return field values that are FALSE (weren't initialized) or not
     * @return array ($fieldName => $fieldValue)
     */
    function getDataFields($fieldNames = false, $returnFalseFields = true) {
        if ($fieldNames === false) $fieldNames = $this->_listOwnPublicVars();
        elseif (!is_array($fieldNames)) $fieldNames = array($fieldNames);
        $res = array();
        if ($returnFalseFields)
            foreach ($fieldNames as $fn) $res[$fn] = $this->$fn;
        else 
           foreach ($fieldNames as $fn) if (($this->$fn !== false) && !is_null($this->$fn)) $res[$fn] = $this->$fn;
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
    
    function _storesFalseValues() {
        return true;
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
                    foreach ($this->_listOwnPublicVars() as $v) {
                        if (!$this->isChanged($v)) $this->$v = $rec->$v;
                    }
                } else {
                    
                    // we have to rely on object_vars of prototype record instead of class_vars since onCreate() method can alter default values
                    $defaultVars = get_object_vars($m->getPrototype());
                    
                    foreach ($this->_listOwnPublicVars() as $v) {
                        if ($this->$v === $defaultVars[$v]) $this->$v = $rec->$v;
                    }
                }
            }
            if ($this->isPersistent()) $m->forget($this);
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
    function _storeUpstandingRecords() {
    }
    
    /**
     * Saves records that are referencing current record and are linked to it in-memory
     */
    function _storeDownstandingRecords() {
    }
    
    function _storeNNRecords() {
    }
    
    /**
     * Stores upstanding records and populates this record foreign keys.
     */
    function _autoStoreUpstanding($recordOrRecords, $fieldLinks, $errorKey) {
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
     * Populates downstanding records' foreign keys from this record keys and stores them
     */
    function _autoStoreDownstanding($recordOrRecords, $fieldLinks, $errorKey) {
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
    
    function _autoStoreNNRecords(& $recordOrRecords, $ids, $fieldLinks, $fieldLinks2, $midTableName, $errorKey) {
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
                    $ids[] = $rc;
                }
            }
        }
        if ($res && is_array($ids)) {
            $ids = array_unique($ids); //TODO: check why sometimes we receive duplicate IDs...
            $rows = array();
            $rowProto = array();
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
            $this->mapper->peReplaceNNRecords($rowProto, $rows, $midTableName, $errors);
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
        $copy = $m->factory();
        $flds = array_diff($this->_listOwnPublicVars(), $this->doListNonCopiedFields());
        if (!$asReference && !$withPk) $flds = array_diff($flds, $m->listPkFields());
        if ($withPk) $flds = array_merge($flds, $m->listPkFields());
        foreach ($flds as $v) {
            $copy->$v = $this->$v;
        }
        //$copy->_isReference = $this->_isReference;
        $copy->_setIsReference(is_null($asReference)? $this->isReference() : $asReference);
        $this->doOnCopy($copy);
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
            foreach ($fields as $k) {
                $v = $srcFields[$k];
                if (!array_key_exists($k, $destFields) || ($destFields[$k] != $v)) {
                    $res = false;
                    break; 
                }
            }
            if ($assocList === false) $assocList = $this->doListDefaultComparedAssociations();
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
            }
        }
        $this->_isBeingCompared = false;
        return $res;
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
        
        //var_dump($a1, $a2);
        
        //var_dump($a1, $a2);
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

    function cleanupReferences($otherObject) {
        foreach (array_keys(get_object_vars($this)) as $k) {
            if (is_object($this->$k) && Ac_Util::sameObject($this->$k, $otherObject)) {
                $this->$k = null;
            } elseif (is_array($this->$k)) {
                $tmp = $this->$k;
                foreach (array_keys($tmp) as $kk) {
                    if (is_object($tmp[$kk]) && Ac_Util::sameObject($tmp[$kk], $otherObject)) {
                        unset($tmp[$kk]);
                    }
                }
                unset($tmp);
            }
        }
    }
    
    function cleanupMembers() {
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
    
    function setDefaultFields() {
        $cv = get_class_vars(get_class($this));
        $defs = $this->getMapper()->getDefaults();
        foreach ($defs as $k => $v) {
            if (array_key_exists($k, $cv)) {
                $v = $cv[$k];
            }
            $this->$k = $v;
        }
    }
    
//  function __destruct() {
//      var_dump(get_class($this).' # '.$this->id.' destroyed');
//  }
    
    
}

?>
