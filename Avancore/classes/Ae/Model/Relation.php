<?php
/**
 * result rows will be returned as plain numeric array of rows
 * @see Ae_Model_Relation::getDest()
 * @see Ae_Model_Relation::getSrc())
 */
define('AMR_PLAIN_RESULT', 0);
/**
 * result rows will have DB keys of source records 
 * @see Ae_Model_Relation::getDest()
 * @see Ae_Model_Relation::getSrc())
 */
define('AMR_RECORD_KEYS', 1);
/**
 * result rows will have keys of original array 
 * @see Ae_Model_Relation::getDest()
 * @see Ae_Model_Relation::getSrc())
 */
define('AMR_ORIGINAL_KEYS', 2);
/**
 * results rows will have keys of original array and all keys from original array will be in result array.
 * Values in places of missing result rows will have default value.
 *  
 * @see Ae_Model_Relation::getDest()
 * @see Ae_Model_Relation::getSrc())
 */
define('AMR_ALL_ORIGINAL_KEYS', 6);

/**
 * Delete linked records when master record is delted
 */
define('AMR_DELETE_CASCADE', 'AMR_DELETE_CASCADE');

/**
 * Restrict deletion of linked records when master record is deleted
 */
define ('AMR_DELETE_RESTRICT', 'AMR_DELETE_RESTRICT');

/**
 * Perform no action when master record is deleted
 */
define ('AMR_DELETE_NO_ACTION', 'AMR_DELETE_NO_ACTION');

/**
 * Set corresponding foreign keys of linked records 
 */
define ('AMR_DELETE_SET_VALUE', 'AMR_DELETE_SET_VALUE');

/**
 * Call specified method of foreign recrods
 */
define ('AMR_DELETE_CALL_METHOD', 'AMR_DELETE_CALL_METHOD');

/**
 * Describes relation between two tables. 
 * 
 * Does all the work with related records (load, count, delete). 
 * Can work with objects, Ae_Model_Data descendants and associative arrays.
 * 
 * Interface of this class has one specific quality: most methods (load, count, delete) are in two flavours: for source table and for destination one.     
 */
class Ae_Model_Relation extends Ae_Model_Relation_Abstract {

    /**
     * @var Ae_Application
     */
    protected $application = false;
    
    /**
     * Name of source table mapper (if given). If source mapper is given, all other parameters will be taken from its function calls.
     * @var string|bool 
     */
    var $srcMapperClass = false;
    
    /**
     * Name of destination table mapper (if given). If destination mapper is given, all other parameters will be taken from its function calls.
     * @var string|bool 
     */
    var $destMapperClass = false;
    
    /**
     * Class of source records. If false, $srcMapper will be used to determine record classes. 
     * If we don't have mapper or $srcRecordClass is an empty string (''), associative arrays will be created instead of objects.
     * @var string|bool
     */
    var $srcRecordClass = false;
    
    /**
     * Class of destination records. If false, $destMapper will be used to determine record classes. 
     * If we don't have mapper or $destRecordClass is an empty string (''), associative arrays will be created instead of objects.
     * @var string|bool
     */
    var $destRecordClass = false;
    
    /**
     * Name of source table
     * @var string|bool
     */
    var $srcTableName = false;
    
    /**
     * Name of destination table
     * @var string|bool
     */
    var $destTableName = false;
        
    /**
     * Name of linking table (for many-to-many relations). It is important to set midTableName to empty string ('') if we don't have it. False means that
     * value is not initialized and has to be taken from one of the mappers (if possible)
     * @var string|bool
     */
    var $midTableName = '';
    
    /**
     * Default alias for middle table
     * @var string
     */
    var $midTableAlias = 'mid';
    
    /**
     * Description of links between soruce and destination fields. Structure of the array is as follows:
     * array('srcFieldName' => 'destFieldName') or ('srcFieldName' => 'midFieldName') for many-to-many relations
     * @var array
     */
    var $fieldLinks = false;
    
    /**
     * If many-to-many relations ar used, array('midFieldName' => 'destFieldName')
     * @var array
     */
    var $fieldLinks2 = false;
    
    /**
     * Describes cardinality of source table (true if source fields point to unique record)
     * @var bool 
     */
    var $srcIsUnique = false;
    
    /**
     * Describes cardinality of destination table (true if destination fields point to unique record)
     */
    var $destIsUnique = false;
    
    /**
     * Name of variable in source object that contains reference to destination object (if $destUnique is false, it has to be an array with references) 
     */
    var $srcVarName = false;
    
    /**
     * Name of variable in source object that contains link records for N-N (with midTable) links
     * @var string
     */
    var $srcNNIdsVarName = false;
    
    /**
     * If $srcIsUnique is false and $srcVarName property in the source object is array, whether keys in that array match to keys of the database records 
     */
    var $srcKeyMatch = false;
    
    /**
     * Name of variable in destination object that contains reference to source object (if $srcIsMultiple is true, it has to be an array with references)
     */
    var $destVarName = false;
    
    /**
     * Name of variable in destination object that contains link records for N-N (with midTable) links
     * @var string
     */
    var $destNNIdsVarName = false;
    
    /**
     * @var bool This relation is outgoing from source object (belongs to source table)
     */
    var $srcOutgoing = false;
    
    /**
     * @var string One of AMR_DELETE constants - what should be performed when handleSrcDeleted(& $src) is called 
     */
    var $onDeleteSrc = AMR_DELETE_NO_ACTION;
    
    /**
     * @var mixed Parameter(s) for handling src object deletion (format and meaning depends on $this->onDeleteSrc value)
     */
    var $onDeleteSrcParam = null;

    /**
     * @var string One of AMR_DELETE constants - what should be performed when handleDestDeleted(& $dest) is called
     */
    var $onDeleteDest = AMR_DELETE_NO_ACTION;
    
