<?php
/**
 * result rows will be returned as plain numeric array
 * @see Ac_Model_Relation::getDest()
 * @see Ac_Model_Relation::getSrc()
 */
define('AMR_PLAIN_RESULT', 0);
/**
 * result rows will have DB keys of source records 
 * @see Ac_Model_Relation::getDest()
 * @see Ac_Model_Relation::getSrc()
 */
define('AMR_RECORD_KEYS', 1);
/**
 * result rows will have keys of original array 
 * @see Ac_Model_Relation::getDest()
 * @see Ac_Model_Relation::getSrc()
 */
define('AMR_ORIGINAL_KEYS', 2);
/**
 * results rows will have keys of original array and all keys from original array will be in result array.
 * Values in places of missing result rows will have default value.
 *  
 * @see Ac_Model_Relation::getDest()
 * @see Ac_Model_Relation::getSrc()
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
 * Can work with objects, Ac_Model_Data descendants and associative arrays.
 * 
 * Interface of this class has one specific quality: most methods (load, count, delete) are in two flavours: for source table and for destination one.     
 */
class Ac_Model_Relation extends Ac_Model_Relation_Abstract {

    /**
     * @var Ac_Application
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
     * @var Ac_Sql_Db
     */
    var $database = false;
    
    var $destOrdering = false;
    
    var $srcOrdering = false;
    
    var $srcExtraJoins = false;
    
    var $destExtraJoins = false;
    
    var $srcWhere = false;
    
    var $destWhere = false;
    
    var $midWhere = false;
    
    var $srcQualifier = false;
    
    var $destQualifier = false;
    
    /**
     * Flipped links (from destination to midtable to source table) 
     */
    var $_fieldLinksRev = false;
    var $_fieldLinksRev2 = false;
    
    /**
     * @var Ac_Model_Mapper
     */
    var $_srcMapper = false;
    
    /**
     * @var Ac_Model_Mapper
     */
    var $_destMapper = false;
    
    // ------------------------ PUBLIC METHODS -----------------------

    /**
     * @return Ac_Model_Relation
     */
    static function factory($config = array()) {
        return Ac_Prototyped::factory($config, 'Ac_Model_Relation');
    }
    
