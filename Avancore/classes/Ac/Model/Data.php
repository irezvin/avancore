<?php

/**
 * TODO Consider moving all unified accessors into separate class (i.e. Ac_Model_Data_Accessor). Since during several requests they won't be used at all (it's not hard to imagine (c) Samsung)
 * TODO Another step: consider moving all mutation-related methods (bind, check.., set.., delete..) into other separate class (i.e. Ac_Model_Data_Mutator)
 * TODO If we will build web requests based on Ac_Model_Data, mutators will be used on each request. It's useful, it's unified... how fast it should be? 
 */

class Ac_Model_Data extends Ac_Mixin_WithEvents {
    
    /**
     * function onBind($data, $ignore)
     */
    const EVENT_ON_BIND = 'onBind';
    
    /**
     * function onCheck(& $errors)
     */
    const EVENT_ON_CHECK = 'onCheck';
    
    /**
     * function onListLists(& $lists)
     */
    const EVENT_ON_LIST_LISTS = 'onListLists';
    
    /**
     * function onListAssociations(& $associations)
     */
    const EVENT_ON_LIST_ASSOCIATIONS = 'onListAssociations';

    /**
     * function onListFields(& $fileds)
     */
    const EVENT_ON_LIST_PROPERTIES = 'onListProperties';
    
    /**
     * function onListAggregates(& $aggregates)
     */
    const EVENT_ON_LIST_AGGREGATES = 'onListAggregates';
    
    /**
     * function onGetPropertiesInfo(& $propertiesInfo)
     */
    const EVENT_ON_GET_PROPERTIES_INFO = 'onGetPropertiesInfo';
    
    /**
     * Don't cache any metadata (property lists, property info)
     */
    const META_CACHE_NONE = 0;
    
    /**
     * Cache property lists for all compatible instances, don't cache property info
     */
    const META_CACHE_STRUCTURE = 1;
    
    /**
     * Cache property lists and property info, but re-calculate property info 
     * every time when data fields change
     */
    const META_CACHE_BY_STATE = 2;
    
    /**
     * Cache both property lists and property info
     */
    const META_CACHE_ALL = 3;

    protected static $metaCache = array();
    
    /**
     * @var int How metadata is cached (one of META_CACHE_ consts)
     */
    protected $metaCacheMode = false;

    /**
     * @var string Which key is used to cache metadata
     */
    protected $metaClassId = false;
    
    var $_bound = false;
    
    var $_beingChecked = false;
    
    var $_checked = false;
    
    var $_errors = array();
    
    // +------ TEMPLATE METHODS - most should be overridden by developer of concrete class -----+  

    /**
     * Should return true if this instance has same metadata as other ones of the class. This function is used to do some optimizations of algorhythms
     * that involve processing of many instances of the same class (for example, database import).
     * @return bool
     */
    function hasUniformPropertiesInfo() {
        return false;
    }
    
    function hasToConvertTypesOnBind() {
        return false;
    }
    
    function hasToModifyOnCheck() {
        return true;
    }
    
    /**
     * @return array
     */
    protected function getOwnPropertiesInfo() {
        return array();
    }
    
    /**
     * Should be overridden in concrete class
     * @return array ('propName', 'propName2', ...)
     */
    protected function listOwnProperties() {
        return array();
    }
    
    /**
     * Should be overridden in concrete class
     * @return array ('assocName' => 'assocClass', 'assocName2' => 'assocClass2', ...)
     */
    protected function listOwnAssociations() {
        return array();
    }
    
    /**
     * Should be overridden in concrete class
     * @return array ('assocName', 'assocName2', ...)
     */
    protected function listOwnAggregates() {
        return array();
    }
    
    /**
     * Should be overridden in concrete class 
     * @return array ('propName' => 'pluralName', 'propName2' => 'pluralName2', ...)
     */
    protected function listOwnLists() {
        return array();
    }
    
    // +--- metadata cache control routines ----+

    function getMetaClassId() {
        return $this->metaClassId;
    }
    
    function setMetaClassId($metaClassId) {
        $this->metaClassId = $metaClassId;
    }
    
    function getMetaCacheMode() {
        return $this->metaCacheMode;
    }
    
    function setMetaCacheMode($metaCacheMode) {
        if (!in_array($metaCacheMode, array(self::META_CACHE_NONE, self::META_CACHE_ALL, 
            self::META_CACHE_STRUCTURE, self::META_CACHE_BY_STATE_C)))
            throw Ac_E_InvalidCall::outOfConst ('metaCacheMode', $metaCacheMode, 'META_', __CLASS__);
        $this->metaCacheMode = $metaCacheMode;
    }
    
    function __construct(array $prototype = array()) {
        $this->intAssignMetaCaching();
        parent::__construct($prototype);
    }
    
    protected function intAssignMetaCaching() {
        if ($this->metaClassId === false)
            $this->metaClassId = get_class($this);
        if ($this->metaClassId === 'Ac_Model_Data') $this->metaCacheMode = self::META_CACHE_NONE;
            else $this->metaCacheMode = 
                $this->hasUniformPropertiesInfo ()? self::META_CACHE_ALL : self::META_CACHE_STRUCTURE;
    }
    
    // +---- metadata-supplying public functions -----+

    final function getPropertiesInfo() {
        // TODO: implement META_CACHE_BY_STATE
        
        $c = $this->metaCacheMode == self::META_CACHE_ALL;
        
        if ($c && isset(self::$metaCache[$mc = $this->metaClassId])
            && isset(self::$metaCache[$mc][__FUNCTION__]))
            return self::$metaCache[$mc][__FUNCTION__];
        
        $res = $this->getOwnPropertiesInfo();
        
        $this->triggerEvent(self::EVENT_ON_GET_PROPERTIES_INFO, array(& $res));
        
        if ($c) self::$metaCache[$mc][__FUNCTION__] = $res;
        return $res;
    }
    
    /**
     * @return array ('propName', 'propName2', ...)
     */
    final function listProperties() {
        $c = $this->metaCacheMode > self::META_CACHE_NONE;
        
        if ($c && isset(self::$metaCache[$mc = $this->metaClassId])
            && isset(self::$metaCache[$mc][__FUNCTION__]))
            return self::$metaCache[$mc][__FUNCTION__];
        
        $res = $this->listOwnProperties();
        
        $this->triggerEvent(self::EVENT_ON_LIST_PROPERTIES, array(& $res));
        
        if ($c) self::$metaCache[$mc][__FUNCTION__] = $res;
        return $res;
    }
    
    /**
     * @return array ('assocName' => 'assocClass', 'assocName2' => 'assocClass2', ...)
     */
    final function listAssociations() {
        $c = $this->metaCacheMode > self::META_CACHE_NONE;
        
        if ($c && isset(self::$metaCache[$mc = $this->metaClassId])
            && isset(self::$metaCache[$mc][__FUNCTION__]))
            return self::$metaCache[$mc][__FUNCTION__];
        
        $res = $this->listOwnAssociations();
        
        $this->triggerEvent(self::EVENT_ON_LIST_ASSOCIATIONS, array(& $res));
        
        if ($c) self::$metaCache[$mc][__FUNCTION__] = $res;
        return $res;
    }
    