    /**
     * @var mixed Parameter(s) for handling dest object deletion (format and meaning depends on $this->onDeleteDest value)
     */
    var $onDeleteDestParam = false;
    
    /**
     * Name of property of source object where count of linked destination records is temporarily stored (or FALSE to not to use this feature)
     * @var bool|string
     */
    var $srcCountVarName = false;
    
    /**
     * Name of property of destination object where count of linked source records is temporarily stored (or FALSE to not to use this feature)
     * @var bool|string
     */
    var $destCountVarName = false;
    
    /**
     * If $destIsUnique is false and $destVarName property in the destination object is array, whether keys in that array match to keys of the database records 
     */
    var $destKeyMatch = false;

    /**
     * Whether getSrc() and getDest() should return multiple results as collections
     * @var bool
     */
    var $returnCollections = false;
    
    /**
     * Default $matchKeys value for getSrc() and getDest() if multiple result should be returned
     */
    var $matchKeys = false;
    
    /**
     * @var Ae_Legacy_Database
     */
    var $database = false;
    
    var $destOrdering = false;
    
    var $srcOrdering = false;
    
    var $srcExtraJoins = false;
    
    var $destExtraJoins = false;
    
    var $srcWhere = false;
    
    var $destWhere = false;
    
    /**
     * Flipped links (from destination to midtable to source table) 
     */
    var $_fieldLinksRev = false;
    var $_fieldLinksRev2 = false;
    
    /**
     * @var Ae_Model_Mapper
     */
    var $_srcMapper = false;
    
    /**
     * @var Ae_Model_Mapper
     */
    var $_destMapper = false;
    
    // ------------------------ PUBLIC METHODS -----------------------

    /**
     * @return Ae_Model_Relation
     */
    function & factory($config = array()) {
        return Ae_Autoparams::factory($config, 'Ae_Model_Relation');
    }
    
    function setSrcMapper(Ae_Model_Mapper $srcMapper) {
        $this->_srcMapper = $srcMapper;
        $this->srcMapperClass = $srcMapper->getId();
        if (!$this->database) $this->database = $this->_srcMapper->getDatabase();
    }
    
    function setDestMapper(Ae_Model_Mapper $destMapper) {
        $this->_destMapper = $destMapper;
        if (!$this->database) $this->database = $this->_destMapper->getDatabase();
    }
    
    function setApplication(Ae_Application $application) {
        $this->application = $application;
        if (!$this->database) $this->database = $this->application->getLegacyDatabase();
    }
    
    /**
     * @return Ae_Application
     */
    function getApplication() {
        return $this->application;
    }
    
    /**
     * @param array $config Array prototype of the object
     */
    function __construct ($config = array()) {
        Ae_Autoparams::setObjectProperty($this, $config);
        
        if (($this->srcTableName === false) && strlen($this->srcMapperClass)) {
            $this->_srcMapper = & Ae_Model_Mapper::getMapper($this->srcMapperClass, $this->application);
            $this->srcTableName = $this->_srcMapper->tableName;
            if ($this->srcOrdering === false) $this->srcOrdering = $this->_srcMapper->getDefaultOrdering();
        }
        if (($this->destTableName === false) && strlen($this->destMapperClass)) {
            $this->_destMapper = & Ae_Model_Mapper::getMapper($this->destMapperClass, $this->application);
            $this->destTableName = $this->_destMapper->tableName;
            if ($this->destOrdering === false) $this->destOrdering = $this->_destMapper->getDefaultOrdering();
        }
        
        if ($this->database === false) {
            if ($this->_srcMapper) $this->database = $this->_srcMapper->getDatabase();
            elseif ($this->_destMapper) $this->database = $this->_destMapper->getDatabase();
        }
        
        if (!$this->fieldLinks) trigger_error('fieldLinks must be specified', E_USER_ERROR);
            else $this->_fieldLinksRev = array_flip($this->fieldLinks);
        
        if (strlen($this->midTableName)) {
            if (!$this->fieldLinks2) trigger_error('fieldLinks2 must be specified with midTableName', E_USER_ERROR);
                else $this->_fieldLinksRev2 = array_flip($this->fieldLinks2);
        }
            
        
    }
    
    // ----------------------- getDest... family ---------------------
    