    function setSrcMapper(Ac_Model_Mapper $srcMapper) {
        $this->_srcMapper = $srcMapper;
        $this->srcMapperClass = $srcMapper->getId();
        if ($this->srcTableName === false) $this->srcTableName = $srcMapper->tableName;
        if (!$this->database) $this->database = $this->_srcMapper->getApplication()->getDb();
        if ($this->srcOrdering === false) $this->srcOrdering = $this->_srcMapper->getDefaultOrdering();
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    function getSrcMapper() {
        return $this->_srcMapper;
    }
    
    function setDestMapper(Ac_Model_Mapper $destMapper) {
        $this->_destMapper = $destMapper;
        if ($this->destTableName === false) $this->destTableName = $destMapper->tableName;
        if (!$this->database) $this->database = $this->_destMapper->getApplication()->getDb();
        if ($this->destOrdering === false) $this->destOrdering = $this->_destMapper->getDefaultOrdering();
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    function getDestMapper() {
        return $this->_destMapper;
    }
    
    function setApplication(Ac_Application $application) {
        $this->application = $application;
        if (!$this->database) $this->database = $this->application->getDb();
    }
    
    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }
    
    /**
     * @param array $config Array prototype of the object
     */
    function __construct ($config = array()) {
        Ac_Accessor::setObjectProperty($this, $config);
        
        if (($this->srcTableName === false) && strlen($this->srcMapperClass)) {
            $this->setSrcMapper(Ac_Model_Mapper::getMapper($this->srcMapperClass, $this->application? $this->application : null));
        }
        if (($this->destTableName === false) && strlen($this->destMapperClass)) {
            $this->setDestMapper(Ac_Model_Mapper::getMapper($this->destMapperClass, $this->application? $this->application : null));
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
     * @param Ac_Model_Data|object $srcData
     * @param int $matchMode How keys of result array are composed (can be AMR_PLAIN_RESULT, AMR_RECORD_KEYS, AMR_ORIGINAL_KEYS, AMR_ALL_ORIGINAL_KEYS) -- only if srcData is array
     * @param mixed $defaultValue Value to be returned for missing rows when $matchMode is AMR_ALL_ORIGINAL_KEYS or when $srcData is an object. By default it is null if $this->destIsUnique and empty array() if not.
     * @return Ac_Model_Data|array
     */
    function getDest ($srcData, $matchMode = AMR_PLAIN_RESULT, $defaultValue = null) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        $hasDef = func_num_args() >= 3;
        $res = $this->_getSrcOrDest (
            $srcData, 
            $matchMode, 
            $defaultValue, 
            $hasDef, 
            $this->fieldLinks, 
            $this->fieldLinks2, 
            $this->destIsUnique, 
            $this->destTableName, 
            '_instantiateDest', 
            $this->destOrdering, 
            $this->destExtraJoins, 
            $this->destWhere,
            $this->srcNNIdsVarName
        );
        return $res;
    }
    
    /**
     * @param Ac_Model_Data|object|array $srcData
     * @return Ac_Model_Collection
     */
    function getDestCollection($srcData, $matchKeys = false) {
        trigger_error ('Method not implemented yet', E_USER_ERROR);
    }
    
    /**
     * @param Ac_Model_Collection $srcCollection
     * @return Ac_Model_Collection 
     */
    function getDestCollectionForCollection($srcCollection) {
        trigger_error ('Method not implemented yet', E_USER_ERROR);
    }
    
    // ----------------------- getSrc... family ---------------------
    
    /**
     * Returns one or more source objects for given destination object
     * @param Ac_Model_Data|object $destData
     * @param int $matchMode How keys of result array are composed (can be AMR_PLAIN_RESULT, AMR_RECORD_KEYS, AMR_ORIGINAL_KEYS, AMR_ALL_ORIGINAL_KEYS) -- only if srcData is array
     * @param mixed $defaultValue Value to be returned for missing rows when $matchMode is AMR_ALL_ORIGINAL_KEYS or when $srcData is an object. By default it is null if $this->destIsUnique and empty array() if not.
     * @return Ac_Model_Data|array
     */ 
    function getSrc ($destData, $matchMode = AMR_PLAIN_RESULT, $defaultValue = null) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        $hasDef = func_num_args() >= 3;
        $res = $this->_getSrcOrDest ($destData, 
            $matchMode, 
            $defaultValue, 
            $hasDef, 
            $this->_fieldLinksRev, 
            $this->_fieldLinksRev2, 
            $this->srcIsUnique, 
            $this->srcTableName, 
            '_instantiateSrc', 
            $this->srcOrdering, 
            $this->srcExtraJoins, 
            $this->srcWhere,
            $this->destNNIdsVarName
        );
        return $res;
    }
    
    /**
     * @param Ac_Model_Data|object|array $destData
     * @return Ac_Model_Collection
     */
    function getSrcCollection($destData, $matchKeys = false) {
        trigger_error ('Method not implemented', E_USER_ERROR);
    }
    
    /**
     * @param Ac_Model_Collection $destCollection
     * @return Ac_Model_Collection 
     */
    function getSrcCollectionForCollection($destCollection) {
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
    
    function loadDest (& $srcData, $dontOverwriteLoaded = true, $biDirectional = true) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        //if (!$this->srcVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $srcVarName is not set');
        $defaultValue = $this->destIsUnique? null : array();
        $biDirectional = $biDirectional && strlen($this->destVarName);

        $res = $this->_loadSrcOrDest ($srcData, $defaultValue, $this->srcVarName, $this->destVarName, 
            $dontOverwriteLoaded, $biDirectional, $this->fieldLinks, $this->fieldLinks2, $this->destIsUnique, 
                $this->srcIsUnique, $this->destTableName, '_instantiateDest', $this->destOrdering, 
                $this->destExtraJoins, $this->destWhere, $this->destQualifier, $this->srcQualifier, 
                $this->srcNNIdsVarName);
        return $res;
    }
    
    function loadSrc (& $destData, $dontOverwriteLoaded = true, $biDirectional = true) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        if (!$this->destVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $destVarName is not set');
        $defaultValue = $this->srcIsUnique? null : array();
        $biDirectional = $biDirectional && strlen($this->srcVarName);
        
        if (strlen($this->midTableName)) {
            $fl1 = $this->_fieldLinksRev2;
            $fl2 = $this->_fieldLinksRev;
        } else {
            $fl1 = $this->_fieldLinksRev;
            $fl2 = $this->_fieldLinksRev2;
        }
        
        return $this->_loadSrcOrDest ($destData, $defaultValue, $this->destVarName, $this->srcVarName, 
            $dontOverwriteLoaded, $biDirectional, $fl1, $fl2, $this->srcIsUnique, $this->destIsUnique, 
            $this->srcTableName, '_instantiateSrc', $this->srcOrdering, $this->srcExtraJoins, 
            $this->srcWhere, $this->srcQualifier, $this->destQualifier, $this->destNNIdsVarName);
    }
    
    function getStrMidWhere($alias = false) {
        if (is_array($this->midWhere)) $res = $this->database->valueCriterion($this->midWhere, $alias);
        else $res = $this->midWhere;
        return $res;
    }
    
    function loadDestNNIds($srcData, $dontOverwriteLoaded = true) {
        if (!$this->midTableName) trigger_error("This function is only applicable to relations with midTableName set", E_USER_ERROR);
        if (!strlen($this->srcNNIdsVarName)) trigger_error("Property \$srcNNIdsVarName should be set to non-empty string to use this method", E_USER_ERROR); 
        $relConfig = array(
            'database' => $this->database,
            'srcTableName' => $this->srcTableName,
            'srcMapperClass' => $this->srcMapperClass,
            'srcVarName' => $this->srcNNIdsVarName,
            
            'destTableName' => $this->midTableName,
            'destWhere' => $this->getStrMidWhere(),
            'fieldLinks' => $this->fieldLinks,
        
            'srcIsUnique' => $this->srcIsUnique,
            'destIsUnique' => false,
        );
        $rel = new Ac_Model_Relation($relConfig);
        $res = $rel->loadDest($srcData, $dontOverwriteLoaded);
        $this->_fixNNIds($srcData, array_keys($this->fieldLinks2), $this->srcNNIdsVarName);
    }
    
    function loadSrcNNIds($destData, $dontOverwriteLoaded = true) {
        if (!$this->midTableName) trigger_error("This function is only applicable to relations with midTableName set", E_USER_ERROR);
        if (!strlen($this->destNNIdsVarName)) trigger_error("Property \$destNNIdsVarName should be set to non-empty string to use this method", E_USER_ERROR);
        $relConfig = array(
            'database' => $this->database,
            'destTableName' => $this->destTableName,
            'destMapperClass' => $this->destMapperClass,
            'destVarName' => $this->destNNIdsVarName,
            
            'srcTableName' => $this->midTableName,
            'srcWhere' => $this->midWhere,
            'fieldLinks' => $this->fieldLinks2,
        
            'srcIsUnique' => false,
            'destIsUnique' => $this->srcIsUnique,
        );
        $rel = new Ac_Model_Relation($relConfig);
        $rel->loadSrc($destData, $dontOverwriteLoaded);
        $this->_fixNNIds($destData, array_values($this->fieldLinks), $this->destNNIdsVarName);
    }
    
    /**
     * Counts destination objects and stores result in $srcCountVarName of each corresponding $srcData object
     */
    function loadDestCount ($srcData, $dontOverwriteLoaded = true) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        if (!$this->srcCountVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $srcCountVarName is not set');
        return $this->_loadSrcOrDestCount ($srcData, $this->srcCountVarName, $dontOverwriteLoaded, 
            $this->fieldLinks, $this->fieldLinks2, $this->destTableName, $this->srcNNIdsVarName);
    }
    
    /**
     * Counts source objects and stores result in $destCountVarName of each corresponding $destData object
     */
    function loadSrcCount ($destData, $dontOverwriteLoaded = true) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        if (!$this->destCountVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $destCountVarName is not set');
        return $this->_loadSrcOrDestCount ($destData, $this->destCountVarName, $dontOverwriteLoaded, 
            $this->_fieldLinksRev, $this->_fieldLinksRev2, $this->srcTableName, $this->destNNIdsVarName);
    }

    /**
     * Performs corresponding cascade actions when src object(s) are to be deleted.
     * @see Ac_Model_Relation::$onDeleteSrc
     * @see Ac_Model_Relation::$onDeleteSrcParam
     */
    function handleSrcDeleted ($srcData) {
        trigger_error ('Method not implemented yet', E_USER_ERROR);
    }
    
    /**
     * Performs corresponding cascade actions when dest object(s) are to be deleted.
     * @see Ac_Model_Relation::$onDeleteDest
     * @see Ac_Model_Relation::$onDeleteDestParam
     */
        function handleDestDeleted ($destData) {
        trigger_error ('Method not implemented yet', E_USER_ERROR);
    }
    
    function getDestJoin ($srcAlias = false, $destAlias = false, $type = 'left', $midAlias = false) {
        if (!$this->midTableName) {
            $res = $this->_getJoin($type, $srcAlias, $this->destTableName, $destAlias, $this->fieldLinks);
        } else {
            if ($midAlias === false) $midAlias = $this->midTableAlias;
            $res = $this->_getJoin($type, $srcAlias, $this->midTableName, $midAlias, $this->fieldLinks, $this->getStrMidWhere($midAlias));
            $res .= ' '.$this->_getJoin($joinType, $midAlias, $this->destTableName, $destAlias, $this->fieldLinks2);
        }
        return $res;
    }
    
    function getSrcJoin ($srcAlias = false, $destAlias = false, $type = 'left', $midAlias = false) {
        if (!$this->midTableName) {
            $res = $this->_getJoin($type, $destAlias, $this->srcTableName, $srcAlias, $this->_fieldLinksRev);
        } else {
            if ($midAlias === false) $midAlias = $this->midTableAlias;
            $res = $this->_getJoin($type, $destAlias, $this->midTableName, $midAlias, $this->_fieldLinksRev2, $this->getStrMidWhere($midAlias));
            $res .= ' '.$this->_getJoin($joinType, $midAlias, $this->srcTableName, $srcAlias, $this->_fieldLinksRev);
        }
        return $res;
    }
    
    function getCritForSrcOfDest ($dest, $srcAlias = '', $default = '0') {
        if ($this->midTableName) trigger_error ("This method cannot be used when midTableName is set!", E_USER_ERROR);
        if (!$this->srcTableName) trigger_error ("Cannot ".__FUNCTION__."() with non-persistent source!", E_USER_ERROR);
        $res = $this->_makeCritForSrcOrDest($dest, $srcAlias, $this->_fieldLinksRev, $default);
        return $res;        
    }
    
    function getCritForDestOfSrc ($src, $destAlias = '', $default = '0') {
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
    function _makeMapAndGetAllValues(& $source, & $map, & $values, $keys, 
        $varToCheck = false, $midIdsVar = false, &$midMap = null, & $midVals = null, $midKeys2 = array() ) {
        $map = array();
        if (!is_array($midMap)) $midMap = array();
        $values = array();
        if (!is_array($midVals)) $midVals = array();
        foreach(array_keys($source) as $k) {
            $srcItem = $source[$k];
            $hasIds = false;
            if ($varToCheck !== false) {
                if (!$this->_isVarEmpty($srcItem, $varToCheck)) continue;
            }
            if ($midIdsVar !== false && $midKeys2) {
                $hasIds = is_array($ids = $this->_getValue($srcItem, $midIdsVar));
                if ($hasIds) {
                    $ids = $this->_nnIdsToValues($ids, $midKeys2);
                    foreach ($ids as $v) {
                        if (is_array($v)) {
                            $mk = implode('-', $v);
                        }
                            else $mk = ''.$v;
                        $midVals[$mk] = $v;
                    }
                    $midMap[$k] = array($ids, $k);
                }
            }
            if (!$hasIds) {
                $vals = $this->_getValues($srcItem, $keys, false, false);
                $map[] = array($vals, $k);
                $values[] = $vals;
            }
        }
    }
    
    function _getAllValues(& $source, & $values, $keys, $nnIdsVar = false, & $midVals = null, 
        $midKeys = array()) {
        
        $values = array();
        if (!is_array($midVals)) $midVals = array();
        foreach (array_keys($source) as $k) {
            $hasIds = false;
            $srcItem  = $source[$k];
            if ($nnIdsVar !== false) {
                $hasIds = is_array($ids = $this->_getValue($srcItem, $nnIdsVar));
                if ($hasIds) {
                    $ids = $this->_nnIdsToValues($ids, $midKeys);
                    foreach ($ids as $v) {
                        if (is_array($v)) $mk = implode('-', $v);
                            else $mk = ''.$v;
                        $midVals[$mk] = $v;
                    }
                }
            }
            if (!$hasIds) {
                $values[] = $this->_getValues($srcItem, $keys, false, false);
            }
        }
    }
    
    function _getSrcOrDest ($data, $matchMode, $defaultValue, $hasDefaultValue, $fieldLinks, $fieldLinks2, 
        $isUnique, $tableName, $instanceFunc, $ordering, $extraJoins, $extraWhere, $nnIdsVar = false) {
        $keys = array_keys($fieldLinks);
        $midMap = false;
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $midKeys = is_array($fieldLinks2)? array_values($fieldLinks2) : array();
            if ($matchMode & AMR_ORIGINAL_KEYS) {
                $map = array();
                $midMap = array();
                $this->_makeMapAndGetAllValues($data, $map, $values, $keys, false, $nnIdsVar, 
                    $midMap, $midValues, $midKeys);
            } else {
                $this->_getAllValues($data, $values, $keys, $nnIdsVar, $midValues, $midKeys);
            }

            $rows = array();
            $rows2 = array();

            if ($values) {
                $rows = $this->_getWithValues($values, 
                    $this->midTableName? array($fieldLinks, $fieldLinks2) : Ac_Util::array_values($fieldLinks), 
                    true, $isUnique, $matchMode > 0, $tableName, $instanceFunc, false, false, $this->midTableName, 
                    $ordering, $extraJoins, $extraWhere, 
                    $midValues? array_values($fieldLinks2) : false, $midValues);

                if ($midValues) {
                    list ($rows, $rows2) = $rows;
                }
                
            } else {
                if ($midValues) {
                    $rows2 = $this->_getWithValues($midValues, 
                            array_values($fieldLinks2), 
                            true, $isUnique, true, $tableName, $instanceFunc, false, false, false, 
                            $ordering, $extraJoins, $extraWhere);
                } else {
                    $rows2 = array();
                }
            }
            
            if ($matchMode & AMR_ORIGINAL_KEYS) {
                $res = array();
                if ($rows) $this->_unmapResult($keys, $map, $matchMode, $res, $rows, $defaultValue, $isUnique, 
                    false);
                if ($midMap) {
                    $this->_unmapResult(array_values($fieldLinks2), $midMap, $matchMode, $res, $rows2, 
                        $defaultValue, $isUnique, true);                    
                }
            } else {
                $res = $rows;
                if (!$res && $rows2) {
                    if ($isUnique) $res = array_values($rows2);
                    else foreach ($rows2 as $k => $rows) $res = array_merge($res, $rows);
                }
            }
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            $values = $this->_getValues($data, $keys, false, false);
            if ($nnIdsVar && isset($data->$nnIdsVar) && is_array($ids = $data->$nnIdsVar)) {
                $ids = $this->_nnIdsToValues($ids, array_values($fieldLinks2));
                $rows = $this->_getWithValues($ids, array_values($fieldLinks2), 
                    true, $isUnique, false, $tableName, $instanceFunc, false, false, 
                    false, $ordering, $extraJoins, $extraWhere);
            } else {
                $rows = $this->_getWithValues($values, 
                        $this->midTableName? array($fieldLinks, $fieldLinks2) : Ac_Util::array_values($fieldLinks), 
                        false, $isUnique, (bool) $matchMode, $tableName, $instanceFunc, false, false, 
                        $this->midTableName, $ordering, $extraJoins, $extraWhere);
            }
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
    
    function _unmapResult($keys, $map, $matchMode, & $res, $rows, $defaultValue, $isUnique, $mapMultiple) {
        if (count($keys) === 1) {
            foreach ($map as $m) {
                $toSet = false;
                if ($mapMultiple && !$isUnique) {
                    $toSet = array();
                    foreach ($m[0] as $rowKey) {
                        if (isset($rows[$rowKey])) {
                            $toSet = array_merge($toSet, $rows[$rowKey]);
                        }
                    }
                } else {
                    if (isset($rows[$rowKey = $m[0][0]])) $toSet = $rows[$rowKey]; 
                }
                if ($toSet === false) {
                    if ($matchMode == AMR_ALL_ORIGINAL_KEYS) {
                        $toSet = $defaultValue;
                    } else {
                        continue;
                    }
                }
                $res[$m[1]] = $toSet; 
            }
        } else {
            foreach ($map as $m) {
                $toSet = false;
                if ($mapMultiple && !$isUnique) {
                    $toSet = array();
                    foreach ($m[0] as $rowPath) {
                        $row = Ac_Util::simpleGetArrayByPath($rows, $rowPath, false);
                        if ($row !== false) {
                           $toSet = array_merge($toSet, $row);
                        }
                        if (isset($rows[$rowKey])) {
                            if ($isUnique) $rr = array($rows[$rowKey]);
                                else $rr = $rows[$rowKey];
                            $toSet = array_merge($toSet, $rr);
                        }
                    }
                } else {
                    $rowPath = $m[0];
                    $dataKey = $m[1];
                    $row = Ac_Util::simpleGetArrayByPath($rows, $rowPath, false);
                    if ($row !== false) {
                       $toSet = $row;
                    }
                }
                if ($toSet === false) {
                    if ($matchMode == AMR_ALL_ORIGINAL_KEYS) {
                        $toSet = $defaultValue;
                    } else {
                        continue;
                    }
                }
                $res[$m[1]] = $toSet; 
            }
        }
    }
    
    function _countSrcOrDest ($data, $separate = true, $matchMode = AMR_PLAIN_RESULT, $fieldLinks, $fieldLinks2, 
        $isUnique, $tableName) {
        $keys = array_keys($fieldLinks);
        if (is_array($data)) { // we assume that this is array of objects or array of rows
            $values = array();
            if ($separate && ($matchMode & AMR_ORIGINAL_KEYS)) {
                $map = array();
                $this->_makeMapAndGetAllValues($data, $map, $values, $keys, 
                    is_array($fieldLinks2)? array_values($fieldLinks2) : array());
            } else {
                $this->_getAllValues($data, $values, $keys);
            }
            $counts = $this->_countWithValues($values, $this->midTableName? $fieldLinks2 : Ac_Util::array_values($fieldLinks), 
                true, $separate, $matchMode > 0, $tableName, false, false, $this->midTableName, 
                $this->midTableName? $fieldLinks : false);
            if (!$separate) {
                $res = $counts;
            } else {
                if ($matchMode & AMR_ORIGINAL_KEYS) {
                    $res = array();
                    if (count($keys) === 1) {
                        foreach ($map as $m) {
                            if ($matchMode == AMR_ALL_ORIGINAL_KEYS) {
                                if (isset($counts[$countKey = $m[0][0]])) $res[$m[1]] = $counts[$countKey]; else $res[$m[1]] = 0; 
                            } else {
                                if (isset($counts[$countKey = $m[0][0]])) $res[$m[1]] = $counts[$countKey];
                            } 
                        }
                    } else {
                        $countPath = $m[0];
                        foreach ($map as $m) {
                            $countValue = Ac_Util::simpleGetArrayByPath($counts, $countPath, false);
                            if ($countValue !== false)
                                $res[$m[1]] = $countValue;
                            elseif ($matchMode == AMR_ALL_ORIGINAL_KEYS) 
                                $res[$m[1]] = 0; 
                        }
                    }
                } else {
                    $res = $counts;
                }
            }
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            $values = $this->_getValues($data, $keys, false, false);
            $res = $this->_countWithValues($values, $this->midTableName? $fieldLinks2 : Ac_Util::array_values($fieldLinks), false, false, false, $destTableName, false, false, $this->midTableName);
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    function _deleteSrcOrDest ($data, $fieldLinks, $fieldLinks2, $tableName, $where = false) {
        $keys = array_keys($fieldLinks);
        if (is_object($data)) {
            $xd = array(& $data);
            return $this->_deleteSrcOrDest($xd, $fieldLinks, $fieldLinks2, $tableName, $where);
        }
        if (is_array($data)) { // we assume that this is array of objects or array of rows
            $values = array();
            $this->_getAllValues($data, $values, $keys);
            if ($this->midTableName) {
                $midValues = $this->_getWithValues($values, Ac_Util::array_values($fieldLinks), true, false, false, $this->midTableName, '_rowInstance', false, false, '', false, $where );
                $rightKeyValues = array();
                $this->_getAllValues($midValues, $rightKeyValues, array_keys($fieldLinks2));
                $res = true;
                $this->database->startTransaction();
                if (!$this->_deleteWithValues($values, true, array_values($fieldLinks))) $res = false;
                if (!$this->_deleteWithValues($midValues, true, Ac_Util::array_values($fieldLinks2))) $res = false;
                if (!$res) $this->database->rollback();
                    else $this->database->commit();
            } else {
                $res = $this->_deleteWithValues($values, true, Ac_Util::array_values($fieldLinks), $tableName, false, $where);
            }
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    function _loadSrcOrDest (& $data, $defaultValue, $varName, $otherVarName, $dontOverwriteLoaded, $biDirectional, 
            $fieldLinks, $fieldLinks2, $isUnique, $otherIsUnique, $tableName, $instanceFunc, $ordering = false, 
            $extraJoins = false, $extraWhere = false, $qualifier = false, $otherQualifier = false, 
            $nnIdsVar = false) {
        $keys = array_keys($fieldLinks);
        $res = array();
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $map = array();
            $midValues = array();

            $varToCheck = $dontOverwriteLoaded? $varName : false;
            $this->_makeMapAndGetAllValues($data, $map, $values, $keys, $varToCheck, $nnIdsVar, $midMap, 
                $midValues, is_array($fieldLinks2)? array_values($fieldLinks2) : array());
            
            $rows = array();
                
            if ($values) {
                
                $rows = $this->_getWithValues($values, 
                        $this->midTableName? array($fieldLinks, $fieldLinks2) : Ac_Util::array_values($fieldLinks), 
                        true, $isUnique, true, $tableName, $instanceFunc, false, false, $this->midTableName, 
                        $ordering, $extraJoins, $extraWhere, 
                        $midValues? array_values($fieldLinks2) : false, $midValues);
                
                // _getWithValues returns two mapped sets of same rows in that case
                
                if ($midValues) {
                    list($rows, $rows2) = $rows;
                }
                
                $this->_unmap($keys, $map, $rows, $data, $defaultValue, $biDirectional, $varName, 
                    $otherVarName, $isUnique, $otherIsUnique, $qualifier, $otherQualifier);
                
                if ($midMap) {
                    if (!$midValues) $rows2 = array();
                    $this->_unmap(array_values($fieldLinks2), $midMap, $rows2, $data, $defaultValue, $biDirectional, 
                        $varName, $otherVarName, $isUnique, $otherIsUnique, $qualifier, $otherQualifier, true);
                }
                
                
            } elseif ($midMap) {
                // Degrade to loading dest with known values
                if ($midValues) {
                    $rows = $this->_getWithValues($midValues, 
                            array_values($fieldLinks2), 
                            true, $isUnique, true, $tableName, $instanceFunc, false, false, false, 
                            $ordering, $extraJoins, $extraWhere);
                } else {
                    $rows = array();
                }
                $this->_unmap(array_keys($fieldLinks2), $midMap, $rows, $data, $defaultValue, $biDirectional, 
                    $varName, $otherVarName, $isUnique, $otherIsUnique, $qualifier, $otherQualifier, true);
            }
            $res = $rows;
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            if (!$dontOverwriteLoaded || $this->_isVarEmpty($data, $varName)) {  
                // check for NN ids
                if ($nnIdsVar && isset($data->$nnIdsVar) && is_array($ids = $data->$nnIdsVar)) {
                    $values = $this->_nnIdsToValues($ids, array_values($fieldLinks2));
                    $rows = $this->_getWithValues($values,  array_values($fieldLinks2), 
                        true, $isUnique, false, $tableName, $instanceFunc, false, false, 
                        false, $ordering, $extraJoins, $extraWhere);
                } else {
                    $values = $this->_getValues($data, $keys, false, false);
                    
                    $rows = $this->_getWithValues($values, 
                        $this->midTableName? array($fieldLinks, $fieldLinks2) : Ac_Util::array_values($fieldLinks), 
                        false, $isUnique, false, $tableName, $instanceFunc, false, false, 
                        $this->midTableName, $ordering, $extraJoins, $extraWhere);
                }
                if ($rows) {
                    $toSet = $rows;
                    if ($biDirectional) $this->_linkBack($rows, $data, $otherVarName, !$isUnique, $otherIsUnique, $otherQualifier);
                } else $toSet = $defaultValue;
                $this->_setVal($data, $varName, $toSet, $qualifier);
                if ($isUnique) $res = array(& $rows); else $res = $rows;
            }
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    function _nnIdsToValues($idsArray, $keys) {
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
    
    function _unmap($keys, $map, & $rows, & $data, $defaultValue, $biDirectional, $varName, $otherVarName, 
            $isUnique, $otherIsUnique, $qualifier, $otherQualifier, $mapMultiple = false) {
        if (count($keys) === 1) {
            foreach ($map as $m) {
                $dataKey = $m[1];
                if ($mapMultiple && !$isUnique) {
                    $toSet = array();
                    foreach ($m[0] as $rowKey) {
                        if (isset($rows[$rowKey])) {
                            $toSet = array_merge($toSet, $rows[$rowKey]);
                            if ($biDirectional) {
                                $this->_linkBack($rows[$rowKey], $data[$dataKey], $otherVarName, 
                                    !$isUnique, $otherIsUnique, $otherQualifier); 
                            }
                        }
                    }
                } else {
                    if (isset($rows[$rowKey = $m[0][0]])) {
                        $toSet = $rows[$rowKey];
                        if ($biDirectional) $this->_linkBack($rows[$rowKey], $data[$dataKey], $otherVarName, 
                            !$isUnique, $otherIsUnique, $otherQualifier); 
                    } else {
                        $toSet = $defaultValue;
                    }
                }
                $this->_setVal($data[$dataKey], $varName, $toSet, $qualifier);
            }
        } else {
            foreach ($map as $m) {
                $dataKey = $m[1];
                if ($mapMultiple && !$isUnique) {
                    $toSet = array();
                    foreach ($m[0] as $rowPath) {
                        $row = Ac_Util::simpleGetArrayByPath($rows, $rowPath, false);
                        if ($row !== false) {
                           $toSet = array_merge($toSet, $row);
                           if ($biDirectional) $this->_linkBack($row, $data[$dataKey], $otherVarName, 
                               !$isUnique, $otherIsUnique, $otherQualifier);
                        }
                    }
                } else {
                    $rowPath = $m[0];
                    $row = Ac_Util::simpleGetArrayByPath($rows, $rowPath, false);
                    if ($row !== false) {
                       $toSet = $row;
                       if ($biDirectional) $this->_linkBack($row, $data[$dataKey], $otherVarName, !$isUnique, $otherIsUnique, $otherQualifier);
                    }
                    else {
                        $toSet = $defaultValue;
                    }
                }
                $this->_setVal($data[$dataKey], $varName, $toSet, $qualifier);
            }
        }
    }
    
    function _loadSrcOrDestCount ($data, $varName, $dontOverwriteLoaded, $fieldLinks, $fieldLinks2, 
        $tableName, $nnIdsVar = false) {
        $keys = array_keys($fieldLinks);
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $map = array();
            
            $this->_makeMapAndGetAllValues($data, $map, $values, $keys, 
                $dontOverwriteLoaded? $varName : false, $nnIdsVar, $midMap, $midValues,
                is_array($fieldLinks2)? array_values($fieldLinks2) : array());
            
            if ($midMap) {
                foreach ($midMap as $m) {
                    $dataKey = $m[1];
                    $this->_setVal($data[$dataKey], $varName, count($m[0])); 
                }
            }

            if ($values) {
                
                $counts = $this->_countWithValues($values, 
                    $this->midTableName? $fieldLinks2 : Ac_Util::array_values($fieldLinks), 
                    true, true, true, $tableName, false, false, $this->midTableName, 
                    $fieldLinks);
                
                if (count($keys) === 1) {
                    foreach ($map as $m) {
                        $dataKey = $m[1];
                        if (isset($counts[$countKey = $m[0][0]])) {
                            $toSet = $counts[$countKey];
                        } else $toSet = 0;
                        $this->_setVal($data[$dataKey], $varName, $toSet); 
                    }
                } else {
                    foreach ($map as $m) {
                        $countPath = $m[0];
                        $dataKey = $m[1];
                        $count = Ac_Util::simpleGetArrayByPath($counts, $countPath, 0);
                        $this->_setVal($data[$dataKey], $varName, $count);
                    }
                }
            }
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            if ($nnIdsVar && is_array($v = $this->_getValue($data, $nnIdsVar))) {
                $this->_setVal($data, $varName, count($v));
            }
            if (!$dontOverwriteLoaded || $this->_isVarEmpty($data, $varName)) {  
                $values = $this->_getValues($data, $keys, false, false);
                $this->_setVal($data, $varName, $k = $this->_countWithValues(
                    $values, 
                    $this->midTableName? $fieldLinks2 : Ac_Util::array_values($fieldLinks), 
                    false, false, false, $tableName, false, false, $this->midTableName, $fieldLinks)
                );
            }
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
    }
    
    function _getWithValues ($values, $keys, $multipleValues, $unique, $byKeys, $tableName, $ifun, 
            $orderByKeys = false, $retSql = false, $midTableName = '', $ordering = false, 
            $extraJoins = false, $extraWhere = false, $keys2 = false, $values2 = false) {
        $ta = 't';
        $asTa = 'AS t';
        $cols = 't.*'; 
        if (!$multipleValues) $values = array($values);
            elseif (!count($values)) {
                $res = array();
                return $res;
            }
        if ($midTableName) {
            $allKeys = $keys;
            $selKeys = array_values($allKeys[0]);
            $keys = array_values($allKeys[0]);            
            $lta = '_mid_.';
            $crit = $this->_makeSqlCriteria($values, $selKeys, '_mid_');
            if ($keys2) {
                $join = 'LEFT';
                $crit = "(".$crit.") OR (".$this->_makeSqlCriteria($values2, $keys2, strlen($ta)? $ta : $tableName).")";
            } else {
                $join = 'INNER';
            }
            $fromWhere = ' FROM '.$this->database->n($midTableName).' AS _mid_ '
                .$this->_getJoin($join, '_mid_', $tableName, $ta, $allKeys[1]);
        } else {
            $fromWhere = ' FROM '.$this->database->n($tableName).$asTa;
            $selKeys = $keys;    
            $lta = $this->database->n($tableName).'.';
            $crit = $this->_makeSqlCriteria($values, $keys, $ta);
        }
        if ($extraJoins) $fromWhere .= ' '.$extraJoins;
        $fromWhere .= ' WHERE ('.$crit.')';
        if ($extraWhere) $fromWhere .= ' AND '.$extraWhere; 
            

        foreach ($keys as $key) $qKeys[] = $lta.$this->database->n($key);
        $sKeys = implode(', ', $qKeys);
        $sql = 'SELECT ';
        if ($midTableName) {
            $sql .= 'DISTINCT '.$sKeys.', '.$this->database->n($ta? $ta: $tableName).'.*'.$fromWhere;
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
        
        if ($midTableName) {
        
            $rr = $this->database->getResultResource($sql);
            $fi = $this->database->resultGetFieldsInfo($rr);
            
            $prefix = $this->database->getDbPrefix();
            $tn = str_replace('#__', $prefix, $tableName);
            if ($ta) $tn = $ta;
            
            $rows = array();
            $mid = array();

            while($row = $this->database->resultFetchAssocByTables($rr, $fi))  {
                $rows[] = $row[$tn];
                $mid[] = $row['_mid_'];
            }
            $objects = $this->$ifun($rows);
            
            $rightKeyFields = array_values($allKeys[1]);
            if ($byKeys) {
                if (count($keys) === 1) {
                    $key = $keys[0];
                    if ($unique) {
                        foreach ($mid as $i => $keyValue) 
                            $res[$keyValue[$key]] = $objects[$i];
                    } else {
                        foreach ($mid as $i => $keyValue) 
                            $res[$keyValue[$key]][] = $objects[$i];
                    }
                } else {
                    foreach ($mid as $i => $keyValue) {
                        Ac_Util::simpleSetArrayByPathNoRef($res, array_values($keyValue), $objects[$i], $unique);
                    }
                }
            } else {
                $res = $objects;
            }
            $this->database->resultFreeResource($rr);
        }
        if (!$midTableName || $keys2) {
            if ($keys2) {
                $tmp = $res;
                $res = array();
                $keys = $keys2;
                // we already have $rows and $objects populated
            } else {
                $rows = $this->database->fetchArray($sql);
                $objects = $this->$ifun($rows);
            }
            if ($byKeys) {
                if (count($keys) === 1) {
                    $key = $keys[0];
                    if ($unique) {
                        foreach ($rows as $i => $row)
                            $res[$row[$key]] = $objects[$i];
                    } else {
                        foreach ($rows as $i => $row)
                            $res[$row[$key]][] = $objects[$i];
                    }
                } else {
                    foreach ($rows as $i => $row)
                        $this->_putRowToArray($row, $objects[$i], $res, $keys, $unique);        
                }
            } else {
                $res = $objects;     
            }
            // return two alternative mapping sets
            if ($keys2) $res = array($tmp, $res);
        }
        if (!$multipleValues && !$keys2 && $unique && count($res)) {
            $tmp = $res[0];
            return $tmp;
        }
        return $res;
    }
    
    // TODO: optimize _countWithValues and _getWithValues to place instances into nested array faster when resultset is ordered
    
    function _countWithValues($values, $keys, $multipleValues, $separateCounts, $byKeys, $tableName, 
            $orderByKeys = false, $retSql = false, $midTableName = '', $otherKeys = false) {
        if (!$multipleValues) $values = array($values);
            elseif (!count($values)) return $separateCounts? array() : 0;
        if ($midTableName) {
            $fromWhere = ' FROM '.$this->database->n($midTableName).' AS _mid_ '/*.$this->_getJoin('INNER', '_mid_', $tableName, '', $keys)*/;
            $keys = array_values($otherKeys); 
            $selKeys = $keys;
            $lta = '_mid_.';
            $crit = $this->_makeSqlCriteria($values, $keys, '_mid_');
            if ($this->midWhere !== false) $crit = "( $crit ) AND (".$this->getStrMidWhere('_mid_').")";
        } else {
            $fromWhere = ' FROM '.$this->database->n($tableName);
            $selKeys = $keys;    
            $lta = $this->database->n($tableName).'.';
            $crit = $this->_makeSqlCriteria($values, $keys, '');
        }
        $fromWhere .= ' WHERE '.$crit;
        
        if (!$separateCounts) {
            $sql = 'SELECT COUNT(*) '.$fromWhere;
            if ($retSql) return $sql;
            return $this->database->fetchValue($sql);
        }
        $qKeys = array();
        foreach ($keys as $key) $qKeys[] = $lta.$this->database->n($key);
        $sKeys = implode(', ', $qKeys);
        $i = 0;
        while(in_array($cntColumn = '__count__'.$i, $keys)) $i++; 
        $sql = 'SELECT '.$sKeys.', COUNT(*) AS '.$this->database->n($cntColumn).$fromWhere.' GROUP BY '.$sKeys;
        if ($orderByKeys) $sql .= ' ORDER BY '.$sKeys; 
        if ($retSql) return $sql;
        $res = array();
        $rr = $this->database->getResultResource($sql);
        if ($byKeys && $multipleValues) {
            if (count($selKeys) === 1) {
                $key = $selKeys[0];
                while($row = $this->database->resultFetchAssoc($rr)) {
                    $res[$row[$key]] = $row[$cntColumn];        
                }
            } else {
                while($row = $this->database->resultFetchAssoc($rr)) {
                    $this->_putRowToArray($row, $row[$cntColumn], $res, $selKeys, true);        
                }
            }
        } else {
            while($row = $this->database->resultFetchAssoc($rr)) 
                $res[] = $row[$cntColumn];     
        }
        $this->database->resultFreeResource($rr);
        if (!$multipleValues) $res = $res[0];
        return $res;
    }
    
    function _deleteWithValues($values, $multipleValues, $keys, $tableName, $retSql = false, $where = false) {
        $crit = $this->_makeSqlCriteria($values, $keys);
        if (!$multipleValues) $values = array($values);
            elseif (!count($values)) return 0;
        $sql =  'DELETE FROM '.$this->database->n($tableName).' WHERE ('.$crit.')';
        if (strlen($where)) $sql .= ' AND '.$where;
        if ($retSql) return $sql;
        return $this->database->query($sql);
    }

    function _instantiateDest(array $rows) {
        if ($this->_destMapper) 
            $rows = $this->_destMapper->loadFromRows($rows);
        return $rows;
    }
    
    function _instantiateSrc(array $rows) {
        if ($this->_srcMapper) 
            $rows = $this->_srcMapper->loadFromRows($rows);
        return $rows;
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
        if (!count($values)) return $default;
        // TODO: Optimization 1: remove duplicates from values! (how??? sort keys??? make a tree???)
        // TODO: Optimization 2: make nested criterias depending on values cardinality
        $values = Ac_Util::array_unique($values); 
        $db = $this->database;
        $qAlias = strlen($alias)? $alias.'.' : $alias;
        if (is_array($keyFields)) {
            if (count($keyFields) === 1) {
                $qValues = array();
                $qKeyField = $db->n($keyFields[0]);
                foreach ($values as $val) $qValues[] = $db->q(is_array($val)? $val[0] : $val);
                $qValues = array_unique($qValues);
                if (count($qValues) === 1) $res = $qAlias.$qKeyField.' = '.$qValues[0];
                    else $res = $qAlias.$qKeyField.' IN('.implode(",", $qValues).')';
            } else {
                $cKeyFields = count($keyFields);
                $bKeyFields = $cKeyFields - 1;
                $qKeyFields = array();
                foreach ($keyFields as $keyField) $qKeyFields[] = $qAlias.$db->n($keyField);
                $crit = array();
                foreach ($values as $valArray) {
                    $c = '';
                    for ($i = 0; $i < $bKeyFields; $i++) {
                        $c .= $qKeyFields[$i].'='.$db->q($valArray[$i]).' AND ';
                    }
                    $crit[] = $c.$qKeyFields[$bKeyFields].' = '.$db->q($valArray[$bKeyFields]);
                }
                $res = '('.implode(')OR(', $crit).')';
            }
        } else {
            $qValues = array();
            $qKeyField = $db->NameQuote($keyFields);
            foreach ($values as $val) $qValues[] = $db->Quote($val);
            if (count($qValues) === 1) $res = $qAlias.$qKeyField.' = '.$qValues[0];
                else $res = $qAlias.$qKeyField.' IN('.implode(",", $qValues).')';
        }
        return $res;
    }
    
    /**
     * Creates JOIN clause ("$joinType JOIN $rightTable AS $rightAlias ON $leftAlias.$key0 = $rightAlias.$field0 AND $leftAlias.$key1 = $rightAlias.$field1"), 
     * $keyN and $fieldN are taken from $fieldNames 
     */
    function _getJoin ($joinType, $leftAlias, $rightTable, $rightAlias, $fieldNames, $extraCrit = false) {
        $db = $this->database;
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
        if ($extraCrit) $res[] = "($extraCrit)";
        $res .= implode(' AND ', $on);
        return $res;
    }    
        
    function _makeCritForSrcOrDest ($data, $otherAlias, $fieldLinks, $default) {
        $keys = array_keys($fieldLinks);
        if (is_object($data)) $d = array(& $data);
        else $d = $data;
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $this->_getAllValues($data, $values, $keys);
            $crit = $this->_makeSqlCriteria($values, Ac_Util::array_values($fieldLinks), $otherAlias, $default);
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $crit;
    }
    
    function _fixNNIds($data, $fields, $varName) {
        $isSingle = false;
        if (is_object($data)) {
            $isSingle = true;
            $d = array(& $data);
        } else {
            $d = $data;
        }
        foreach (array_keys($d) as $k) {
            $row = $d[$k];
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
    }
    
}