    /**
     * @return array ('assocName', 'assocName2', ...)
     */
    final function listAggregates() {
        $c = $this->metaCacheMode > self::META_CACHE_NONE;
        
        if ($c && isset(self::$metaCache[$mc = $this->metaClassId])
            && isset(self::$metaCache[$mc][__FUNCTION__]))
            return self::$metaCache[$mc][__FUNCTION__];
        
        $res = $this->listOwnAggregates();
        
        $this->triggerEvent(self::EVENT_ON_LIST_AGGREGATES, array(& $res));
        
        if ($c) self::$metaCache[$mc][__FUNCTION__] = $res;
        return $res;
    }
    
    /**
     * @return array ('propName' => 'pluralName', 'propName2' => 'pluralName2', ...)
     */
    final function listLists() {
        $c = $this->metaCacheMode > self::META_CACHE_NONE;
        
        if ($c && isset(self::$metaCache[$mc = $this->metaClassId])
            && isset(self::$metaCache[$mc][__FUNCTION__]))
            return self::$metaCache[$mc][__FUNCTION__];
        
        $res = $this->listOwnLists();
        
        $this->triggerEvent(self::EVENT_ON_LIST_LISTS, array(& $res));
        
        if ($c) self::$metaCache[$mc][__FUNCTION__] = $res;
        return $res;
    }
    
    /**
     * @return array
     */
    final function listFields($noLists = false) {
        $res = array_diff($this->listProperties(), array_keys($this->listAssociations()));
        if ($noLists) $res = array_diff($res, array_keys($this->listLists()));
        return $res;
    }
    
    // +------------------ SUPPLEMENTARY REFLECTION METHODS - are called by accessors ---------------+
    
    function _hasVar($varName) {
        if (isset($this->$varName)) return true;
        elseif (array_key_exists($varName, get_object_vars($this))) return true;
        else {
            if ($this->mixPropertyMap === false) $this->fillMixMaps();
            return isset($this->mixPropertyMap[$varName]);
        }
    }
    
    function _getPlural($propName) {
        $plurals = $this->listLists();
        if (isset($plurals[$propName])) return $plurals[$propName];
        return false;
    }
    
    function _getAssocClass($propName) {
        $assocClasses = $this->listAssociations();
        if (isset($assocClasses[$propName])) $res = $assocClasses[$propName];
            else $res = false;
        return $res;
    }
    
    function _getMethod($prefix, $suffix) {
        if (method_exists($this, $methodName = $prefix.$suffix)) return $methodName; 
        if ($this->hasMethod($methodName)) return $methodName;
        return false;
    }
    
    // +------------------------ CONVERSION & VALIDATION METHODS -------------------------+
    
    /**
     * @param array $src Source array to take values from
     * @param string|bool|array $ignore Names of keys to exclude from $src
     */
    function bind($src, $ignore = false) {
        if (!is_array($src)) trigger_error ('$src must be an array', E_USER_ERROR);
        if (!is_array($ignore) && strlen($ignore)) $ignore = explode(' ', $ignore);
        if ($ignore)
            foreach ($ignore as $ignorePath) if (strlen($ignorePath)) {
                Ac_Util::unsetArrayByPath($src, Ac_Util::pathToArray($ignorePath));
            }
        
        if ($ctob = $this->hasToConvertTypesOnBind()) $v = $this->_createValidator();
            else $v = false;

        $this->mustRevalidate();
            
        foreach ($this->listProperties() as $propName) { 
            if ($plural = $this->_getPlural($propName)) $srcKey = $plural;
                else $srcKey = $propName;
            
            if (isset($src[$srcKey]) || array_key_exists($srcKey, $src)) {
                $p = $this->getPropertyInfo($propName, true);
                if ($p->noBind) continue;
                if ($this->_getAssocClass($propName)) {
                    if ($plural = $this->_getPlural($propName)) {
                        $this->setListProperty($propName, array());
                        if (is_array($src[$srcKey])) {
                            foreach ($src[$srcKey] as $key => $value) if (is_array($value)) {
                                $assocObject = $this->createAssociable($propName);
                                $assocObject->bind($value);
                                $this->setAssoc($propName, $assocObject, $key);
                            }
                        }
                    } else {
                        if (in_array($propName, $this->listAggregates())) {
                            $assocObject = $this->getAssoc($propName);
                            $assocObject->bind($src[$propName]);
                        } else {
                            if ($this->hasAssoc($propName)) $this->deleteAssoc($propName);
                            if (is_array($src[$srcKey])) {
                                $assocObject = $this->createAssociable($propName);
                                $assocObject->bind($src[$propName]);
                                $this->setAssoc($propName, $assocObject);
                            } else {
                                $this->setAssoc($propName, $src[$srcKey]);
                            }
                        }
                    }
                } else {
                    $oldValue = $value = $src[$srcKey];
                    $formOptions = $p->toFormOptions();
                    if ($plural = $this->_getPlural($propName)) $formOptions['plural'] = $plural;
                    if ($v && (!isset($formOptions['autoValidate']) || !$formOptions['autoValidate'])) {
                        $value = $v->convertValue($value, false, $formOptions, null);
                    }
                    if (!is_null($value) || ($oldValue === $value)) $this->setField($propName, $value);
                }
            }
        }
        
        $this->_checked = false;
        
        $this->doOnBind($src, $ignore);
        
        trigger_event(self::EVENT_ON_BIND, array(& $src, $ignore));
        
        return true;
    }
    
    function doOnBind($src, $ignore = false) {
    }
    
    function check() {
        if (!$this->_beingChecked && !$this->_checked) {
            $this->_beingChecked = true;
            $this->_errors = array();
            $this->_checkOwnFields();
            $this->_checkOwnAssociations();
            // $this->_checkOverrideProperties(); // This function is not implemented yet 
            $this->doOnCheck();
            
            trigger_event(self::EVENT_ON_CHECK, array(& $this->_errors));
            
            $this->_beingChecked = false;
            $this->_checked = true;
        }
        $res = !$this->_errors;
        return $res;
    }
    
    function doOnCheck() {
    }
    
    function mustRevalidate() {
        $this->_bound = true;
        $this->_errors = array();
        $this->_checked = false;
    }
    
    function getError() {
        if ($errors = $this->getErrors()) return Ac_Util::implode_r(";\n", $errors);
        else return false;
    }
    
    function getErrors($propertyName = false, $concat = false, $forceCheck = true) {
        if (!$this->_checked && !$this->_beingChecked) {
            if ($forceCheck || $this->_bound) $this->check();
        }
        if ($this->_errors) {
            if ($propertyName) {
                $res = Ac_Util::getArrayByPath($this->_errors, Ac_Util::pathToArray($propertyName));
                if (($concat !== false) && is_array($res)) $res = Ac_Util::implode_r($concat, $res);
            } else {
                if ($concat === false) $res = $this->_errors;
                else {
                    $res = Ac_Util::implode_r($concat, $this->_errors);
                }
            }
        } else $res = false;
        return $res;
    }
    
    function isBound() {
        return $this->_bound; 
    }
    
    function isChecked() {
        return $this->_checked;
    }
    
