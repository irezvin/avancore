<?php

class Ac_Model_Relation_Impl extends Ac_Prototyped {
    
    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    /**
     * Description of links between soruce and destination fields. Structure of the array is as follows:
     * array('srcFieldName' => 'destFieldName') or ('srcFieldName' => 'midFieldName') for many-to-many relations
     * @var array
     */
    protected $fieldLinks = false;
    
    /**
     * If many-to-many relations ar used, array('midFieldName' => 'destFieldName')
     * @var array
     */
    protected $fieldLinks2 = false;
    
    /**
     * Describes cardinality of source table (true if source fields point to unique record)
     * @var bool 
     */
    protected $srcIsUnique = null;
    
    /**
     * Describes cardinality of destination table (true if destination fields point to unique record)
     */
    protected $destIsUnique = null;
    
    /**
     * Name of variable in source object that contains reference to destination object (if $destUnique is false, it has to be an array with references) 
     */
    protected $srcVarName = false;
    
    /**
     * Name of variable in source object that contains link records for N-N links
     * @var string
     */
    protected $srcNNIdsVarName = false;
    
    /**
     * Name of variable in destination object that contains reference to source object (if $srcIsMultiple is true, it has to be an array with references)
     */
    protected $destVarName = false;
    
    /**
     * Name of property of source object where count of linked destination records is temporarily stored (or FALSE to not to use this feature)
     * @var bool|string
     */
    protected $srcCountVarName = false;
    
    /**
     * Name of property of destination object where count of linked source records is temporarily stored (or FALSE to not to use this feature)
     * @var bool|string
     */
    protected $destCountVarName = false;
    
    /**
     * @var bool|string
     */
    protected $srcLoadedVarName = false;
    
    protected $srcLoadNNIdsMethod = false;
    
    /**
     * @var Ac_Model_Relation_Impl
     */
    protected $destNNIdsImpl = false;

    /**
     * @var Ac_Model_Mapper|string Mapper or mapper class (will only be used if relation provider is retrieved by ID)
     */
    protected $destMapper = false;
    
    /**
     * @var Ac_Model_Relation_Provider
     */
    protected $provider = false;
    
    /**
     * @param array $prototype Array prototype of the object
     */
    function __construct (array $prototype = array()) {
        
        if (isset($prototype['application'])) {
            if ($prototype['application'])
                $this->setApplication($prototype['application']);
            unset ($prototype['application']);
        }
        
        parent::__construct($prototype);
        
    }
    