    /**
     * Returns one or more destination objects for given source object
     * @param Ae_Model_Data|object $srcData
     * @param int $matchMode How keys of result array are composed (can be AMR_PLAIN_RESULT, AMR_RECORD_KEYS, AMR_ORIGINAL_KEYS, AMR_ALL_ORIGINAL_KEYS) -- only if srcData is array
     * @param mixed $defaultValue Value to be returned for missing rows when $matchMode is AMR_ALL_ORIGINAL_KEYS or when $srcData is an object. By default it is null if $this->destIsUnique and empty array() if not.
     * @return Ae_Model_Data|array
     */
    function getDest (& $srcData, $matchMode = AMR_PLAIN_RESULT, $defaultValue = null) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        $hasDef = func_num_args() >= 3;
        $res = & $this->_getSrcOrDest ($srcData, $matchMode, $defaultValue, $hasDef, $this->fieldLinks, $this->fieldLinks2, $this->destIsUnique, $this->destTableName, '_destInstance', $this->destOrdering, $this->destExtraJoins, $this->destWhere);
        return $res;
    }
    
    /**
     * @param Ae_Model_Data|object|array $srcData
     * @return Ae_Model_Collection
     */
    function getDestCollection(& $srcData, $matchKeys = false) {
        trigger_error ('Method not implemented yet', E_USER_ERROR);
    }
    
    /**
     * @param Ae_Model_Collection $srcCollection
     * @return Ae_Model_Collection 
     */
    function getDestCollectionForCollection(& $srcCollection) {
        trigger_error ('Method not implemented yet', E_USER_ERROR);
    }
    
    // ----------------------- getSrc... family ---------------------
    
    /**
     * Returns one or more source objects for given destination object
     * @param Ae_Model_Data|object $destData
     * @param int $matchMode How keys of result array are composed (can be AMR_PLAIN_RESULT, AMR_RECORD_KEYS, AMR_ORIGINAL_KEYS, AMR_ALL_ORIGINAL_KEYS) -- only if srcData is array
     * @param mixed $defaultValue Value to be returned for missing rows when $matchMode is AMR_ALL_ORIGINAL_KEYS or when $srcData is an object. By default it is null if $this->destIsUnique and empty array() if not.
     * @return Ae_Model_Data|array
     */ 
    function getSrc (& $destData, $matchMode = AMR_PLAIN_RESULT, $defaultValue = null) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        $hasDef = func_num_args() >= 3;
        $res = & $this->_getSrcOrDest ($destData, $matchMode, $defaultValue, $hasDef, $this->_fieldLinksRev, $this->_fieldLinksRev2, $this->srcIsUnique, $this->srcTableName, '_srcInstance', $this->srcOrdering, $this->srcExtraJoins, $this->srcWhere);
        return $res;
    }
    
    /**
     * @param Ae_Model_Data|object|array $destData
     * @return Ae_Model_Collection
     */
    function getSrcCollection(& $destData, $matchKeys = false) {
        trigger_error ('Method not implemented', E_USER_ERROR);
    }
    
    /**
     * @param Ae_Model_Collection $destCollection
     * @return Ae_Model_Collection 
     */
    function getSrcCollectionForCollection(& $destCollection) {
        trigger_error ('Method not implemented', E_USER_ERROR);
    }
    
    // ------------------------ count / delete / load methods -------
    
    function countDest (& $srcData, $separate = true, $matchMode = AMR_PLAIN_RESULT) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        return $this->_countSrcOrDest($srcData, $separate, $matchMode, $this->fieldLinks, $this->fieldLinks2, $this->destIsUnique, $this->destTableName);
    }
    
    function countSrc (& $destData, $separate = true, $matchMode = AMR_PLAIN_RESULT) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        return $this->_countSrcOrDest($destData, $separate, $matchMode, $this->_fieldLinksRev, $this->_fieldLinksRev2, $this->srcIsUnique, $this->srcTableName);
    }
    
    function deleteDest (& $srcData) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        return $this->_deleteSrcOrDest($srcData, $this->fieldLinks, $this->fieldLinks2, $this->destTableName, $this->destWhere);
    }
    
    function deleteSrc (& $destData) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        return $this->_deleteSrcOrDest($destData, $this->_fieldLinksRev, $this->_fieldLinksRev2, $this->srcTableName, $this->srcWhere);
    }
    
    function loadDest (& $srcData, $ignoreLoaded = true, $biDirectional = true) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        //if (!$this->srcVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $srcVarName is not set');
        $defaultValue = $this->destIsUnique? null : array();
        $biDirectional = $biDirectional && strlen($this->destVarName);
        $res = $this->_loadSrcOrDest ($srcData, $defaultValue, $this->srcVarName, $this->destVarName, $ignoreLoaded, $biDirectional,
            $this->fieldLinks, $this->fieldLinks2, $this->destIsUnique, $this->srcIsUnique, $this->destTableName, '_destInstance', $this->destOrdering, $this->destExtraJoins, $this->destWhere);
        return $res;
    }
    
    function loadSrc (& $destData, $ignoreLoaded = true, $biDirectional = true) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        if (!$this->destVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $destVarName is not set');
        $defaultValue = $this->srcIsUnique? null : array();
        $biDirectional = $biDirectional && strlen($this->srcVarName);
        return $this->_loadSrcOrDest ($destData, $defaultValue, $this->destVarName, $this->srcVarName, $ignoreLoaded, $biDirectional, 
            $this->_fieldLinksRev, $this->_fieldLinksRev2, $this->srcIsUnique, $this->destIsUnique, $this->srcTableName, '_srcInstance', $this->srcOrdering, $this->srcExtraJoins, $this->srcWhere);
    }
    
    function loadDestNNIds(& $srcData, $ignoreLoaded = true) {
        if (!$this->midTableName) trigger_error("This function is only applicable to relations with midTableName set", E_USER_ERROR);
        if (!strlen($this->srcNNIdsVarName)) trigger_error("Property \$srcNNIdsVarName should be set to non-empty string to use this method", E_USER_ERROR); 
        $relConfig = array(
            'database' => & $this->database,
            'srcTableName' => $this->srcTableName,
            'srcMapperClass' => $this->srcMapperClass,
            'srcVarName' => $this->srcNNIdsVarName,
            
            'destTableName' => $this->midTableName,
            'fieldLinks' => $this->fieldLinks,
        
            'srcIsUnique' => $this->srcIsUnique,
            'destIsUnique' => false,
        );
        $rel = new Ae_Model_Relation($relConfig);
        $rel->loadDest($srcData, $ignoreLoaded);
        $this->_fixNNIds($srcData, array_keys($this->fieldLinks2), $this->srcNNIdsVarName);
    }
    
    function loadSrcNNIds(& $destData, $ignoreLoaded = true) {
        if (!$this->midTableName) trigger_error("This function is only applicable to relations with midTableName set", E_USER_ERROR);
        if (!strlen($this->destNNIdsVarName)) trigger_error("Property \$destNNIdsVarName should be set to non-empty string to use this method", E_USER_ERROR);
        $relConfig = array(
            'database' => & $this->database,
            'destTableName' => $this->destTableName,
            'destMapperClass' => $this->destMapperClass,
            'destVarName' => $this->destNNIdsVarName,
            
            'srcTableName' => $this->midTableName,
            'fieldLinks' => $this->fieldLinks2,
        
            'srcIsUnique' => false,
            'destIsUnique' => $this->srcIsUnique,
        );
        $rel = new Ae_Model_Relation($relConfig);
        $rel->loadSrc($destData, $ignoreLoaded);
        $this->_fixNNIds($destData, array_values($this->fieldLinks), $this->destNNIdsVarName);
    }
    
    /**
     * Counts destination objects and stores result in $srcCountVarName of each corresponding $srcData object
     */
    function loadDestCount (& $srcData, $ignoreLoaded = true) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        if (!$this->srcCountVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $srcCountVarName is not set');
        return $this->_loadSrcOrDestCount ($srcData, $this->srcCountVarName, $ignoreLoaded, 
            $this->fieldLinks, $this->fieldLinks2, $this->destTableName);
    }
    
    /**
     * Counts source objects and stores result in $destCountVarName of each corresponding $destData object
     */
    function loadSrcCount (& $destData, $ignoreLoaded = true) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        if (!$this->destCountVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $destCountVarName is not set');
        return $this->_loadSrcOrDestCount ($destData, $this->destCountVarName, $ignoreLoaded, 
            $this->_fieldLinksRev, $this->_fieldLinksRev2, $this->srcTableName);
    }

    /**
     * Performs corresponding cascade actions when src object(s) are to be deleted.
     * @see Ae_Model_Relation::$onDeleteSrc
     * @see Ae_Model_Relation::$onDeleteSrcParam
     */
    function handleSrcDeleted (& $srcData) {
        trigger_error ('Method not implemented yet', E_USER_ERROR);
    }
    
    /**
     * Performs corresponding cascade actions when dest object(s) are to be deleted.
     * @see Ae_Model_Relation::$onDeleteDest
     * @see Ae_Model_Relation::$onDeleteDestParam
     */
        function handleDestDeleted (& $destData) {
        trigger_error ('Method not implemented yet', E_USER_ERROR);
    }
    
    function getDestJoin ($srcAlias = false, $destAlias = false, $type = 'left', $midAlias = false) {
        if (!$this->midTableName) {
            $res = $this->_getJoin($type, $srcAlias, $this->destTableName, $destAlias, $this->fieldLinks);
        } else {
            if ($midAlias === false) $midAlias = $this->midTableAlias;
            $res = $this->_getJoin($type, $srcAlias, $this->midTableName, $midAlias, $this->fieldLinks);
            $res .= ' '.$this->_getJoin($joinType, $midAlias, $this->destTableName, $destAlias, $this->fieldLinks2);
        }
        return $res;
    }
    
    function getSrcJoin ($srcAlias = false, $destAlias = false, $type = 'left', $midAlias = false) {
        if (!$this->midTableName) {
            $res = $this->_getJoin($type, $destAlias, $this->srcTableName, $srcAlias, $this->_fieldLinksRev);
        } else {
            if ($midAlias === false) $midAlias = $this->midTableAlias;
            $res = $this->_getJoin($type, $destAlias, $this->midTableName, $midAlias, $this->_fieldLinksRev2);
            $res .= ' '.$this->_getJoin($joinType, $midAlias, $this->srcTableName, $srcAlias, $this->_fieldLinksRev);
        }
        return $res;
    }
    
    function getCritForSrcOfDest (& $dest, $srcAlias = '', $default = '0') {
        if ($this->midTableName) trigger_error ("This method cannot be used when midTableName is set!", E_USER_ERROR);
        if (!$this->srcTableName) trigger_error ("Cannot ".__FUNCTION__."() with non-persistent source!", E_USER_ERROR);
        $res = $this->_makeCritForSrcOrDest($dest, $srcAlias, $this->_fieldLinksRev, $default);
        return $res;        
    }
    
    function getCritForDestOfSrc (& $src, $destAlias = '', $default = '0') {
        if ($this->midTableName) trigger_error ("This method cannot be used when midTableName is set!", E_USER_ERROR);
        if (!$this->destTableName) trigger_error ("Cannot ".__FUNCTION__."() with non-persistent destination!", E_USER_ERROR);
        $res = $this->_makeCritForSrcOrDest($src, $destAlias, $this->fieldLinks, $default);
        return $res;        
    }
    
    // ------------------------ PRIVATE METHODS -----------------------
    /**
     * Extracts keys from source array and makes map (recordKeys => srcArrayKey). Format of $map will be array($keys, $sourceKey). By traversing the map later,
     * one can find corresponding records in the right table. 
     */
    function _makeMapAndGetAllValues(& $source, & $map, & $values, $keys) {
        $map = array();
        $values = array();
        foreach(array_keys($source) as $k) {
            $srcItem = & $source[$k];
            $vals = $this->_getValues($srcItem, $keys, false, false);
            //if (!$this->_isFull($vals)) continue;
            //foreach ($vals as $v) if ($v === false) continue 2;
            //Ae_Util::simpleSetArrayByPath($map, $vals, $source[$k], true);
            $map[] = array($vals, $k);
            $values[] = $vals;
        }
    }
    
    function _makeMapAndGetAllValuesIfVarNotFalse(& $source, & $map, & $values, $keys, $varName) {
        $map = array();
        $values = array();
        foreach(array_keys($source) as $k) {
            $srcItem = & $source[$k];
            if (is_array($srcItem)) $varFalse = isset($srcItem[$varName]) && ($srcItem[$varName] === false);
                else $varFalse = isset($srcItem->$varName) && ($srcItem->$varName === false);
            if (!$varFalse) {    
                $vals = $this->_getValues($srcItem, $keys, false, false);
                //Ae_Util::simpleSetArrayByPath($map, $vals, $source[$k], true);
                $map[] = array($vals, $k);
                $values[] = $vals;
            }
        }
    }
    
    function _getAllValues(& $source, & $values, $keys) {
        $values = array();
        foreach (array_keys($source) as $k) {
//            if ($this->_isFull($gv = $this->_getValues($source[$k], $keys, false, false))) $values[] = $gv;
            $values[] = $this->_getValues($source[$k], $keys, false, false);
        }
    }
    
    function & _getSrcOrDest (& $data, $matchMode, $defaultValue, $hasDefaultValue, $fieldLinks, $fieldLinks2, $isUnique, $tableName, $instanceFunc, $ordering, $extraJoins, $extraWhere) {
        $keys = array_keys($fieldLinks);
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            if ($matchMode & AMR_ORIGINAL_KEYS) {
                $map = array();
                $this->_makeMapAndGetAllValues($data, $map, $values, $keys);
            } else {
                $this->_getAllValues($data, $values, $keys);
            }
            $rows = $this->_getWithValues($values, $this->midTableName? $fieldLinks2 : Ae_Util::array_values($fieldLinks), true, $isUnique, $matchMode > 0, $tableName, $instanceFunc, false, false, $this->midTableName, $ordering, $extraJoins, $extraWhere);
            if ($matchMode & AMR_ORIGINAL_KEYS) {
                if (!$hasDefaultValue) $defaultValue = $isUnique? null : array();
                $res = array();
                if (count($keys) === 1) {
                    foreach ($map as $m) {
                        if ($matchMode == AMR_ALL_ORIGINAL_KEYS) {
                            if (isset($rows[$rowKey = $m[0][0]])) $res[$m[1]] = & $rows[$rowKey]; else $res[$m[1]] = $defaultValue; 
                        } else {
                            if (isset($rows[$rowKey = $m[0][0]])) $res[$m[1]] = & $rows[$rowKey];
                        } 
                    }
                } else {
                    $rowPath = $m[0];
                    foreach ($map as $m) {
                        $row = & Ae_Util::simpleGetArrayByPath($rows, $rowPath, false);
                        if ($row !== false)
                            $res[$m[1]] = & $row;
                        elseif ($matchMode == AMR_ALL_ORIGINAL_KEYS) 
                            $res[$m[1]] = $row; 
                    }
                }
            } else {
                $res = $rows;
            }
        } elseif (is_a($data, 'Ae_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            $values = $this->_getValues($data, $keys, false, false);
            $rows = $this->_getWithValues($values, $this->midTableName? $fieldLinks2 : Ae_Util::array_values($fieldLinks), false, $isUnique, $matchMode > 0, $tableName, $instanceFunc, false, false, $this->midTableName, $ordering, $extraJoins, $extraWhere);
            if ($rows) $res = $rows;
                else {
                    if (!$hasDefaultValue) $defaultValue = $this->destIsUnique? null : array();
                    $res = $defaultValue;
                }
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    function _countSrcOrDest (& $data, $separate = true, $matchMode = AMR_PLAIN_RESULT, $fieldLinks, $fieldLinks2, $isUnique, $tableName) {
        $keys = array_keys($fieldLinks);
        if (is_array($data)) { // we assume that this is array of objects or array of rows
            $values = array();
            if ($separate && ($matchMode & AMR_ORIGINAL_KEYS)) {
                $map = array();
                $this->_makeMapAndGetAllValues($data, $map, $values, $keys);
            } else {
                $this->_getAllValues($data, $values, $keys);
            }
            $counts = $this->_countWithValues($values, $this->midTableName? $fieldLinks2 : Ae_Util::array_values($fieldLinks), true, $separate, $matchMode > 0, $tableName, false, false, $this->midTableName);
            if (!$separate) {
                $res = $counts;
            } else {
                if ($matchMode & AMR_ORIGINAL_KEYS) {
                    $res = array();
                    if (count($keys) === 1) {
                        foreach ($map as $m) {
                            if ($matchMode == AMR_ALL_ORIGINAL_KEYS) {
                                if (isset($counts[$countKey = $m[0][0]])) $res[$m[1]] = & $counts[$countKey]; else $res[$m[1]] = 0; 
                            } else {
                                if (isset($counts[$countKey = $m[0][0]])) $res[$m[1]] = & $counts[$countKey];
                            } 
                        }
                    } else {
                        $countPath = $m[0];
                        foreach ($map as $m) {
                            $countValue = & Ae_Util::simpleGetArrayByPath($counts, $countPath, false);
                            if ($countValue !== false)
                                $res[$m[1]] = & $countValue;
                            elseif ($matchMode == AMR_ALL_ORIGINAL_KEYS) 
                                $res[$m[1]] = 0; 
                        }
                    }
                } else {
                    $res = $counts;
                }
            }
        } elseif (is_a($data, 'Ae_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            $values = $this->_getValues($data, $keys, false, false);
            $res = $this->_countWithValues($values, $this->midTableName? $fieldLinks2 : Ae_Util::array_values($fieldLinks), false, false, false, $destTableName, false, false, $this->midTableName);
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    function _deleteSrcOrDest (& $data, $fieldLinks, $fieldLinks2, $tableName, $where = false) {
        $keys = array_keys($fieldLinks);
        if (is_object($data)) {
            $xd = array(& $data);
            return $this->_deleteSrcOrDest($xd, $fieldLinks, $fieldLinks2, $tableName, $where);
        }
        if (is_array($data)) { // we assume that this is array of objects or array of rows
            $values = array();
            $this->_getAllValues($data, $values, $keys);
            if ($this->midTableName) {
                $midValues = $this->_getWithValues($values, Ae_Util::array_values($fieldLinks), true, false, false, $this->midTableName, '_rowInstance', false, false, '', false, $where );
                $rightKeyValues = array();
                $this->_getAllValues($midValues, $rightKeyValues, array_keys($fieldLinks2));
                $res = true;
                $this->database->startTransaction();
                if (!$this->_deleteWithValues($values, true, array_values($fieldLinks))) $res = false;
                if (!$this->_deleteWithValues($midValues, true, Ae_Util::array_values($fieldLinks2))) $res = false;
                if (!$res) $this->database->rollback();
                    else $this->database->commit();
            } else {
                $res = $this->_deleteWithValues($values, true, Ae_Util::array_values($fieldLinks), $tableName, false, $where);
            }
        } elseif (is_a($data, 'Ae_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    function _loadSrcOrDest (& $data, $defaultValue, $varName, $otherVarName, $ignoreLoaded, $biDirectional, $fieldLinks, $fieldLinks2, $isUnique, $otherIsUnique, $tableName, $instanceFunc, $ordering = false, $extraJoins = false, $extraWhere = false) {
        $keys = array_keys($fieldLinks);
        $res = array();
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $map = array();
            if ($ignoreLoaded) {
                $this->_makeMapAndGetAllValues($data, $map, $values, $keys);
            } else {
                $this->_makeMapAndGetAllValuesIfVarNotFalse($data, $map, $values, $keys, $varName);
            }
            $rows = $this->_getWithValues($values, $this->midTableName? array($fieldLinks, $fieldLinks2) : Ae_Util::array_values($fieldLinks), true, $isUnique, true, $tableName, $instanceFunc, false, false, $this->midTableName, $ordering, $extraJoins, $extraWhere);
            if ($this->midTableName) {
            }
            if (count($keys) === 1) {
                foreach ($map as $m) {
                    $dataKey = $m[1];
                    if (isset($rows[$rowKey = $m[0][0]])) {
                        $toSet = & $rows[$rowKey];
                        if ($biDirectional) $this->_linkBack($rows[$rowKey], $data[$dataKey], $otherVarName, !$isUnique, $otherIsUnique); 
                    } else $toSet = & $defaultValue;
                    $this->_setRef($data[$dataKey], $varName, $toSet);
                }
            } else {
                $add = $this->midTableName && $otherIsUnique; 
                foreach ($map as $m) {
                    $rowPath = $m[0];
                    $dataKey = $m[1];
                    $row = & Ae_Util::simpleGetArrayByPath($rows, $rowPath, false);
                    if ($row !== false) {
                       $toSet = & $row;
                       if ($biDirectional) $this->_linkBack($row, $data[$dataKey], $otherVarName, !$isUnique, $otherIsUnique);
                    }
                    else
                    $toSet = & $defaultValue;
                    $this->_setRef($data[$dataKey], $varName, $toSet, $add);
                }
            }
            $res = & $rows;
        } elseif (is_a($data, 'Ae_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            if (!($ignoreLoaded && isset($data->$varName) && $data->$varName !== false)) {  
                $values = $this->_getValues($data, $keys, false, false);
                $rows = $this->_getWithValues($values, $this->midTableName? array($fieldLinks, $fieldLinks2) : Ae_Util::array_values($fieldLinks), false, $isUnique, false, $tableName, $instanceFunc, false, false, $this->midTableName, $ordering, $extraJoins, $extraWhere);
                if ($rows) {
                    $toSet = & $rows;
                    if ($biDirectional) $this->_linkBack($rows, $data, $otherVarName, !$isUnique, $otherIsUnique);
                } else $toSet = $defaultValue;
                if (strlen($varName)) $data->$varName = & $toSet;
                if ($isUnique) $res = array(& $rows); else $res = $rows;
            }
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    function _loadSrcOrDestCount (& $data, $varName, $ignoreLoaded, $fieldLinks, $fieldLinks2, $tableName) {
        $keys = array_keys($fieldLinks);
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $map = array();
                
            if ($ignoreLoaded) {
                $this->_makeMapAndGetAllValues($data, $map, $values, $keys);
            } else {
                $this->_makeMapAndGetAllValuesIfVarNotFalse($data, $map, $values, $keys, $varName);
            }
                        
            $counts = $this->_countWithValues($values, $this->midTableName? $fieldLinks2 : Ae_Util::array_values($fieldLinks), true, true, true, $tableName, false, false, $this->midTableName);
            if (count($keys) === 1) {
                foreach ($map as $m) {
                    $dataKey = $m[1];
                    if (isset($counts[$countKey = $m[0][0]])) {
                        $toSet = $counts[$countKey];
                    } else $toSet = 0;
                    $this->_setVal($data[$dataKey], $varName, $toSet); 
                }
            } else {
                $countPath = $m[0];
                $dataKey = $m[1];
                foreach ($map as $m) {
                    $count = & Ae_Util::simpleGetArrayByPath($counts, $countPath, 0);
                    $this->_setLoaded($data[$dataKey], $varName, $count);
                }
            }
        } elseif (is_a($data, 'Ae_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            if (!($ignoreLoaded && isset($data->$varName) && $data->$varName !== false)) {  
                $values = $this->_getValues($data, $keys, false, false);
                $data->$varName = $this->_countWithValues($values, $this->midTableName? $fieldLinks2 : Ae_Util::array_values($fieldLinks), false, false, false, $tableName, false, false, $this->midTableName);
            }
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
    }
    
    function & _getWithValues ($values, $keys, $multipleValues, $unique, $byKeys, $tableName, $ifun, $orderByKeys = false, $retSql = false, $midTableName = '', $ordering = false, $extraJoins = false, $extraWhere = false) {
        if (!$extraJoins) {
            $ta = '';
            $asTa = '';
            $cols = '*';
        } else {
            $ta = 't';
            $asTa = 'AS t';
            $cols = 't.*'; 
        }
        if (!$multipleValues) $values = array($values);
            elseif (!count($values)) {
                $res = array();
                return $res;
            }
        if ($midTableName) {
            $allKeys = $keys;
            $selKeys = array_values($allKeys[0]);
            $keys = array_values($allKeys[0]);            
            //$selKeys = array_keys($keys);
            $lta = '_mid_.';
            $fromWhere = ' FROM '.$this->database->NameQuote($midTableName).' AS _mid_ '.$this->_getJoin('INNER', '_mid_', $tableName, $ta, $allKeys[1]);
            $crit = $this->_makeSqlCriteria($values, $selKeys, '_mid_');
        } else {
            $fromWhere = ' FROM '.$this->database->NameQuote($tableName).$asTa;
            $selKeys = $keys;    
            $lta = $this->database->NameQuote($tableName).'.';
            $crit = $this->_makeSqlCriteria($values, $keys, $ta);
        }
        if ($extraJoins) $fromWhere .= ' '.$extraJoins;
        $fromWhere .= ' WHERE ('.$crit.')';
        if ($extraWhere) $fromWhere .= ' AND '.$extraWhere; 
        
        $qKeys = array();
        foreach ($keys as $key) $qKeys[] = $lta.$this->database->NameQuote($key);
        $sKeys = implode(', ', $qKeys);
        $sql = 'SELECT ';
        if ($midTableName) {
            $sql .= 'DISTINCT '.$sKeys.', '.$this->database->NameQuote($ta? $ta: $tableName).'.*'.$fromWhere;
        } else $sql = 'SELECT '.$cols.' '.$fromWhere;
        if ($orderByKeys) {
            $ord = array();
            $sql .= ' ORDER BY '.$qKeys;
            if ($ordering) $sql .= ', '.$ordering; 
        } elseif ($ordering) {
            $sql .= ' ORDER BY '.$ordering;
        }
        if ($retSql) return $sql;
        $res = array();
        
        
        if ($midTableName && $byKeys && !$unique) {
            //return $res;
        }
        
        $this->database->setQuery($sql);
        $rr = $this->database->getResultResource();
        
        if ($midTableName) {
            //$xti = xdebug_time_index();
            $fi = $this->database->getFieldsInfo($rr);
            $rightKeyFields = array_values($allKeys[1]);
            if ($byKeys) {
                $prefix = $this->database->getPrefix();
                $tn = str_replace('#__', $prefix, $tableName);
                if ($ta) $tn = $ta;
                if (count($keys) === 1) {
                    $key = $keys[0];
                    if ($unique) {
                        while($row = $this->database->fetchAssocByTables($rr, $fi)) 
                            $res[$row['_mid_'][$key]] = & $this->$ifun ($row[$tn]);
                    } else {
                        //$res = array();
                        //return $res;
                        $instances = array();
                        if (count($rightKeyFields) == 1) {
                            $kf = $rightKeyFields[0];
                            while($row = $this->database->fetchAssocByTables($rr, $fi)) {
                                $rowKey = $row[$tn][$kf];
                                if (isset($instances[$rowKey])) $instance = & $instances[$rowKey];
                                else {
                                    $instance = & $this->$ifun ($row[$tn]);
                                    $instances[$rowKey] = & $instance;
                                }
                                $res[$row['_mid_'][$key]][] = & $instance;
                            }
                        } else {
                            while($row = $this->database->fetchAssocByTables($rr, $fi)) {
                                $rowKey = $this->_getValues($row[$tn], $rightKeyFields);
                                $instance = & Ae_Util::simpleGetArrayByPath($instances, $rowKey, false);
                                if (!$instance) {
                                    Ae_Util::simpleSetArrayByPath($instances, $rowKey, $instance = & $this->$ifun ($row[$tn]));
                                }
                                $res[$row['_mid_'][$key]][] = & $instance;
                            }
                        }
                    }
                } else {
                    while($row = $this->database->fetchAssocByTables($rr, $fi)) {
                        $instance = & $this->$ifun ($row[$tableName]);
                        Ae_Util::simpleSetArrayByPath($res, $row['_mid_'], $instance, $unique);
                    }
                }
            } else {
                while($row = $this->database->fetchAssoc($rr)) { 
                    $res[] = & $this->$ifun ($row);     
                }
            }
        } else {
        
            if ($byKeys) {
                if (count($keys) === 1) {
                    $key = $keys[0];
                    if ($unique) {
                        while($row = $this->database->fetchAssoc($rr)) {
                            $res[$row[$key]] = & $this->$ifun ($row);
                        }
                    } else {
                        while($row = $this->database->fetchAssoc($rr)) { 
                            $res[$row[$key]][] = & $this->$ifun ($row);
                        }
                    }
                } else {
                    while($row = $this->database->fetchAssoc($rr)) {
                        $instance = & $this->$ifun ($row);
                        $this->_putRowToArray($row, $instance, $res, $keys, $unique);        
                    }
                }
                
            } else {
                
                while($row = $this->database->fetchAssoc($rr)) {
                    // that's it - The Circular Reference Creator -- 26.03.2009
                    $res[] = & $this->$ifun ($row);     
                }
            }
        
        }
        $this->database->freeResultResource($rr);
        if (!$multipleValues && $unique && count($res)) {
            // $res = & $res[0] // crashes PHP 4.3.9, works in PHP 4.4.x
            $tmp = & $res[0];
            return $tmp;
        }
        
        // WHAT'S THIS???
//        foreach (array_keys($res) as $k) {
//          unset($res[$k]);
//        }
        return $res;
    }
    
    // TODO: optimize _countWithValues and _getWithValues to place instances into nested array faster when resultset is ordered
    
    function _countWithValues($values, $keys, $multipleValues, $separateCounts, $byKeys, $tableName, $orderByKeys = false, $retSql = false, $midTableName = '') {
        if (!$multipleValues) $values = array($values);
            elseif (!count($values)) return $separateCounts? array() : 0;
        if ($midTableName) {
            $fromWhere = ' FROM '.$this->database->NameQuote($midTableName).' AS _mid_ '.$this->_getJoin('INNER', '_mid_', $tableName, '', $keys);
            //foreach($midKeysMap as $rightKey) $selKeys[] = $rightKey;
            $selKeys = array_keys($keys);
            $keys = array_keys($keys); 
            $lta = '_mid_.';
            $crit = $this->_makeSqlCriteria($values, $keys, '_mid_');
        } else {
            $fromWhere = ' FROM '.$this->database->NameQuote($tableName);
            $selKeys = $keys;    
            $lta = $this->database->NameQuote($tableName).'.';
            $crit = $this->_makeSqlCriteria($values, $keys, '');
        }
        $fromWhere .= ' WHERE '.$crit;
        
        if (!$separateCounts) {
            $sql = 'SELECT COUNT(*) '.$fromWhere;
            if ($retSql) return $sql;
            $this->database->setQuery($sql);
            return $this->database->loadResult();
        }
        $qKeys = array();
        foreach ($keys as $key) $qKeys[] = $lta.$this->database->NameQuote($key);
        $sKeys = implode(', ', $qKeys);
        $i = 0;
        while(in_array($cntColumn = '__count__'.$i, $keys)) $i++; 
        $sql = 'SELECT '.$sKeys.', COUNT(*) AS '.$this->database->NameQuote($cntColumn).$fromWhere.' GROUP BY '.$sKeys;
        if ($orderByKeys) $sql .= ' ORDER BY '.$sKeys; 
        if ($retSql) return $sql;
        $res = array();
        $this->database->setQuery($sql);
        $rr = $this->database->getResultResource();
        if ($byKeys && $multipleValues) {
            if (count($selKeys) === 1) {
                $key = $selKeys[0];
                while($row = $this->database->fetchAssoc($rr)) 
                    $res[$row[$key]] = $row[$cntColumn];        
            } else {
                while($row = $this->database->fetchAssoc($rr)) {
                    $this->_putRowToArray($row, $row[$cntColumn], $res, $selKeys, true);        
                }
            }
        } else {
            while($row = $this->database->fetchAssoc($rr)) 
                $res[] = $row[$cntColumn];     
        }
        $this->database->freeResultResource($rr);
        if (!$multipleValues) $res = & $res[0];
        return $res;
    }
    
    function _deleteWithValues($values, $multipleValues, $keys, $tableName, $retSql = false, $where = false) {
        $crit = $this->_makeSqlCriteria($values, $keys);
        if (!$multipleValues) $values = array($values);
            elseif (!count($values)) return 0;
        $sql =  'DELETE FROM '.$this->database->NameQuote($tableName).' WHERE ('.$crit.')';
        if (strlen($where)) $sql .= ' AND '.$where;
        if ($retSql) return $sql;
        $this->database->setQuery($sql);
        return $this->database->query();
    }

    function & _destInstance($row) {
        $res = & $this->_recordInstance($row, $this->destRecordClass, $this->_destMapper);
        return $res;
    }
    
    function & _srcInstance($row) {
        $res = & $this->_recordInstance($row, $this->srcRecordClass, $this->_srcMapper);
        return $res;
    }
    
    /**
     * Makes SQL criteria to select multiple records with given key values and names.
     * @param array $values Array of single or composite keys. Note: composite keys are expected to be numeric arrays ordered as $keyFields. No checks are performed!
     * @param array|string $keyFields Name of key field(s). If $keyfields is string, $values elements should be scalars, otherwise arrays are expected
     * @param string alias Table alias
     * @param mixed $default Crtieria to return when $values is an empty array
     * @return string
     **/
    function _makeSqlCriteria($values, $keyFields, $alias = '', $default = '0') {
        return $this->database->sqlKeysCriteria($values, $keyFields, $alias, $default);
    }
    
    /**
     * Creates JOIN clause ("$joinType JOIN $rightTable AS $rightAlias ON $leftAlias.$key0 = $rightAlias.$field0 AND $leftAlias.$key1 = $rightAlias.$field1"), 
     * $keyN and $fieldN are taken from $fieldNames 
     */
    function _getJoin ($joinType, $leftAlias, $rightTable, $rightAlias, $fieldNames) {
        $db = & $this->database;
        $la = $db->NameQuote($leftAlias);
        $ra = $db->NameQuote($rightAlias);
        $res = $joinType.' JOIN '.$db->NameQuote($rightTable);
        if ($rightAlias) $res .= ' AS '.$ra.' ON ';
            else {
                $res .= ' ON ';
                $ra = $db->NameQuote($rightTable);
            }
        $on = array();
        foreach ($fieldNames as $leftField => $rightField) {
            $on[] = $la.'.'.$db->NameQuote($leftField).' = '.$ra.'.'.$db->NameQuote($rightField);
        }
        $res .= implode(' AND ', $on);
        return $res;
    }    
        
    function _makeCritForSrcOrDest (& $data, $otherAlias, $fieldLinks, $default) {
        $keys = array_keys($fieldLinks);
        if (is_object($data)) $d = array(& $data);
        else $d = & $d;
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $this->_getAllValues($data, $values, $keys);
            $crit = $this->_makeSqlCriteria($values, Ae_Util::array_values($fieldLinks), $otherAlias, $default);
        } elseif (is_a($data, 'Ae_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $crit;
    }
    
    function _fixNNIds(& $data, $fields, $varName) {
        $isSingle = false;
        if (is_object($data)) {
            $isSingle = true;
            $d = array(& $data);
            foreach (array_keys($d) as $k) {
                $row = & $d[$k];
                $val = $this->_getValue($row, $varName);
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
                    $this->_setVal($row, $varName, $newVal);
                }
            }
        } else {
            $d = $data;
        }
    }
    
    
}

?>