    function _checkOwnFields() {
        $val = $this->_createValidator();
        $fieldsToCheck = false;
        $val->fieldList = array();
        foreach ($this->listFields() as $propName) {
            if ($this->_getPlural($propName) && $keys = $this->listProperty($propName)) {
                $fieldsToCheck = array_merge($fieldsToCheck, Ac_Util::concatManyPaths($propName, $keys));
            } else $fieldsToCheck[] = $propName;
        }
        if ($fieldsToCheck) {
            $val->fieldList = $fieldsToCheck;
            $val->check($this->hasToModifyOnCheck());
            $this->_mergeErrors($val->errors);
        }
        $f = false;
        $val->model = $f;
    }
    
    function _checkOwnAssociations() {
        foreach ($this->listAssociations() as $propName => $assocClass) {
            $pi = $this->getPropertyInfo($propName, true);
            if ($pi->loadToCheck || $this->isAssocLoaded($propName)) {
                if ($pi->plural) {
                    foreach ($this->listProperty($propName) as $key) {
                        if ($pi->loadToCheck || $this->isAssocLoaded($propName, $key)) {
                            $assocObject = $this->getAssoc($propName, $key);
                            if (is_a($assocObject, 'Ac_Model_Data')) {
                                if (!$assocObject->_beingChecked && ($pi->checkIfUnbound || $assocObject->_bound)) { 
                                    $assocObject->check();
                                    $this->_mergeErrors($assocObject->_errors, $propName, $key);
                                }
                            } else {
                            }
                        }
                    }
                } else {
                    $assocObject = $this->getAssoc($propName);
                    if (!is_null($assocObject))
                    if (!is_null($assocObject) && !$assocObject->_beingChecked && ($pi->checkIfUnbound || $assocObject->_bound)) {
                        $assocObject->check();
                        $this->_mergeErrors($assocObject->_errors, $propName, '');
                    }
                }
            }
        }
    }
    
    /**
     * @return Ac_Model_Validator
     * @access protected
     */
    function _createValidator() {
        $res = new Ac_Model_Validator($this);
        return $res;
    }
    
    /**
     * @access private
     */
    protected static function intSetErrorByPathCallback ($currPath, $element, $value) {
        $element = array($element);    
    }
    
    /**
     * @access protected
     */
    function _mergeErrors($extraErrors, $propName = false, $key = false) {
        if (is_array($extraErrors)) {
            $prefix = Ac_Util::concatPaths($propName, $key);
            foreach ($extraErrors as $path => $errors) {
                $destPath = Ac_Util::concatPaths($prefix, $path);
                Ac_Util::setArrayByPath($this->_errors, Ac_Util::pathToArray($destPath), $errors, 
                    array(__CLASS__, 'intSetErrorByPathCallback'));
            }
        }
    }
    
    
    // +----------------------------------------    --------------------------------------+
    // +--------------------------------  ACCESSOR METHODS  ------------------------------+
    // +----------------------------------------    --------------------------------------+

    
    // +-------------------------------------  getField  ---------------------------------+
    