    function setApplication(Ac_Application $application) {
        if ($application !== ($oldApplication = $this->application)) {
            $this->application = $application;
        }
    }
    
    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }
    
    /**
     * Assigns links between source table and either dest table or intermediary table.
     * 
     * Array key is always field of source table.
     * 
     * If no intemediary table was configured, array value is corresponding field
     * of destination table.
     * 
     * If intermediary table is configured, array key is field of source table,
     * and array value is field of intermediary table (also setFieldLinks2() must be
     * called for other part of mapping)
     * 
     * @param array $fieldLinks Fields mapping ($srcTableField => $midOrDestField)
     */
    function setFieldLinks(array $fieldLinks) {
        if ($fieldLinks !== ($oldFieldLinks = $this->fieldLinks)) {
            if (!$fieldLinks) throw new Ac_E_InvalidCall("\$fieldLinks must not be empty");
            $this->fieldLinks = $fieldLinks;
        }
    }

    /**
     * Returns links between source table and either dest table or intermediary table.
     * 
     * Array key is always field of source table.
     * 
     * If no intemediary table was configured, array value is corresponding field
     * of destination table.
     * 
     * If intermediary table is configured, array key is field of source table,
     * and array value is field of intermediary table (also getFieldLinks2() returns
     * the other part of mapping)
     * 
     * @return array Fields mapping ($srcTableField => $midOrDestField)
     */
    function getFieldLinks() {
        return $this->fieldLinks;
    }

    /**
     * Assigns links between intermediary table and destination table.
     * If no intermediary table is configured, FALSE value is used.
     * 
     * @param array|bool $fieldLinks2 Fields mapping ($midTableField => $destTableField)
     */
    function setFieldLinks2($fieldLinks2) {
        if ($fieldLinks2 !== ($oldFieldLinks2 = $this->fieldLinks2)) {
            if (is_array($fieldLinks2)) {
                if (!$fieldLinks2) throw new Ac_E_InvalidCall("\$fieldLinks2 must not be empty");
            } elseif ($fieldLinks2 === false) {
            } else {
                throw Ac_E_InvalidCall::wrongType('fieldLinks2', $fieldLinks2, array('array', 'false'));
            }
            $this->fieldLinks2 = $fieldLinks2;
        }
    }

    /**
     * Returns links between intermediary table and destination table.
     * 
     * FALSE is used when such value is non-applicable (no intermediary table
     * is used).
     * 
     * @return array|bool $fieldLinks2 Fields mapping ($midTableField => $destTableField)
     */
    function getFieldLinks2() {
        return $this->fieldLinks2;
    }

    /**
     * Assigns whether source records are uniquely identified by their foreign keys
     * 
     * If keys in $fieldLinks is enough, this must be set to TRUE.
     * Used to determine whether SRC items will be arrays' or single objects.
     * 
     * @param bool $srcIsUnique
     */
    function setSrcIsUnique($srcIsUnique) {
        $this->srcIsUnique = (bool) $srcIsUnique;
    }

    /**
     * Returns whether source records are uniquely identified by their foreign keys
     * 
     * @return bool
     */
    function getSrcIsUnique() {
        return $this->srcIsUnique;
    }

    /**
     * Assigns whether destination records are uniquely identified by their foreign keys
     * 
     * If values in $fieldLinks/$fieldLinks2 are enough to identify dest record(s), 
     * this must be set to TRUE. 
     * 
     * Used to determine whether DEST items will be arrays' or single objects.
     * 
     * @param bool $destIsUnique
     */
    function setDestIsUnique($destIsUnique) {
        $destIsUnique = (bool) $destIsUnique;
        if ($destIsUnique !== ($oldDestIsUnique = $this->destIsUnique)) {
            $this->destIsUnique = $destIsUnique;
        }
    }

    /**
     * Returns whether destination records are uniquely identified by their foreign keys
     * @return bool
     */
    function getDestIsUnique() {
        return $this->destIsUnique;
    }

    /**
     * Assigns name of variable in source object that contains reference to destination object 
     * (if $destIsUnique is false, it will be array with references).
     * 
     * If no reference must be assigned, FALSE can be provided.
     * 
     * @param string|bool $srcVarName
     */
    function setSrcVarName($srcVarName) {
        if ($srcVarName !== ($oldSrcVarName = $this->srcVarName)) {
            $this->srcVarName = $srcVarName;
        }
    }

    /**
     * Returns name of variable in source object that contains reference to destination object
     * FALSE means no such reference is assigned.
     * 
     * @return string|bool
     */
    function getSrcVarName() {
        return $this->srcVarName;
    }

    /**
     * Assigns name of variable in source object that contains intermediary records
     *
     * FALSE = such variable does not exist / not used
     * 
     * @param string|bool $srcNNIdsVarName
     */
    function setSrcNNIdsVarName($srcNNIdsVarName) {
        if ($srcNNIdsVarName !== ($oldSrcNNIdsVarName = $this->srcNNIdsVarName)) {
            $this->srcNNIdsVarName = $srcNNIdsVarName;
        }
    }

    /**
     * Returns name of variable in source object that contains intermediary records
     * 
     * FALSE = such variable does not exist / not used
     * 
     * @return string|bool
     */
    function getSrcNNIdsVarName() {
        return $this->srcNNIdsVarName;
    }

    /**
     * Assigns name of variable in destination object that contains reference to source object 
     * (if $srcIsUnique is false, it will be array with references).
     * 
     * If no reference must be assigned, FALSE can be provided.
     * 
     * @param string|bool $destVarName
     */
    function setDestVarName($destVarName) {
        if ($destVarName !== ($oldDestVarName = $this->destVarName)) {
            $this->destVarName = $destVarName;
        }
    }

    /**
     * Returns name of variable in destination object that contains reference to source object
     * FALSE means no such reference is assigned.
     * 
     * @return string|bool
     */
    function getDestVarName() {
        return $this->destVarName;
    }
    
    /**
     * Variable in SRC with COUNT of DEST objects
     * 
     * Assigns name of property of source object where count of linked destination 
     * records is temporarily stored (or FALSE to ignore this feature)
     * 
     * @param string|bool $srcCountVarName
     */
    function setSrcCountVarName($srcCountVarName) {
        if ($srcCountVarName !== ($oldSrcCountVarName = $this->srcCountVarName)) {
            $this->srcCountVarName = $srcCountVarName;
        }
    }

    /**
     * Variable in SRC with COUNT of DEST objects
     * 
     * Returns name of property of source object where count of linked destination 
     * records is temporarily stored (or FALSE to ignore this feature)
     * 
     * @return string|bool
     */
    function getSrcCountVarName() {
        return $this->srcCountVarName;
    }

    /**
     * Variable in DEST with COUNT of SRC objects
     * 
     * Assigns name of property of destination object where count of linked source
     * records is temporarily stored (or FALSE to ignore this feature)
     * 
     * @param string|bool $destCountVarName
     */
    function setDestCountVarName($destCountVarName) {
        if ($destCountVarName !== ($oldDestCountVarName = $this->destCountVarName)) {
            $this->destCountVarName = $destCountVarName;
        }
    }

    /**
     * Variable in DEST with COUNT of SRC objects
     * 
     * Returns name of property of destination object where count of linked source
     * records is temporarily stored (or FALSE to ignore this feature)
     * 
     * @return string|bool
     */
    function getDestCountVarName() {
        return $this->destCountVarName;
    }
    
    /**
     * Assigns variable in SRC that denotes that DEST objects are fully loaded (For partially-loaded
     * associations feature)
     * 
     * Is only applicable when !$destIsUnique.
     * 
     * FALSE = feature not used (any non-FALSE value of $srcVarName is treated as the association
     * is fully loaded)
     * 
     * @param string|bool $srcLoadedVarName
     */
    function setSrcLoadedVarName($srcLoadedVarName) {
        if ($srcLoadedVarName !== ($oldSrcLoadedVarName = $this->srcLoadedVarName)) {
            $this->srcLoadedVarName = $srcLoadedVarName;
        }
    }

    /**
     * Returns variable in SRC that denotes that DEST objects are fully loaded (For partially-loaded
     * associations feature)
     * 
     * Is only applicable when !$destIsUnique.
     * 
     * FALSE = feature not used (any non-FALSE value of $srcVarName is treated as the association
     * is fully loaded)
     * 
     * @return string|bool
     */
    function getSrcLoadedVarName() {
        return $this->srcLoadedVarName;
    }
    
    function setSrcLoadNNIdsMethod($srcLoadNNIdsMethod) {
        $this->srcLoadNNIdsMethod = $srcLoadNNIdsMethod;
    }

    function getSrcLoadNNIdsMethod() {
        return $this->srcLoadNNIdsMethod;
    }

    /**
     * @param string|Ac_Model_Object $mapperOrId
     */
    function setDestMapper($mapperOrId) {
        if (is_object($mapperOrId) && !($mapperOrId instanceof Ac_Model_Mapper)) {
            throw Ac_E_InvalidCall::wrongClass('mapperOrId', $mapperOrId, 'Ac_Model_Mapper');
        }
        $this->destMapper = $mapperOrId;
    }

    /**
     * @param bool $asIs Return mapper ID if no object was provided during setDestMapper() call
     * @return Ac_Model_Mapper
     */
    function getDestMapper($asIs = false) {
        if ($asIs) $res = $this->destMapper;
        else {
            $res = null;
            if (is_object($this->destMapper)) $res = $this->destMapper;
            elseif ($this->destMapper) 
                $res = $this->application? $this->application->getMapper ($this->destMapper) : Ac_Model_Mapper::getMapper($this->destMapper);
        }
        return $res;
    }
    
    function setProvider($provider) {
        $this->provider = $provider;
    }
    
    /**
     * @param bool $asIs Dont create instance
     * @return Ac_Model_Relation_Provider (when $asIs, result is mixed)
     */
    function getProvider($asIs = false) {
        if (!$asIs && !is_object($this->provider)) {
            if (is_string($this->provider)) {
                $mapper = $this->getDestMapper();
                if (!$mapper) 
                    throw new Ac_E_InvalidUsage("Cannot retrieve relation Provider by identifier string without having destMapper set");
                $this->provider = $mapper->getRelationProviderByRelationId($this->provider);
            } elseif ($this->provider) {
                $def = array();
                if ($this->application) $def['application'] = $this->application;
                $this->provider = Ac_Prototyped::factory($this->provider, 'Ac_Model_Relation_Provider', $def);
            }
            $res = $this->provider;
            if (!$res) $res = null;
        } else {
            $res = $this->provider;
        }
        return $res;
    }
    
    function setDestNNIdsImpl($destNNIdsImpl) {
        $this->destNNIdsImpl = $destNNIdsImpl;
    }
    
    /**
     * @return Ac_Model_Relation_Impl
     */
    function getDestNNIdsImpl($asIs = false) {
        if (!is_object($this->destNNIdsImpl) && !$asIs) {
            if ($this->destNNIdsImpl) {
                $def = array();
                if ($this->application) $def['application'] = $this->application;
                $this->destNNIdsImpl = Ac_Prototyped::factory($this->destNNIdsImpl, 'Ac_Model_Relation_Impl', $def);
            }
            if (!$this->destNNIdsImpl) return null;
        }
        return $this->destNNIdsImpl;
    }

    /**
     * Returns one or more destination objects for given source object
     * @param Ac_Model_Data|object $srcData
     * @param int $matchMode How keys of result array are composed (can be Ac_Model_Relation_Abstract::RESULT_PLAIN, Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS, Ac_Model_Relation_Abstract::RESULT_ORIGINAL_KEYS, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS) -- only if srcData is array
     * @param mixed $defaultValue Value to be returned for missing rows when $matchMode is Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS or when $srcData is an object. By default it is null if $this->destIsUnique and empty array() if not.
     * @return Ac_Model_Data|array
     */
    function getDest ($srcData, $matchMode = Ac_Model_Relation_Abstract::RESULT_PLAIN, $defaultValue = null) {
        $hasDefaultValue = func_num_args() >= 3;
        $destIsUnique = $this->getDestIsUnique();
        $keys = array_keys($this->fieldLinks);
        
        if (is_array($srcData)) { // we assume that this array is of objects or rows
            $values = array();
            $midKeys = is_array($this->fieldLinks2)? array_values($this->fieldLinks2) : array();
            if ($matchMode != Ac_Model_Relation_Abstract::RESULT_PLAIN) {
                $map = array();
                $midMap = array();
                $this->extractSrcMapAndValues($srcData, $map, $values, $midMap, $midValues, $alreadyLoaded,
                    false, $matchMode == Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS);
            } else {
                $this->extractSrcValues($srcData, $values, $midValues);
            }

            $rows = array();    // rows retrieved by keys of right storage
            $lRows = array();   // rows retrieved by left keys of "mid-table" - some Providers can do that

            $byKeys = $matchMode != Ac_Model_Relation_Abstract::RESULT_PLAIN;
            if ($this->fieldLinks2) {
                list ($rows, $lRows) = $this->getWithValues($midValues, $byKeys, $values, true);
            } else {
                list ($rows, $lRows) = $this->getWithValues($values, $byKeys, array(), true);
            }

            $defaultValue = $destIsUnique? $defaultValue : array();
            
            if ($matchMode == Ac_Model_Relation_Abstract::RESULT_ORIGINAL_KEYS || $matchMode == Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS || $matchMode === Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS && $midMap) {
                $res = array();
                if ($matchMode !== Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS && $lRows) $this->unmapResult($keys, $map, $matchMode, $res, $lRows, $defaultValue, $destIsUnique, false);
                    else $res = $midMap? $lRows : $rows;
                if ($midMap) {
                    $this->unmapResult(array_values($this->fieldLinks2), $midMap, $matchMode, $res, $rows, 
                        $defaultValue, $destIsUnique, true);                    
                }
            } else {
                $res = $lRows;
                if (!$res && $rows) {
                    if ($destIsUnique || !$byKeys) $res = array_values($rows);
                    else foreach ($rows as $lRows) $res = array_merge($res, $lRows);
                }
            }
        } elseif (is_object($srcData)) {
            if ($this->srcNNIdsVarName && isset($srcData->{$this->srcNNIdsVarName}) && is_array($ids = $srcData->{$this->srcNNIdsVarName})) {
                $values = array();
                $midValues = $this->nnIdsToValues($ids, array_values($this->fieldLinks2));
            } else {
                $values = $this->getValues($srcData, $keys, false, true);
                $midValues = array();
            }
            if ($this->fieldLinks2) {
                list ($lRows, $rows) = $this->getWithValues($midValues, false, $values, (bool) $midValues);
            } else {
                list ($lRows, $rows) = $this->getWithValues($values, false, array(), false);
            }
            $res = $lRows? $lRows: $rows;
            if (!$res) {
                if (!$hasDefaultValue) $defaultValue = $destIsUnique? null : array();
                    else $res = $defaultValue;
            }
        } else {
            trigger_error ('$srcData/$destData must be an array or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    function countDest ($srcData, $separate = true, $matchMode = Ac_Model_Relation_Abstract::RESULT_PLAIN) {
        $keys = array_keys($this->fieldLinks);
        if (is_array($srcData)) { // we assume that this is array of objects or array of rows
            $values = array();
            $midKeys = is_array($this->fieldLinks2)? array_values($this->fieldLinks2) : array();
            if ($matchMode === Ac_Model_Relation_Abstract::RESULT_PLAIN && is_array($srcData) && $separate) {
                trigger_error("Using countSrc or countDest(array, true, Ac_Model_Relation_Abstract::RESULT_PLAIN) "
                    . "does not make sense; using Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS instead", E_USER_NOTICE);
                $matchMode = Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS;
            }
            if ($separate && $matchMode !== Ac_Model_Relation_Abstract::RESULT_PLAIN) {
                $map = array();
                $this->extractSrcMapAndValues($srcData, $map, $values, $midMap, $midValues, $alreadyLoaded, 
                    false, $matchMode === Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS);
            } else {
                $midMap = array();
                $this->extractSrcValues($srcData, $values, $midValues);
            }
                
            $counts = $this->countWithValues($midValues, $separate, $values, true);
            
            if (!$separate) {
                $res = $counts;
            } else {
                if ($counts) {
                    if ($matchMode === Ac_Model_Relation_Abstract::RESULT_PLAIN) {
                        $res = $counts;
                    } else {
                        $this->unmapResult($keys, $map, $matchMode, $res, $counts, 0, true, false);
                    }
                }
                if ($midMap) {
                    $this->unmapResult(array_values($this->fieldLinks2), $midMap, $matchMode, $res, array(), 
                        0, false, true, true);
                }
            }
        } elseif (is_a($srcData, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($srcData)) {
            if ($this->srcNNIdsVarName !== false) {
                $nnIds = $this->getValue($srcData, $this->srcNNIdsVarName);
            } else {
                $nnIds = null;
            }
            if (is_array($nnIds)) $res = count($nnIds);
            else {
                $values = $this->getValues($srcData, $keys, false, false);
                $res = $this->countWithValues(array(), false, $values, false);
            }
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    function loadDest (& $srcData, $dontOverwriteLoaded = true, $biDirectional = true, $returnAll = true) {
        $defaultValue = $this->getDestIsUnique()? null : array();
        $biDirectional = $biDirectional && strlen($this->destVarName);

        $lvn = $this->srcLoadedVarName;
        
        $destIsUnique = $this->getDestIsUnique();
        $srcIsUnique = $this->getSrcIsUnique();
        $keys = array_keys($this->fieldLinks);
        $res = array();
        if (is_array($srcData)) { // we assume that this array is of objects or rows
            
            $values = array();
            $map = array();
            $midValues = array();
            
            $alreadyLoaded = array();

            $instancesVar = $dontOverwriteLoaded? $this->srcVarName : false;
            
            $this->extractSrcMapAndValues($srcData, $map, $values, $midMap, $midValues, $alreadyLoaded, 
                $instancesVar, false, $this->srcLoadedVarName);
            
            if ($this->fieldLinks2) {
                $r = $this->getWithValues($midValues, true, $values, true);
                list($rows2, $rows) = $r;
            } else {
                $r = $this->getWithValues($values, true, array());
                list($rows, $rows2) = $r;
            }
                
            $res = $rows2? $rows2 : $rows; // $rows2 set will include $rows
            
            if ($map) $this->unmap($keys, $map, $rows, $srcData, $defaultValue, $biDirectional, false);
            if ($midMap) $this->unmap(array_values($this->fieldLinks2), $midMap, $rows2, $srcData, $defaultValue, $biDirectional, true);
            
            if (!$res) $res = $rows;
            
            if ($returnAll && $alreadyLoaded) $res['__alreadyLoaded'] = $alreadyLoaded;
            
        } elseif (is_object($srcData)) {
            $loaded = false;
            $items = array();
            $pks = array();
            $skip = false;
            if ($dontOverwriteLoaded) {
                $isEmpty = $this->isVarEmpty($srcData, $this->srcVarName, $loaded);
                if (!$isEmpty) {
                    if ($this->srcLoadedVarName !== false) {
                        if ($this->getValue($srcData, $this->srcLoadedVarName)) {
                            $skip = true;
                        } else {
                            if (is_array($loaded)) {
                                $items = $loaded;
                                foreach ($loaded as $item) {
                                    if (is_object($item) && $item instanceof Ac_Model_Object) {
                                        if ($item->hasFullPrimaryKey()) $pks[$item->getPrimaryKey()] = $item;
                                    }
                                }
                            }
                        }
                    } else {
                        $skip = true;
                    }
                }
            }
            if (!$skip) {  
                // check for NN ids
                if ($this->srcNNIdsVarName && isset($srcData->{$this->srcNNIdsVarName}) && is_array($ids = $srcData->{$this->srcNNIdsVarName})) {
                    $midValues = $this->nnIdsToValues($ids, array_values($this->fieldLinks2));
                    $values = array();
                } else {
                    $values = $this->getValues($srcData, $keys, false, false);
                    $midValues = array();
                }
                if ($this->fieldLinks2) {
                    $multiple = (bool) $midValues;
                    list ($rows, $rows2) = $this->getWithValues($midValues, false, $values, $multiple);
                } else {
                    list ($rows, $rows2) = $this->getWithValues($values, false, array(), false);
                }
                if ($rows2) $rows = $rows2; // they are mutually exclusive -- see the logic above
                if ($rows) {
                    $toSet = $rows;
                    if ($biDirectional) $this->linkBack($rows, $srcData, $this->destVarName, !$destIsUnique, $srcIsUnique);
                } else $toSet = $defaultValue;
                if ($items && is_array($toSet)) {
                    $toSet = $this->mergeByPk($toSet, $items, $pks);
                }
                $this->setVal($srcData, $this->srcVarName, $toSet);
                if ($this->srcLoadedVarName !== false) {
                    $this->setVal($srcData, $this->srcLoadedVarName, true);
                }
                if ($destIsUnique) $res = array(& $rows); else $res = $rows;
            } else {
                if ($returnAll && !is_null($loaded)) {
                    $res = is_array($loaded)? $loaded : array($loaded);
                } else {
                    $res = array();
                }
            }
        } else {
            trigger_error ('$srcData/$destData must be an array or an object, '.Ac_Util::typeClass($srcData).' provided', E_USER_ERROR);
        }
            
        return $res;
    }
    
    function loadDestNNIds($srcData, $dontOverwriteLoaded = true) {
        if ($i = $this->destNNIdsImpl) {
            if (!is_object($i)) $i = $this->getDestNNIdsImpl();
            $res = $i->loadDest($srcData, $dontOverwriteLoaded);
            // TODO: this should be implemented in NNIdsImpl
            $this->fixNNIds($srcData, array_keys($this->fieldLinks2), $this->srcNNIdsVarName);
        } else {
            trigger_error("loadDestNNIds() produces no effect without non-empty setDestNNIDsImpl()", E_USER_WARNING);
        }
        return $res;
    }
    
    /**
     * Counts destination objects and stores result in $srcCountVarName of each corresponding $srcData object
     */
    function loadDestCount ($srcData, $dontOverwriteLoaded = true) {
        if (!$this->srcCountVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $srcCountVarName is not set');
        $keys = array_keys($this->fieldLinks);
        if (is_array($srcData)) { // we assume that this array is of objects or rows
            $values = array();
            $map = array();
            
            $this->extractSrcMapAndValues($srcData, $map, $values, $midMap, $midValues, $alreadyLoaded,
                $dontOverwriteLoaded? $this->srcCountVarName : false);
            
            if ($midMap) {
                foreach ($midMap as $m) {
                    $dataKey = $m[1];
                    $this->setVal($srcData[$dataKey], $this->srcCountVarName, count($m[0])); 
                }
            }
            
            if ($values) {
                
                if ($this->fieldLinks2) {
                    $counts = $this->countWithValues(array(), true, $values);
                } else {
                    $counts = $this->countWithValues($values, true);
                }
                
                if (count($keys) === 1) {
                    foreach ($map as $m) {
                        $dataKey = $m[1];
                        if (isset($counts[$countKey = $m[0][0]])) {
                            $toSet = $counts[$countKey];
                        } else $toSet = 0;
                        $this->setVal($srcData[$dataKey], $this->srcCountVarName, $toSet); 
                    }
                } else {
                    foreach ($map as $m) {
                        $countPath = $m[0];
                        $dataKey = $m[1];
                        $count = Ac_Util::simpleGetArrayByPath($counts, $countPath, 0);
                        $this->setVal($srcData[$dataKey], $this->srcCountVarName, $count);
                    }
                }
            }
        } elseif (is_a($srcData, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($srcData)) {
            if ($this->srcNNIdsVarName && is_array($v = $this->getValue($srcData, $this->srcNNIdsVarName))) {
                $this->setVal($srcData, $this->srcCountVarName, count($v));
            }
            if (!$dontOverwriteLoaded || $this->isVarEmpty($srcData, $this->srcCountVarName)) {  
                $values = $this->getValues($srcData, $keys, false, false);
                if ($this->fieldLinks2) {
                    $count = $this->countWithValues(array(), false, $values, false);
                } else {
                    $count = $this->countWithValues($values, false, array(), false);
                }
                $this->setVal($srcData, $this->srcCountVarName, $count, $this->srcCountVarName);
            }
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
    }
    
    /**
     * Extracts keys from source array and makes map (recordKeys => srcArrayKey). 
     * Format of $map will be array($keys, $sourceKey). By traversing the map later, 
     * one can find corresponding records in the right table. 
     */
    protected function extractSrcMapAndValues(
        & $source, & $map, & $values, &$midMap = null, & $midValues = null, & $alreadyLoaded = null, 
        $instancesVar = false, $useKeysForMidMapWriteKeys = false, $isLoadedVar = false
    ) {
        $keys = array_keys($this->fieldLinks);
        $midKeys = is_array($this->fieldLinks2)? array_values($this->fieldLinks2) : array();
        $midIdsVar = $this->srcNNIdsVarName;
        $map = array();
        if (!is_array($midMap)) $midMap = array();
        if (!is_array($alreadyLoaded)) $alreadyLoaded = array();
        $values = array();
        if (!is_array($midValues)) $midValues = array();
        
        $acceptsMidValuesOnly = ($midIdsVar !== false) && !$this->getProvider()->getAcceptsSrcValues();
        if ($acceptsMidValuesOnly) $this->loadMidIdsFor($source);
        
        foreach(array_keys($source) as $k) {
            $srcItem = $source[$k];
            $hasIds = false;
            $items = array();
            $pks = array();
            if ($instancesVar !== false) {
                $v = false;
                $hasVar = !$this->isVarEmpty($srcItem, $instancesVar, $v);
                if ($hasVar) {
                    if (!is_null($v) && !(is_array($v) && !count($v))) {
                        $alreadyLoaded[$k] = $v;
                    } else {
                        $v = false;
                    }
                }
                if ($hasVar) {
                    if ($isLoadedVar !== false) {
                        if (is_object($v) && $v instanceof Ac_Model_Object && $v->hasFullPrimaryKey()) {
                            $pk = $v->getPrimaryKey();
                        } elseif (is_array($v)) {
                            $items = $v;
                            foreach ($v as $item) {
                                if (is_object($item) && $item instanceof Ac_Model_Object 
                                    && $item->hasFullPrimaryKey()) {
                                    $pk = $item->getPrimaryKey();
                                    $pks[$pk] = true;   
                                }
                            }
                        }
                        if ($this->getValue($srcItem, $isLoadedVar)) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }
            }
            if ($midIdsVar !== false && $midKeys) {
                $hasIds = is_array($ids = $this->getValue($srcItem, $midIdsVar));
                if ($hasIds) {
                    $ids = $this->nnIdsToValues($ids, $midKeys);
                    foreach ($ids as $v) {
                        if (is_array($v)) {
                            $mk = implode('-', $v);
                        }
                            else $mk = ''.$v;
                        $midValues[$mk] = $v;
                    }
                    if ($useKeysForMidMapWriteKeys) {
                        if (count($keys) == 1) {
                            $tk = $this->getValue($srcItem, $keys[0]);
                        } else {
                            $tk = $this->getValues($srcItem, $keys);
                        }
                        $midMap[$k] = array($ids, $tk, $items, $pks);
                    } else {
                        $midMap[$k] = array($ids, $k, $items, $pks);
                    }
                }
            }
            if (!$hasIds && !$acceptsMidValuesOnly) {
                $itemValues = $this->getValues($srcItem, $keys, false, false);
                $map[] = array($itemValues, $k, $items, $pks);
                $values[] = $itemValues;
            }
        }
    }
    
    protected function extractSrcValues(& $source, & $values, & $midValues = null) {
        
        $keys = array_keys($this->fieldLinks);
        $midKeys = $this->fieldLinks2? array_values($this->fieldLinks2) : array();
        $nnIdsVar = $this->srcNNIdsVarName;
        
        $values = array();
        if (!is_array($midValues)) $midValues = array();
        
        foreach (array_keys($source) as $k) {
            $hasIds = false;
            $srcItem  = $source[$k];
            if ($nnIdsVar !== false) {
                $hasIds = is_array($ids = $this->getValue($srcItem, $nnIdsVar));
                if ($hasIds) {
                    $ids = $this->nnIdsToValues($ids, $midKeys);
                    foreach ($ids as $v) {
                        if (is_array($v)) $mk = implode('-', $v);
                            else $mk = ''.$v;
                        $midValues[$mk] = $v;
                    }
                }
            }
            if (!$hasIds) {
                $values[] = $this->getValues($srcItem, $keys, false, false);
            }
        }
    }
    
    protected function loadMidIdsFor(& $source) {
        if ($this->destNNIdsImpl) $this->getDestNNIdsImpl()->loadDestNNIds($source);
        elseif ($m = $this->srcLoadNNIdsMethod) {
            if (is_array($m) && isset($m[0]) && $m[0] === true && isset($m[1])) {
                $mapper = $this->getDestMapper();
                if (!$mapper) throw new Ac_E_InvalidUsage("\$destMapper is required when using TRUE as first member of array \$srcLoadNNIdsMethod");
                $method = $m[1];
                $mapper->$method($source);
            } else {
                call_user_func($m, $source);
            }
        }
    }
    
    protected function unmapResult($keys, $map, $matchMode, & $res, $rows, $defaultValue, $isUnique, 
        $mapMultiple, $useCounts = false) {
        if (count($keys) === 1) {
            foreach ($map as $m) {
                $toSet = false;
                if ($mapMultiple && !$isUnique) {
                    $toSet = array();
                    if ($useCounts) $toSet = count($m[0]);
                    else {
                        foreach ($m[0] as $rowKey) {
                            if (isset($rows[$rowKey])) {
                                $toSet = array_merge($toSet, $rows[$rowKey]);
                            }
                        }
                    }
                } else {
                    if ($useCounts) {
                        $toSet = isset($m[0][0]) && $m[0][0] !== false && $m[0][0] !== null? 1: 0;
                    } else {
                        if (isset($rows[$rowKey = $m[0][0]])) $toSet = $rows[$rowKey]; 
                    }
                }
                if ($toSet === false) {
                    if ($matchMode == Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS) {
                        $toSet = $defaultValue;
                    } else {
                        continue;
                    }
                }
                if (is_array($m[1])) Ac_Util::simpleSetArrayByPath ($res, $m[1], $toSet);
                    else $res[$m[1]] = $toSet; 
            }
        } else {
            foreach ($map as $m) {
                $toSet = false;
                if ($mapMultiple && !$isUnique) {
                    $toSet = array();
                    if ($useCounts) $toSet = count($m[0]);
                    else {
                        foreach ($m[0] as $rowPath) {
                            $row = Ac_Util::simpleGetArrayByPath($rows, $rowPath, false);
                            if ($row !== false) {
                               $toSet = array_merge($toSet, $row);
                            }
                        }
                    }
                } else {
                    $rowPath = $m[0];
                    $dataKey = $m[1];
                    if ($useCounts) {
                        $toSet = isset($m[0][0]) && $m[0][0] !== false && $m[0][0] !== null? 1: 0;
                    } else {
                        $row = Ac_Util::simpleGetArrayByPath($rows, $rowPath, false);
                        if ($row !== false) {
                           $toSet = $row;
                        }
                    }
                }
                if ($toSet === false) {
                    if ($matchMode == Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS) {
                        $toSet = $defaultValue;
                    } else {
                        continue;
                    }
                }
                if (is_array($m[1])) Ac_Util::simpleSetArrayByPath ($res, $m[1], $toSet);
                    else $res[$m[1]] = $toSet; 
            }
        }
    }   
    
    protected function nnIdsToValues($idsArray, $keys) {
        $res = array();
        if (count($keys) == 1) {
            $key = array_pop($keys);
            foreach ($idsArray as $id) {
                if (is_array($id)) {
                    if (isset($id[$key])) $res[] = $id[$key];
                } else {
                    $res[] = $id;
                }
            }
        } else {
            $ck = count($keys);
            foreach ($idsArray as $id) {
                $r = array();
                if (is_array($id)) {
                    foreach ($keys as $key) {
                        if (isset($id[$key])) $r[] = $id[$key];
                    }
                }
                if (count($r) == $ck) $res[] = $r;
            }
        }
        return $res;
    }
    
    protected function unmap($keys, $map, & $rows, & $data, $defaultValue, $biDirectional, $mapMultiple = false) {
        
        $destIsUnique = $this->getDestIsUnique(); 
        $srcIsUnique = $this->getSrcIsUnique(); 
        
        if (!$this->srcVarName && !$this->destVarName && !$this->srcLoadedVarName) return;
        
        if (count($keys) === 1) {
            foreach ($map as $m) {
                $dataKey = $m[1];
                if ($mapMultiple && !$destIsUnique) {
                    $toSet = array();
                    foreach ($m[0] as $rowKey) {
                        if (isset($rows[$rowKey])) {
                            $toSet = array_merge($toSet, $rows[$rowKey]);
                            if ($biDirectional) {
                                $this->linkBack($rows[$rowKey], $data[$dataKey], $this->destVarName, 
                                    !$destIsUnique, $srcIsUnique); 
                            }
                        }
                    }
                } else {
                    if (isset($rows[$rowKey = $m[0][0]])) {
                        $toSet = $rows[$rowKey];
                        if ($biDirectional) $this->linkBack($rows[$rowKey], $data[$dataKey], $this->destVarName, 
                            !$destIsUnique, $srcIsUnique); 
                    } else {
                        $toSet = $defaultValue;
                    }
                }
                if (!$destIsUnique && $this->srcLoadedVarName) {
                    $toSet = $this->mergeByPk($toSet, $m[2], $m[3]);
                }
                $this->setVal($data[$dataKey], $this->srcVarName, $toSet);
                if ($this->srcLoadedVarName !== false) {
                    $this->setVal($data[$dataKey], $this->srcLoadedVarName, true);
                }
            }
        } else {
            foreach ($map as $m) {
                $dataKey = $m[1];
                if ($mapMultiple && !$destIsUnique) {
                    $toSet = array();
                    foreach ($m[0] as $rowPath) {
                        $row = Ac_Util::simpleGetArrayByPath($rows, $rowPath, false);
                        if ($row !== false) {
                           $toSet = array_merge($toSet, $row);
                           if ($biDirectional) $this->linkBack($row, $data[$dataKey], $this->destVarName, 
                               !$destIsUnique, $srcIsUnique);
                        }
                    }
                } else {
                    $rowPath = $m[0];
                    $row = Ac_Util::simpleGetArrayByPath($rows, $rowPath, false);
                    if ($row !== false) {
                       $toSet = $row;
                       if ($biDirectional) $this->linkBack($row, $data[$dataKey], $this->destVarName, !$destIsUnique, $srcIsUnique);
                    }
                    else {
                        $toSet = $defaultValue;
                    }
                }
                if (!$destIsUnique && $this->srcLoadedVarName !== false) {
                    $toSet = $this->mergeByPk($toSet, $m[2], $m[3]);
                }
                $this->setVal($data[$dataKey], $this->srcVarName, $toSet);
                if ($this->srcLoadedVarName !== false) {
                    $this->setVal($data[$dataKey], $this->srcLoadedVarName, true);
                }
            }
        }
    }
    
    protected function mergeByPk(array $newRecords, array $currentRecords, array $pks) {
        $res = $currentRecords;
        foreach ($newRecords as $rec) {
            if (!isset($pks[$rec->getPrimaryKey()])) $res[] = $rec;
        }
        return $res;
    }
    
    protected function getWithValues($destValues, $byKeys, $srcValues = array(), $multipleValues = true) {
        $prov = $this->getProvider();
        if (!$prov) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."(): \$provider not provided");
        
        // multiply
        if (!$multipleValues) {
            $srcValues = $srcValues? array($srcValues) : array();
            $byKeys = false;
            $destValues = $destValues? array($destValues) : array();
            $byKeys = false;
        }
        
        if (!$srcValues) $srcValues = array();
        $res = $prov->getWithValues($destValues, $byKeys, $srcValues);
        
        // de-multiply to one row
        if (!$multipleValues && $this->destIsUnique) {
            list ($rows, $rows2) = $res;
            if ($rows) $res = array($rows? $rows[0] : null, null);
            elseif ($rows2) $res = array(null, $rows2? $rows2[0] : null);
        }
        
        return $res;
    }
    
    // TODO: optimize _countWithValues and _getWithValues to place instances into nested array faster when resultset is ordered
    
    protected function countWithValues ($destValues, $separate = true, $srcValues = false, $multipleValues = true) {
        $prov = $this->getProvider();
        if (!$prov) throw new Ac_E_InvalidUsage("Cannot getWithValues(): \$provider not provided");
        
        // multiply
        if (!$multipleValues) { 
            $srcValues = $srcValues? array($srcValues) : array();
            $destValues = $destValues? array($destValues) : array();
            $separate = false;
        }
        
        if (!$destValues) $destValues = array();

        $res = $prov->countWithValues($destValues, $separate, $srcValues);
        
        return $res;
    }
    
    protected function fixNNIds($data, $fields, $varName) {
        $isSingle = false;
        if (is_object($data)) {
            $isSingle = true;
            $d = array(& $data);
        } else {
            $d = $data;
        }
        foreach (array_keys($d) as $k) {
            $row = $d[$k];
            $val = $this->getValue($row, $varName);
            if (is_array($val)) {
                $newVal = array();
                if (count($fields) == 1) {
                    $f = $fields[0];
                    foreach ($val as $rec) {
                        $newVal[] = $rec[$f];
                    }
                } else {
                    foreach ($val as $rec) {
                        $nv = array();
                        foreach ($fields as $f) $nv[$f] = $rec[$f];
                        $newVal[] = $nv;
                    }
                }
                $this->setVal($row, $varName, $newVal);
            }
        }
    }
    
    protected function setVal(& $dest, $varName, $val) {
        if (!$varName) return;
        if (is_array($dest)) {
            $dest[$varName] = $val;
        }
        elseif (method_exists($dest, $setter = 'set'.$varName)) $dest->$setter($val);
        else $dest->$varName = $val;
    }
    
    /**
     * $linkTo->$varName = $linked or $linkTo[$varName] = $linked
     *
     * @param object|array $linkTo - object or array to which we are linking $linked object 
     * @param object|array $linked - object that we are adding
     * @param string $varName - name of $linkTo property or key (if it's an array)
     * @param bool $toIsArray - if $linkTo is an array of an objects or an arrays
     * @param bool $linkedIsUnique - whether we should replace $linkTo->varName or add to it ($linkTo->varName[]) 
     */
    protected function linkBack(& $linkTo, & $linked, $varName, $toIsArray, $linkedIsUnique) {
        if (!$varName) return;
        if ($toIsArray) {
            foreach (array_keys($linkTo) as $k) {
                $this->linkBack($linkTo[$k], $linked, $varName, false, $linkedIsUnique);
            }
        } else {
            $lt = & $linkTo;
            if (is_object($lt)) {
                if ($linkedIsUnique) {
                    $lt->$varName = $linked;
                } else {
                    $skip = false;
                    if (isset($lt->$varName) && is_array($v = $lt->$varName)) {
                        if ($lt instanceof Ac_Model_Object && $lt->hasFullPrimaryKey()) {
                            $pk = $lt->getPrimaryKey();
                            foreach ($v as $item) {
                                if (is_object($item) && $item instanceof Ac_Model_Object 
                                    && $item->hasFullPrimaryKey() && $item->getPrimaryKey() == $pk) {
                                    $skip = true;
                                    break;
                                }
                            }
                        }
                    } else {
                        $lt->$varName = array();
                    }
                    if (!$skip) {
                        $lt->{$varName}[] = $linked;
                    }
                }
            } elseif (is_array($lt)) {
                if ($linkedIsUnique) {
                    $lt[$varName] = $linked;
                } else {
                    if (isset($lt[$varName]) && is_array($lt[$varName])) {
                        // TODO: check that record with same key not already there
                        // PKs of array records is not supported yet
                    } else {
                        $lt[$varName] = array();
                    }
                    $lt[$varName][] = $linked;
               }
            }
        }
    }
    
    protected function isFull($v) {
        if (is_array($v)) foreach ($v as $vv) {
            if (is_null($vv) || $vv === false) return false; 
        } else {
            if (is_null($v) || $v === false) return false;
        }
        return true;
    }
    
    protected function putRowToArray(& $row, & $instance, & $array, $keys, $unique) {
        foreach ($keys as $key) $path[] = $row[$key];
        Ac_Util::simpleSetArrayByPathNoRef($array, $path, $instance, $unique);
    }
    
    protected function putInstanceToArray(& $instance, & $array, $keys, $isDest, $unique) {
        $path = $this->getValues($instance, $keys);
        Ac_Util::simpleSetArrayByPathNoRef($array, $path, $instance, $unique);
    }
    
    protected function getFromArray($src, $fieldName) {
        return $src[$fieldName];
    }
    
    protected function getFromMember($src, $fieldName) {
        return $src->$fieldName;
    }
    
    protected function getFromGetter($src, $fieldName) {
        $m = 'get'.ucfirst($fieldName);
        return $src->$m();
    }
    
    protected function getFromAeData($src, $fieldName) {
        return $src->$fieldName;
    }
    
    /**
     * Retrieves field value from source object or array. Caches retrieval strategy for different classes in static variable (as in Ac_Table_Column).
     * Triggers error if retrieval is not possible.
     */
    protected function getValue($src, $fieldName) {
        static $g = array();
        if (is_array($src)) {
            if (!array_key_exists($fieldName, $src)) trigger_error('Cannot extract field \''.$fieldName.'\' from an array', E_USER_ERROR);
            $res = $src[$fieldName];
        } else {
            $cls = get_class($src);
            if (isset($g[$cls]) && isset($g[$cls][$fieldName])) $getter = $g[$cls][$fieldName];
            else {
                switch(true) {
                    case in_array($fieldName, array_keys(get_class_vars($cls))): $getter = 'getFromMember'; break;
                    case method_exists($src, 'get'.$fieldName): $getter = 'getFromGetter'; break;
                    case is_a($src, 'Ac_Model_Data'): $getter = 'getFromAeData'; break;
                    default:
                        trigger_error('Cannot extract field \''.$fieldName.'\' from an object', E_USER_ERROR);
                }
                $g[$cls][$fieldName] = $getter;
            }
            $res = $this->$getter($src, $fieldName);
        }
        return $res;
    }
    
    protected function mapValues($values, $fieldNames) {
        $i = 0;
        $res = array();
        foreach ($values as $value) {
            $res[$fieldNames[$i]] = $value;
        }
        return $res;
    }
    
    /**
     * Retrieves all values of given fields from source object or array. 
     * 
     * @param Ac_Model_Data|object|array $src Information source
     * @param array|string $fieldNames Names of fields to retrieve (if $single is true, it should be single string)
     * @param $originalKeys Whether keys of result fields should be taken from $fieldNames
     * @param bool $single Whether $fieldNames is single string (single value will be returned) 
     * @return array Field values
     * @access private 
     */
    protected function getValues($src, $fieldNames, $originalKeys = false, $single = false) {
        $res = array();
        if ($single) {
            $res = $this->getValue($src, $fieldNames);
        } else {
            $c = count($fieldNames);
            if ($originalKeys)
                for ($i = 0; $i < $c; $i++) {
                    $res[$fieldNames[$i]] = $this->getValue($src, $fieldName);
                }
            else
               foreach ($fieldNames as $fieldName) {
                    $res[] = $this->getValue($src, $fieldName);
                }
        }
        return $res;
    }
    
    protected function isVarEmpty($srcItem, $var, & $value = false) {
        if (!$var) return true;
        $res = true;
        $value = false;
        if (is_array($srcItem)) {
            if (array_key_exists($var, $srcItem)) {
                if ($srcItem[$var] !== false) {
                    $value = $srcItem[$var];
                    $res = false;
                }
            }
        } else {
            if (Ac_Accessor::objectPropertyExists($srcItem, $var) 
                && ($value = $this->getValue($srcItem, $var)) !== false) {
                $res = false;
            }
        }
        return $res;
    }
    
}