    function getField($propName, $key = false) {
        
        //return $this->_getSingleFieldItem($propName);
        list($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        if ($assocClass = $this->_getAssocClass($head)) { 
            if ($tail) {
                $res = $this->_getAssociatedField($head, $tail, $key);
            }
                else trigger_error ('Cannot retrieve associated object '.get_class($this).'::'.$propName.' with getField(); use getAssoc() instead', E_USER_ERROR);
        } else {
            if ($plural = $this->_getPlural($head)) {
                if ($tail && $key === false) {
                    list($tail, $key) = Ac_Util::pathBodyTail($tail);
                }
                if ($tail) trigger_error ('Excess path segments provided to retrieve list field '.get_class($this).'::'.$propName, E_USER_ERROR);
                $res = $key? $this->_getListFieldItem($head, $key, $plural) : $this->_getListFieldItems($head, $plural);
            } else {
                if ($tail || $key !== false) trigger_error ('Excess path segments provided to retrieve field '.get_class($this).'::'.$propName, E_USER_ERROR);
                return $this->_getSingleFieldItem($head);
            }
        }
        return $res;
    }
    
    /**
     * @access private
     */
    function _getSingleFieldItem($head) {
        
        //if ($this->_hasVar($head)) return $this->$head;
        //elseif ($g = $this->_getMethod('get', $head)) return $this->$g();
        
        if ($g = $this->_getMethod('get', $head)) return $this->$g();
        elseif (isset($this->$head) || $this->_hasVar($head)) return $this->$head;
        
        trigger_error ('Cannot retrieve field '.get_class($this).'::'.$head.' - consider implementing get<Prop>() method', E_USER_ERROR);
    }
    
    /**
     * @access private
     */
    function _getListFieldItems($head, $plural) {
        if ($m = $this->_getMethod('get', $head.'Items')) $res = $this->$m();
        elseif ($m = $this->_getMethod('get', $head)) $res = $this->$m();
        elseif (isset($this->$plural) || $this->_hasVar($plural) && is_array($this->$plural)) $res = $this->$plural;
        else trigger_error ('Cannot retrieve list field '.get_class($this).'::'.$head.' - consider implementing get<Prop>Items() method', E_USER_ERROR);
        return $res;         
    }
    
    /**
     * @access private
     */
    function _getListFieldItem ($head, $key, $plural) {
        if (!in_array($key, $this->_listOwnProperty($head, $plural))) 
            trigger_error ('Non-existing key supplied for list field '.get_class($this).'::'.$head, E_USER_ERROR);
        
        if ($m = $this->_getMethod('get', $head.'Item')) $res = $this->$m($key);
        elseif (($m = $this->_getMethod('get', $head.'Items')) || ($m = $this->_getMethod('get', $head))) {
            $tmp = $this->$m();
            $res = $tmp[$key];
        } 
        elseif((isset($this->$plural) || $this->_hasVar($plural)) && is_array($this->$plural)) {
            $res = $this->{$plural}[$key]; 
        }
        return $res;
    }
    
    /**
     * @access private
     */
    function _getAssociatedField($head, $tail, $key = false) {
        $value = null;
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        if (!$target) $value = null; else {
            $value = $target->getField($tail, $key);
        }
        return $value;
    }
    
    // +----------------------------------- listProperty ---------------------------------+
    
    function listProperty($propName) {
        list ($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        if ($assocClass = $this->_getAssocClass($head)) { 
            if ($tail) $res = $this->_listAssociatedProperty($head, $tail);
                else {
                    if ($plural = $this->_getPlural($head)) {
                        $res = $this->_listOwnProperty($head, $plural);
                    } else {
                        $res = false;
                    }
                }
        } else {
            if ($tail) trigger_error ('Excess path segments provided to retrieve list field\'s keys '.get_class($this).'::'.$propName, E_USER_ERROR);
            if ($plural = $this->_getPlural($head)) {
                $res = $this->_listOwnProperty($head, $plural);
            } else $res = false;
        }
        return $res;
    }
    
    /**
     * @access private
     */
    function _listAssociatedProperty($head, $tail) {
        $res = array();
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key is not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        //$target = $this->getAssoc($head, $subKey);
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        $value = $target->listProperty($tail);
        return $value;
    }
    
    /**
     * @access private
     */
    function _listOwnProperty($head, $plural) {
        if ($m = $this->_getMethod('list', $head.'Items')) $res = $this->$m();
        elseif ($m = $this->_getMethod('list', $plural)) {
            $res = $this->$m();
        }
        elseif (($m = $this->_getMethod('get', $head.'Items')) || ($m = $this->_getMethod('get', $head))) {
            $res = array_keys($this->$m());
        } 
        elseif (isset($this->$plural) || $this->_hasVar($plural)) {
            if (is_array($this->$plural)) {
                $res = array_keys($this->$plural);
            } elseif (($this->$plural === false) && ($assocClass = $this->_getAssocClass($head))) {
                if (($m = $this->_getMethod('load', $head.'Items')) || ($m = $this->_getMethod('load', $head))) {
                    $this->$m();
                    if (is_array($this->$plural)) $res = array_keys($this->$plural);
                        else trigger_error ('Cannot retrieve keys list for '.get_class($this).'::'.$head.' - consider implementing list/get/getItems/load/loadItems method(s) or create corresponding array variable', E_USER_ERROR);
                } else {
                    trigger_error ('Cannot retrieve keys list for '.get_class($this).'::'.$head.' - consider implementing list/get/getItems/load/loadItems method(s) or create corresponding array variable', E_USER_ERROR);
                }
            } else {
                trigger_error ('Cannot retrieve keys list for '.get_class($this).'::'.$head.' - consider implementing list/get/getItems/load/loadItems method(s) or create corresponding array variable', E_USER_ERROR); 
            }
        }
        else {
            trigger_error ('Cannot retrieve keys list for '.get_class($this).'::'.$head.' - consider implementing list/get/getItems method(s) or create corresponding array variable', E_USER_ERROR);
        }
        return $res;
    }
    
    // +--------------------------------- countProperty ----------------------------------+
    
    function countProperty ($propName) {
        list ($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        $assocClass = $this->_getAssocClass($head);
        if ($assocClass && $tail) $res = $this->_countAssociatedProperty($head, $tail);
        else {
            $plural = $this->_getPlural($propName);
            if (!$plural) {
                if ($assocClass) $res = $this->hasAssoc($propName)? 1 : 0;
                    else $res = 1;
            } else {
                if ($m = $this->_getMethod('count', $propName)) {
                    $res = $this->$m();
                } else $res = count ($this->_listOwnProperty($head, $plural));
            } 
        } 
        return $res;
    }
    
    /**
     * @access private
     */
    function _countAssociatedProperty($head, $tail) {
        $count = 0;
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key is not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        //$target = $this->getAssoc($head, $subKey);
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        $value = $target->countProperty($tail);
        return $value;
    }
    
    // +----------------------------------- hasAssoc -------------------------------------+

    function hasAssoc ($propName, $key = false) {
        list ($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        $assocClass = $this->_getAssocClass($head);
        if ($assocClass && $tail) {
            if (($plural = $this->_getPlural($head)) && !strlen($key)) {
                list ($newTail, $newKey) = Ac_Util::pathBodyTail($tail);
                if ($newTail) $res = $this->_hasAssociatedAssoc($head, $tail, $key);
                    else $res = $this->_hasOwnAssoc($head, $newKey);
            } else $res = $this->_hasAssociatedAssoc($head, $tail, $key);
        } else {
            if (!$assocClass) trigger_error ('hasAssoc() called for non-associated property '.get_class($this).'::'.$propName, E_USER_ERROR);
            $res = $this->_hasOwnAssoc($head, $key);
        } 
        return $res;
    }
    
    /**
     * @access private
     */
    function _hasAssociatedAssoc($head, $tail, $key) {
        $has = false;
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        //$target = $this->getAssoc($head, $subKey);
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        $has = $target->hasAssoc($tail, $key);
        return $has;
    }
    
    /**
     * @access private
     */
    function _hasOwnAssoc($head, $key) {
        $plural = $this->_getPlural($head);
        if ($plural) {
             if (strlen($key)) { 
                 if (($m = $this->_getMethod('has', $head.'Item')) || ($m = $this->_getMethod('has', $head))) $res = $this->$m($key);
                 else $res = in_array($key, $this->listAssoc($head));
             } else {
                 if (($m = $this->_getMethod('has', $head.'Items')) || ($m = $this->_getMethod('has', $head))) $res = $this->$m();
                 else $res = $this->countProperty($head) > 0;
             }
        } else {
            if (strlen($key)) trigger_error ('List key specified for non-list association '.get_class($this).'::'.$head, E_USER_ERROR);
            if ($m = $this->_getMethod('has', $head)) $res = $this->$m();
                else {
                    $assocObject = $this->_getOwnAssoc($head, $key, $this->_getPlural($head));
                    $res = !is_null($assocObject);
                }
        }
        return $res;
    }
    
    // +------------------------------- isAssocLoaded() ----------------------------------+

    /**
     * Important: if key is specified, this function doesn't check if object with corresponding key exists at all. It won't be considered loaded if it's not found in the memory.
     * Such behavior is different from other functions' one, since usually error is thrown when non-existing key is specified     
     */
    function isAssocLoaded ($propName, $key = false) {
        $s = '_'.$propName;
        if (isset($this->$s)) return $this->$s !== false;
        
        list ($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        $assocClass = $this->_getAssocClass($head);
        if ($assocClass && $tail) {
            if (($plural = $this->_getPlural($head)) && !strlen($key)) {
                list ($newTail, $newKey) = Ac_Util::pathBodyTail($tail);
                if ($newTail) $res = $this->_isAssociatedAssocLoaded($head, $tail, $key);
                    else $res = $this->_isOwnAssocLoaded($head, $newKey);
            } else $res = $this->_isAssociatedAssocLoaded($head, $tail, $key);
        } else {
            if (!$assocClass) trigger_error ('isAssocLoaded() called for non-associated property '.get_class($this).'::'.$propName, E_USER_ERROR);
            $res = $this->_isOwnAssocLoaded($head, $key);
        } 
        return $res;
    }
    
    /**
     * @access private
     */
    function _isAssociatedAssocLoaded($head, $tail, $key) {
        $has = false;
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        //$target = $this->getAssoc($head, $subKey);
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        $has = $target->isAssocLoaded($tail, $key);
        return $has;
    }
    
    /**
     * Note: when this function cannot determine that association is NOT loaded, it assumes it IS loaded, instead of triggering an error
     * @access private
     */
    function _isOwnAssocLoaded($head, $key) {
        $plural = $this->_getPlural($head);
        if ($plural) {
            $vn = '_'.$plural;
            if (strlen($key)) { 
                 if (($m = $this->_getMethod('is', $head.'ItemLoaded'))) $res = $this->$m($key);
                 else {
                     if (isset($this->$vn) || $this->_hasVar($vn)) {
                         if (!is_array($this->$vn)) $res = false;
                         elseif (!isset($this->{$vn}[$key])) $res = true; // Not exists does not mean "not loaded"
                         elseif (is_object($this->{$vn}[$key])) $res = true;
                         else $res = false;
                     } else $res = true;
                 }
             } else {
                 if (($m = $this->_getMethod('are', $head.'ItemsLoaded'))) $res = $this->$m();
                 else {
                     if (isset($this->$vn) || $this->_hasVar($vn)) {
                         $res = is_array($this->$vn);
                     } else $res = true;
                 }
             }
        } else {
            $vn = '_'.$head;
            if (strlen($key)) trigger_error ('List key specified for non-list association '.get_class($this).'::'.$head, E_USER_ERROR);
            if ($m = $this->_getMethod('is', $head.'Loaded')) $res = $this->$m();
            elseif (isset($this->$vn) || $this->_hasVar($vn)) $res = (is_null($this->$vn) || is_object($this->$vn));
            else trigger_error ('Cannot check if assoc is loaded: '.get_class($this).'::'.$head, E_USER_ERROR);
        }
        return $res;
    }
    
    // +-------------------------------- setListProperty ---------------------------------+

    /**
     * This function always causes all elements of list to be deleted, and then items are added from the list() array
     */
    function setListProperty ($propName, $list = array()) {
        list ($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        if (!is_array($list)) trigger_error('Array must be specified for setListProperty() of '.get_class($this).'::'.$propName, E_USER_ERROR);
        if ($assocClass = $this->_getAssocClass($head)) { 
            if ($tail) $res = $this->_setAssociatedListProperty($head, $tail, $list);
                else {
                    if ($plural = $this->_getPlural($head)) {
                        $res = $this->_setOwnAssocList($head, $plural, $list);
                    } else {
                        trigger_error ('setListProperty() called for non-list property '.get_class($this).'::'.$propName, E_USER_ERROR);
                    }
                }
        } else {
            if ($tail) trigger_error ('Excess path segments provided to retrieve list field\'s keys '.get_class($this).'::'.$propName, E_USER_ERROR);
            if ($plural = $this->_getPlural($head)) {
                $res = $this->_setOwnFieldList($head, $plural, $list);
            } else trigger_error ('setListProperty() called for non-list property '.get_class($this).'::'.$propName, E_USER_ERROR);
        }
        return $res;
    }
     
    /**
     * @access private
     */
    function _setAssociatedListProperty($head, $tail, $list) {
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key is not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        //$target = $this->getAssoc($head, $subKey);
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        $value = $target->setListProperty($tail, $list);
    }
    
    /**
     * @access private
     */
    function _setOwnAssocList($head, $plural, $list) {
        if (!count($list) && ($m = $this->_getMethod('delete', $head.'Items'))) $this->$m();
        elseif ($m = $this->_getMethod('set', $head.'Items')) {
            $this->$m($list);
        } else {
            foreach ($this->listProperty($head) as $key) $this->_deleteOwnAssoc($head, $key, $plural);
            foreach (array_keys($list) as $key) {
                $item = $list[$key];
                $this->_setOwnAssocItem($head, $plural, $key, $item);
            }
        }
    }
    
    /**
     * @access private
     */
    function _setOwnFieldList($head, $plural, $list) {
        if (!count($list) && ($m = $this->_getMethod('delete', $head.'Items'))) $this->$m();
        elseif ($m = $this->_getMethod('set', $head.'Items')) {
            $this->$m($list);
        } else {
            foreach ($this->listProperty($head) as $key) $this->_deleteOwnField($propName, $key);
            foreach (array_keys($list) as $key) {
                $item = $list[$key];
                $this->_setOwnListFieldItem($head, $key, $plural, $item);
            }
        }
    }
    
    // +----------------------------------- deletePropItem--------------------------------+
    
    function deletePropItem($propName, $key) {
        if (!strlen($key)) trigger_error ('Valid array key $key must be provided for deletePropItem()', E_USER_ERROR);
        list ($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        $assocClass = $this->_getAssocClass($head);
        if ($assocClass) {
            if ($tail) {
                if (($plural = $this->_getPlural($head)) && !strlen($key)) {
                    list ($newTail, $newKey) = Ac_Util::pathBodyTail($tail);
                    if ($newTail) $res = $this->_deleteAssociatedPropItem($head, $tail, $key);
                        else $res = $this->_deleteOwnAssoc($head, $newKey);
                } else $res = $this->_deleteAssociatedPropItem($head, $tail, $key);
            } else {
                $res = $this->_deleteOwnAssoc($head, $key);
            }
        } else {
            if ($tail) trigger_error ('Excess path segments provided to delete list field '.get_class($this).'::'.$propName, E_USER_ERROR);
            if (!($plural = $this->_getPlural($head))) trigger_error ('deletePropItem() called for non-list property '.get_class($this).'::'.$propName, E_USER_ERROR);
            $this->_deleteOwnListFieldItem($head, $key, $plural);
        }
        return $res;
    }
    
    function _deleteAssociatedPropItem($head, $tail, $key) {
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        //$target = $this->getAssoc($head, $subKey);
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        $res = $target->deletePropItem($tail, $key);
        return $res;
    }
    
    function _deleteOwnListFieldItem($head, $key, $plural) {
        if (!in_array($key, $this->_listOwnProperty($head))) trigger_error ('Attempt to delete non-existing list item '.get_class($this).'::'.$head, E_USER_ERROR);
        if (($m = $this->_getMethod('delete', $head.'Item')) || ($m = $this->_getMethod('delete', $head))) {
            $res = $this->$m($key);
        } elseif ((isset($this->$plural) || $this->_hasVar($plural)) && is_array($this->$plural) && isset($this->{$plural}[$key])) {
            unset($this->{$plural}[$key]);
        } elseif (is_array($items = $this->_getListFieldItems($head, $plural)) && isset($items[$key])) {
            unset($items[$key]);
            $this->_setOwnListFieldItems($head, $plural, $items);
        } else {
            trigger_error ('Cannot delete list field item '.get_class($this).'::'.$head.'; consider implementing delete/get/set methods or check accessor methods consistency', E_USER_ERROR);
        }
    }
    
    // +----------------------------------- deleteAssoc ----------------------------------+

    function deleteAssoc($propName, $key = false) {
        list ($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        $assocClass = $this->_getAssocClass($head);
        if ($assocClass) {
            if ($tail) {
                if (($plural = $this->_getPlural($head)) && !strlen($key)) {
                    list ($newTail, $newKey) = Ac_Util::pathBodyTail($tail);
                    if ($newTail) $res = $this->_deleteAssociatedAssoc($head, $tail, $key);
                        else $res = $this->_deleteOwnAssoc($head, $newKey);
                } else $res = $this->_deleteAssociatedAssoc($head, $tail, $key);
            } else {
                if ($plural = $this->_getPlural($head)) {
                    if (!strlen($key)) $this->setListProperty($head, array());
                        else {
                            if (!$this->_hasOwnAssoc($head, $key)) trigger_error ('Wrong key specified for deleteAssoc() for '.get_class($this).'::'.$propName, E_USER_ERROR);
                            $res = $this->_deleteOwnAssoc($head, $key, $plural);
                        }
                } else {
                    if (strlen($key)) trigger_error ('Key must not be specified for deleteAssoc() of non-list association '.get_class($this).'::'.$propName, E_USER_ERROR);
                    $res = $this->_deleteOwnAssoc($head, $key, $plural);
                }
            }
        } else {
            trigger_error ('deleteAssoc() called for non-association property '.get_class($this).'::'.$propName, E_USER_ERROR);
        }
        $this->mustRevalidate();
        return $res;
    }
    
    function notifyDissociated($assocObject, $assocName, $hisKey) {
        $assocObject->handleDissociated($this, $assocName, $hisKey);
    }
    
    function handleDissociated($dissocObject, $assocName, $myKey) {
    }
    
    function _deleteAssociatedAssoc($head, $tail, $key) {
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key is not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        //$target = $this->getAssoc($head, $subKey);
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        $target->deleteAssoc($tail, $key);
    }
    
    function _deleteOwnAssoc($head, $key, $plural) {
        if (strlen($key)) {
            if (($m = $this->_getMethod('delete', $head.'Item')) || ($m = $this->_getMethod('delete', $head))) {
                $res = $this->$m($key);
            } else {
                if (!$this->_isOwnAssocLoaded($head, $key)) $dissocObject = $this->_getOwnAssoc($head, $key, $plural);
                if ((isset($this->$plural) || $this->_hasVar($plural)) && is_array($this->$plural) && isset($this->{$plural}[$key])) {
                    if (is_object($this->{$plural}[$key])) $dissocObject = $this->{$plural}[$key];
                        else $dissocObject = $this->_getOwnAssoc($head, $key, $plural);
                    $this->notifyDissociated($dissocObject, $head, $key);
                    unset($this->{$plural}[$key]);
                } elseif (is_array($items = $this->_getOwnAssocList($head, $plural)) && isset($items[$key])) {
                    unset($items[$key]);
                    $this->_setOwnAssocList($head, $plural, $items);
                } else {
                    trigger_error ('Cannot delete list association item '.get_class($this).'::'.$head.'; consider implementing delete/get/set methods or check accessor methods consistency', E_USER_ERROR);
                }
            }
        } else {
            if ($m = $this->_getMethod('delete', $head)) {
                $this->$m();
            } elseif ($m = $this->_getMethod('clear', $head)) {
                $this->$m();
            } elseif ($m = $this->_getMethod('set', $head)) {
                $tmp = null;
                $this->$m($tmp);
            } elseif (isset($this->$head) || $this->_hasVar($head)) {
                if (!is_object($this->$head)) $this->_getOwnAssoc($head, false, false);
                if (is_null($this->$head)) trigger_error ('Cannot delete associated object since it\'s already NULL - '.get_class($this).'::'.$head, E_USER_ERROR);
                if (is_object($this->$head)) {
                    $this->notifyDissociated($this->$head, $head, false);
                    $this->$head = null;
                }
            }
        }
    }
    
    // +----------------------------------- setField -------------------------------------+
    
    function setField($propName, $value, $key = false) {
        list ($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        $assocClass = $this->_getAssocClass($head);
        if ($assocClass) {
            if ($tail) {
                if (($plural = $this->_getPlural($head)) && !strlen($key)) {
                    list ($newTail, $newKey) = Ac_Util::pathBodyTail($tail);
                    if ($newTail) $res = $this->_setAssociatedField($head, $tail, $key, $value);
                        else trigger_error('setField() called for association property '.get_class($this).'::'.$propName, E_USER_ERROR);
                } else $res = $this->_setAssociatedField($head, $tail, $key, $value);
            } else {
                trigger_error('setField() called for association property '.get_class($this).'::'.$propName, E_USER_ERROR);
            }
        } else {
            if (!strlen($key)) list($tail, $key) = Ac_Util::pathBodyTail($tail);
            if ($tail) trigger_error ('Excess path segments provided for setField() of '.get_class($this).'::'.$propName, E_USER_ERROR);
            if ($plural = $this->_getPlural($head)) {
                if (strlen($key)) $this->_setOwnListFieldItem($head, $key, $plural, $value);
                    else {
                        if (!is_array($value)) trigger_error ('Array must be specified to set items of list field '.get_class($this).'::'.$propName, E_USER_ERROR);
                        $this->_setOwnFieldList($head, $plural, $value);
                    }
            } else {
                if (strlen($key)) trigger_error ('Key specified for non-list field property '.get_class($this).'::'.$propName, E_USER_ERROR);
                $this->_setOwnSingleFieldItem($head, $value);
            }
        }
        $this->mustRevalidate();
    }

    /**
     * @access private
     */
    function _setOwnSingleFieldItem($head, $value) {
        if ($m = $this->_getMethod('set', $head)) $this->$m($value);
        elseif (isset($this->$head) || $this->_hasVar($head)) $this->$head = $value;
        else trigger_error ('Cannot set property value of '.get_class($this).'::'.$head.' - consider implementing set.. method', E_USER_ERROR);
    }
    
    /**
     * @access private
     */
    function _setOwnListFieldItem($head, $key, $plural, $value) {
        if (strlen($key) && ($m = $this->_getMethod('set', $head.'Item'))) $this->$m($key, $value);
        elseif (!strlen($key) && ($m = $this->_getMethod('set', $head.'Items'))) $this->$m($key, $value);
        elseif ($m = $this->_getMethod('set', $head)) $this->$m($value, $key);
        elseif ((isset($this->$plural) || $this->_hasVar($plural)) && is_array($this->$plural)) $this->{$plural}[$key] = $value;
        else trigger_error ('Cannot set value of list property '.get_class($this).'::'.$head, E_USER_ERROR);
    }
    
    /**
     * @access private
     */
    function _setAssociatedField($head, $tail, $key, $value) {
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key is not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        //$target = $this->getAssoc($head, $subKey);
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        $value = $target->setField($tail, $value, $key);
    }
    
    // +----------------------------------- getAssoc -------------------------------------+
    
    /**
     * @return Ac_Model_Data
     */
    function getAssoc($propName, $key = false) {
        list ($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        $assocClass = $this->_getAssocClass($head);
        if ($assocClass) {
            if ($tail) {
                if (($plural = $this->_getPlural($head)) && !strlen($key)) {
                    list ($newTail, $newKey) = Ac_Util::pathBodyTail($tail);
                    if (strlen($newTail)) $res = $this->_getAssociatedAssoc($head, $tail, $key);
                        else $res = $this->_getOwnAssoc($head, $newKey, $plural);
                } else $res = $this->_getAssociatedAssoc($head, $tail, $key);
            } else {
                if ($plural = $this->_getPlural($head)) {
                    if (!strlen($key)) $res = $this->_getOwnAssocList($head, $plural);
                        else {
                            if (!$this->_hasOwnAssoc($head, $key)) trigger_error ('Wrong key specified for getAssoc() for '.get_class($this).'::'.$propName, E_USER_ERROR);
                            $res = $this->_getOwnAssoc($head, $key, $plural);
                        }
                } else {
                    if (strlen($key)) trigger_error ('Key must not be specified for deleteAssoc() of non-list association '.get_class($this).'::'.$propName, E_USER_ERROR);
                    $res = $this->_getOwnAssoc($head, $key, $plural);
                }
            }
        } else {
            trigger_error ('Excess deleteAssoc() called for non-association property '.get_class($this).'::'.$propName, E_USER_ERROR);
        }
        return $res;
    }
    
    /**
     * @return Ac_Model_Data
     */
    function _getAssociatedAssoc($head, $tail, $key) {
        $res = null;
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key is not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        //$target = $this->getAssoc($head, $subKey);
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        $res = $target->getAssoc($tail, $key);
        return $res;
    }
    
    function _getOwnAssocList($head, $plural) {
        if ($m = $this->_getMethod('get', $head.'Items') || $m = $this->_getMethod('get', $head)) $res = $this->$m();
        else {
            $res = array();
            foreach ($this->_listOwnProperty($head, $plural) as $key) $res[$key] = $this->_getOwnAssoc($head, $key, $plural);
        }
        return $res;
    }
    
    /**
     * @return Ac_Model_Data
     */
    function _getOwnAssoc($head, $key, $plural) {
        $vn = '_'.$plural;
        if (strlen($key)) {
            if (($m = $this->_getMethod('get', $head.'Item')) || ($m = $this->_getMethod('get', $head))) {
                $res = $this->$m($key);
            } else {
                if (!$this->_isOwnAssocLoaded($head, $key)) {
                    if (($m = $this->_getMethod('load', $head.'Item')) || ($m = $this->_getMethod('load', $head))) $this->$m($key);
                    elseif ($m = $this->_getMethod('load', $head.'Items')) $this->$m();
                    else trigger_error ('Cannot load item of associated list '.get_class($this).'::'.$head.'; consider implementing load/loadItem/loadItems method', E_USER_ERROR);
                }
                if ((isset($this->$vn) || $this->_hasVar($vn)) && is_array($this->$vn) && isset($this->{$vn}[$key]) && is_object($this->{$vn}[$key])) {
                    $res = $this->{$vn}[$key];
                } else {
                    trigger_error ('Cannot retrieve item of associated list '.get_class($this).'::'.$head.'; please check accessor methods for consistence', E_USER_ERROR);
                }
            }
        } else {
            if ($m = $this->_getMethod('get', $head)) {
                $res = $this->$m();
            } else {
                if (!$this->_isOwnAssocLoaded($head, $key)) {
                    if ($m = $this->_getMethod('load', $head)) $this->$m();
                        else {
                            trigger_error ('Cannot load associated property '.get_class($this).'::'.$head.'; consider implementing load.. method', E_USER_ERROR);
                        }
                }
                $vn = '_'.$head;
                if ((isset($this->$vn) || $this->_hasVar($vn)) && (is_object($this->$vn) || is_null($this->$vn))) $res = $this->$vn;
                    else trigger_error ('Cannot retrieve associated object '.get_class($this).'::'.$head.' - consider implementing get.. method or corresponding member', E_USER_ERROR);
            }
        }
        return $res;
    }
    
    // +----------------------------------- setAssoc -------------------------------------+
    
    function setAssoc($propName, $assocObject, $key = false) {
        if (is_array($assocObject)) {
            if (strlen($key)) trigger_error ('Key must not be specified when setting multiple associated items of '.get_class($this).'::'.$propName, E_USER_ERROR);
            return $this->setListAssoc($propName, $assocObject);
        }
        if (is_null($assocObject)) return $this->deleteAssoc($propName, $key);
        if (!is_object($assocObject) && !is_null($assocObject) && ($assocObject !== false)) trigger_error ('Wrong assocObject provided for setAssoc() of '.get_class($this).'::'.$propName, E_USER_ERROR);
        list ($head, $tail) = Ac_Util::pathHeadTail($propName);
        if (!in_array($head, $this->listProperties())) trigger_error (get_class($this).' does not have property "'.$propName.'"', E_USER_ERROR);
        $assocClass = $this->_getAssocClass($head);
        if ($assocClass) {
            if ($tail) {
                if (($plural = $this->_getPlural($head)) && !strlen($key)) {
                    list ($newTail, $newKey) = Ac_Util::pathBodyTail($tail);
                    if ($newTail) $res = $this->_setAssociatedAssoc($head, $tail, $key, $assocObject);
                        else $res = $this->_setOwnAssocItem($head, $plural, $newKey, $assocObject);
                } else $res = $this->_setAssociatedAssoc($head, $tail, $key, $assocObject);
            } else {
                if ($plural = $this->_getPlural($head)) {
                    if (!strlen($key)) trigger_error ('Key must be specified when setting associated list item of '.get_class($this).'::'.$propName, E_USER_ERROR);
                    else {
                        $res = $this->_setOwnAssocItem($head, $plural, $key, $assocObject);
                    }
                } else {
                    if (strlen($key)) trigger_error ('Key must not be specified for setAssoc() of non-list association '.get_class($this).'::'.$propName, E_USER_ERROR);
                    $res = $this->_setOwnAssoc($head, $assocObject);
                }
            }
        } else {
            trigger_error ('setAssoc() called for non-association property '.get_class($this).'::'.$propName, E_USER_ERROR);
        }
        $this->mustRevalidate();
        return $res;
    }
    
    
    function notifyAssociated($assocObject, $assocName, $hisKey) {
        $assocObject->handleAssociated($this, $assocName, $hisKey);
    }
    
    function handleAssociated($dissocObject, $assocName, $myKey) {
    }
    
    /**
     * @access private
     */
    function _setAssociatedAssoc($head, $tail, $key, $assocObject) {
        if ($plural = $this->_getPlural($head)) {
            list($subKey, $tail) = Ac_Util::pathHeadTail($tail);
            if (!$tail) trigger_error ('List key is not specified for associated list property '.get_class($this).'::'.$head, E_USER_ERROR);
        } else {
            $subKey = false;
        }
        //$target = $this->getAssoc($head, $subKey);
        $target = $this->_getOwnAssoc($head, $subKey, $plural);
        $value = $target->setAssoc($tail, $assocObject, $key);
    }
    
    /**
     * @access private
     */
    function _setOwnAssocItem($head, $plural, $key, $assocObject) {
        if ($m = $this->_getMethod('set', $head.'Item')) return $this->$m($assocObject, $key);
        elseif ($m = $this->_getMethod('set', $head)) return $this->$m($assocObject, $key);
        else {
            if ($this->_hasOwnAssoc($head, $key)) $this->_deleteOwnAssoc($head, $key);
            if (isset($this->$plural) || $this->_hasVar($plural)) {
                if (!is_array($this->$plural)) {
                    if ($m = $this->_getMethod('load', $head.'Items')) $this->$m();
                    elseif ($this->_listOwnProperty($head, $plural));
                }
                if (!is_array($this->$plural)) trigger_error ('Cannot load associated list '.get_class($this).'::'.$head.' - consider implementing loadItems or get.. method', E_USER_ERROR);
                    else {
                        $this->{$plural}[$key] = $assocObject;
                        $this->notifyAssociated($assocObject, $head, $key);
                    }
            } else {
                trigger_error ('Cannot set associated list item '.get_class($this).'::'.$head.' - consider implementing setItem or set.. method, or provide corresponding member', E_USER_ERROR);
            }
        }
    }
    
    /**
     * @access private
     */
    function _setOwnAssoc($head, $assocObject) {
        if ($m = $this->_getMethod('set', $head)) return $this->$m($assocObject);
        else {
            if ($this->_hasOwnAssoc($head, false)) $this->_deleteOwnAssoc($head, false, false);
            if (isset($this->$head) || $this->_hasVar($head)) {
                if (!is_object($this->$head) && !is_null($this->$head)) {
                    if ($m = $this->_getMethod('load', $head)) $this->$m();
                    elseif ($this->_getOwnAssoc($head, false, false));
                }
                if (is_object($this->$head)) {
                    $this->notifyDissociated($this->$head, $head, false);
                } elseif (!is_null($this->$head)) trigger_error ('Cannot load associated object '.get_class($this).'::'.$head.' - consider implementing load.. or get.. method', E_USER_ERROR);
                $this->$head = $assocObject;
                $this->notifyAssociated($assocObject, $head, false);
            } else {
                trigger_error ('Cannot set associated object '.get_class($this).'::'.$head.' - consider implementing set.. method, or provide corresponding member', E_USER_ERROR);
            }
        }
    }
    
    // +--------------------------------- createAssociable -------------------------------+
    
    /**
     * @return Ac_Model_Data
     */
    function createAssociable($ownAssocName) {
        if (!($assocClass = $this->_getAssocClass($ownAssocName))) trigger_error('Unknown own association: '.$ownAssocName);
        $object = false;
        if (!$this->doCreateAssociable($ownAssocName, $assocClass, $object)) {
            $object = new $assocClass();
        }
        return $object;
    }
    
    function doCreateAssociable($ownAssocName, $assocClass, $object) {
    }
    
    // +----------------------------------- getPropertyInfo ------------------------------+
    
    /**
     * @param bool $onlyStatic Don't include runtime data such as property value and errors into property info 
     * @return Ac_Model_Property
     */
    function getPropertyInfo($propName, $onlyStatic = false) {
        if (substr($propName, -2) == '[]') 
            $propName = substr($propName, 0, strlen($propName)-2);
        $arrPropInfo = $this->_getStaticPropertyInfoArr(Ac_Util::pathToArray($propName));
        $arrPropInfo['srcClass'] = get_class($this);
        if (!$onlyStatic) {
            if (isset($arrPropInfo['isAbstract']) && $arrPropInfo['isAbstract']) 
                trigger_error ('Only static info can be retrieved on abstract property '.get_class($this).'::'.$propName);
            
            if (isset($arrPropInfo['assocClass']) && $arrPropInfo['assocClass']) $arrPropInfo['value'] = $this->getAssoc($propName);
                else $arrPropInfo['value'] = $this->getField($propName);
            if ($errors = $this->getErrors($propName, false, false)) $arrPropInfo['error'] = $errors;
        }
        $res = new Ac_Model_Property($this, $propName, $onlyStatic, $arrPropInfo);
        return $res; 
    }
    
    function hasProperty($propName) {
        return ($this->_getStaticPropertyInfoArr(Ac_Util::pathToArray($propName), false, false) !== false);
    }
    
    function _getStaticPropertyInfoArr ($arrPath, $abstract = false, $trigger = true) {
        $head = $arrPath[0];
        $arrTail = array_slice($arrPath, 1);
        if (!in_array($head, $this->listProperties())) {
            if ($trigger) {
                trigger_error (get_class($this).' does not have property "'.$head.'"', E_USER_ERROR);
            }
            else return false;
        }
        if ($c = count($arrTail)) {
            if (($c == 1) && ($plural = $this->_getPlural($head))) {
                if (!strlen($arrTail[0])) { // We have 'wildcard' here: foo[] - it is allowed as last segment
                    $res = $this->_getOwnStaticPropertyInfoArr($head, false, $plural, $abstract, $trigger);
                } else {
                    $res = $this->_getOwnStaticPropertyInfoArr($head, $arrTail[0], $plural, $abstract, $trigger);
                }
            } else {
                if (!strlen($arrTail[0])) {
                    if (!($plural = $this->_getPlural($head))) {
                        if ($trigger) trigger_error ('Empty path segment provided for non-list property info retrieval '.get_class($this).'::'.$head, E_USER_ERROR);
                            else return false;
                    }
                    else {
                        $arrTail = array_slice($arrTail, 1);
                        $res = $this->_getAssociatedStaticPropertyInfoArr($head, $arrTail, false, $plural, $abstract, $trigger);
                    }
                } else {
                    if ($plural = $this->_getPlural($head)) {
                        $key = $arrTail[0];
                        $arrTail = array_slice($arrTail, 1);
                        $res = $this->_getAssociatedStaticPropertyInfoArr($head, $arrTail, $key, $plural, $abstract, $trigger);
                    } else {
                        $res = $this->_getAssociatedStaticPropertyInfoArr($head, $arrTail, false, false, $abstract, $trigger);
                    }
                }
            }
        } else {
            $res = $this->_getOwnStaticPropertyInfoArr($head, false, false, $abstract, $trigger);
        }
        return $res;
    }
    
    function _getAssociatedStaticPropertyInfoArr($head, $arrTail, $key, $plural, $abstract, $trigger) {
        if ($plural) {
            if (strlen($key)) {
                if ($abstract) {
                    if ($trigger) trigger_error('Cannot use concrete key after empty path segment for '.get_class($this)."::{$head}[{$key}][".implode('][', $arrTail).']', E_USER_ERROR);
                    else return false;
                }
                $target = $this->_getOwnAssoc($head, $key, $plural);
            } else {
                $target = $this->createAssociable($head);
                $abstract = true;
            }
        } else {
             $target = $this->_getOwnAssoc($head, false, false);
             if (!$target) $target = $this->createAssociable($head);
        }
        $info = $target->_getStaticPropertyInfoArr($arrTail, $abstract);
        return $info;
    }
    
    function _getOwnStaticPropertyInfoArr($head, $key, $plural, $abstract, $trigger) {
        $opi = $this->getPropertiesInfo();
        if (isset($opi[$head]) && is_array($opi[$head])) $arrInfo = $opi[$head];
            else $arrInfo = array();
        
        if ($plural = $this->_getPlural($head)) $arrInfo['plural'] = $plural;
        if ($assocClass = $this->_getAssocClass($head)) $arrInfo['assocClass'] = $assocClass;
        
        if ($abstract) $arrInfo['isAbstract'] = true;
            else $arrInfo['implObject'] = $this;
        
        $arrInfo['implClass'] = get_class($this);
        
        return $arrInfo; 
    }
    
    function getFormOptions($propName, $onlyStatic = true) {
        $pi = $this->getPropertyInfo($propName, $onlyStatic);
        return $pi->toFormOptions();
    }    

    //------------------------------ ACCESSOR METHODS' ALIASES ---------------------------+

    function countField($propName) { return $this->countProperty($propName); }
    function countAssoc($propName) { return $this->countProperty($propName); }
    function listField($propName) { return $this->listProperty($propName); }
    function listAssoc($propName) { return $this->listProperty($propName); }
    function deleteFieldItem($propName, $key) { return $this->deletePropItem($propName, $key); }
    function deleteAssocItem($propName, $key) { return $this->deletePropItem($propName, $key); }
    function setListField($propName, $list = array()) { return $this->setListProperty($propName, $list); }
    function setListAssoc($propName, $list = array()) { return $this->setListProperty($propName, $list); }
        
    //------------- serialization support methods -----------
    
    /**
     * @access protected
     */
    function _getSerializeSkip () {
        return array();
    }
    
    function __sleep() {
        return array_diff(array_keys(get_object_vars($this)), $this->_getSerializeSkip());
    }
    
    function __wakeup() {
        $this->doOnWakeup();
    }
    
    function doOnWakeup() {
    }
    
}