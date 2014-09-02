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
    protected $srcMapperClass = false;
    
    /**
     * Name of destination table mapper (if given). If destination mapper is given, all other parameters will be taken from its function calls.
     * @var string|bool 
     */
    protected $destMapperClass = false;
    
    /**
     * Name of source table
     * @var string|bool
     */
    protected $srcTableName = false;
    
    /**
     * Name of destination table
     * @var string|bool
     */
    protected $destTableName = false;
        
    /**
     * Name of linking table (for many-to-many relations). It is important to set midTableName to empty string ('') if we don't have it. False means that
     * value is not initialized and has to be taken from one of the mappers (if possible)
     * @var string|bool
     */
    protected $midTableName = '';
    
    /**
     * Default alias for middle table
     * @var string
     */
    protected $midTableAlias = '_mid_';
    
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
    protected $srcIsUnique = false;
    
    /**
     * Describes cardinality of destination table (true if destination fields point to unique record)
     */
    protected $destIsUnique = false;
    
    /**
     * Name of variable in source object that contains reference to destination object (if $destUnique is false, it has to be an array with references) 
     */
    protected $srcVarName = false;
    
    /**
     * Name of variable in source object that contains link records for N-N (with midTable) links
     * @var string
     */
    protected $srcNNIdsVarName = false;
    
    /**
     * Name of variable in destination object that contains reference to source object (if $srcIsMultiple is true, it has to be an array with references)
     */
    protected $destVarName = false;
    
    /**
     * Name of variable in destination object that contains link records for N-N (with midTable) links
     * @var string
     */
    protected $destNNIdsVarName = false;
    
    /**
     * @var bool This relation is outgoing from source object (belongs to source table)
     */
    protected $srcOutgoing = false;
    
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
    
    /**
     * @var bool|string
     */
    protected $destLoadedVarName = false;
    
    /**
     * @var Ac_Sql_Db
     */
    protected $db = false;
    
    protected $destOrdering = false;
    
    protected $srcOrdering = false;
    
    protected $srcExtraJoins = false;
    
    protected $destExtraJoins = false;
    
    protected $srcWhere = false;
    
    protected $destWhere = false;
    
    protected $midWhere = false;
    
    protected $srcQualifier = false;
    
    protected $destQualifier = false;
    
    /**
     * Flipped links (from destination to midtable to source table) 
     */
    protected $fieldLinksRev = false;
    
    protected $fieldLinksRev2 = false;
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $srcMapper = false;
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $destMapper = false;
    
    protected $immutable = false;
    
    /**
     * @var Ac_Model_Relation
     */
    protected $destNNIdsRelation = false;
    
    /**
     * @var Ac_Model_Relation
     */
    protected $srcNNIdsRelation = false;
    
    
    // ------------------------ PUBLIC METHODS -----------------------
    
    /**
     * @param array $prototype Array prototype of the object
     */
    function __construct (array $prototype = array()) {
        
        if (isset($prototype['immutable'])) {
            $imm = $prototype['immutable'];
            unset($prototype['immutable']);
        }
        else $imm = false;
        
        if (isset($prototype['application']) && $prototype['application']) {
            $this->setApplication($prototype['application']);
            unset ($prototype['application']);
        }
        
        if (isset($prototype['db']) && $prototype['db']) {
            $this->setDb($prototype['db']);
            unset ($prototype['db']);
        }
        
        parent::__construct($prototype);
        
        if (($this->srcTableName === false) && strlen($this->srcMapperClass)) {
            $this->setSrcMapper(Ac_Model_Mapper::getMapper($this->srcMapperClass, $this->application? $this->application : null));
        }
        if (($this->destTableName === false) && strlen($this->destMapperClass)) {
            $this->setDestMapper(Ac_Model_Mapper::getMapper($this->destMapperClass, $this->application? $this->application : null));
        }
        
        /*if (!$this->fieldLinks) trigger_error('fieldLinks must be specified', E_USER_ERROR);
            else $this->fieldLinksRev = array_flip($this->fieldLinks);
        
        if (strlen($this->midTableName)) {
            if (!$this->fieldLinks2) trigger_error('fieldLinks2 must be specified with midTableName', E_USER_ERROR);
                else $this->fieldLinksRev2 = array_flip($this->fieldLinks2);
        }*/
        
        if ($imm) $this->immutable = true;            
        
    }
    
    protected static function immutableException() {
        return new Ac_E_InvalidUsage("Cannot modify ".__CLASS__." with \$immutable == true");
    }    
    
    function setSrcMapper(Ac_Model_Mapper $srcMapper, $force = false) {
        if ($srcMapper !== ($oldSrcMapper = $this->srcMapper) || $force) {
            if ($this->immutable) throw self::immutableException();
            $this->srcMapper = $srcMapper;
            $this->srcMapper = $srcMapper;
            $this->srcMapperClass = $srcMapper->getId();
            if ($this->srcTableName === false) {
                $this->srcTableName = $srcMapper->tableName;
            }
            if (!$this->db) $this->db = $this->srcMapper->getApplication()->getDb();
            if ($this->srcOrdering === false) $this->srcOrdering = $this->srcMapper->getDefaultOrdering();
            $this->srcNNIdsRelation = false;
        }
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    function getSrcMapper() {
        return $this->srcMapper;
    }
    
    function setDestMapper(Ac_Model_Mapper $destMapper, $force = false) {
        if ($destMapper !== ($oldDestMapper = $this->destMapper) || $force) {
            if ($this->immutable) throw self::immutableException();
            $this->destMapper = $destMapper;
            if ($this->destTableName === false) {
                $this->destTableName = $destMapper->tableName;
            }
            if (!$this->db) $this->db = $this->destMapper->getApplication()->getDb();
            if ($this->destOrdering === false) $this->destOrdering = $this->destMapper->getDefaultOrdering();
            $this->destNNIdsRelation = false;
        }
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    function getDestMapper() {
        return $this->destMapper;
    }
    
    function setApplication(Ac_Application $application) {
        if ($application !== ($oldApplication = $this->application)) {
            if ($this->immutable) throw self::immutableException();
            $this->application = $application;
            $this->setDb($this->application->getDb());
            if (!$oldApplication) {
                if ($this->srcMapperClass && !$this->srcMapper) $this->setSrcMapperClass($this->srcMapperClass, true);
                if ($this->destMapperClass && !$this->destMapper) $this->setDestMapper($this->destMapper, true);
            }
        }
    }
    
    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }
    
    /**
     * Assigns ID of source table mapper. 
     * 
     * If source mapper is given, all other parameters are taken from its' members.
     * 
     * @param string $srcMapperClass ID of source mapper
     * @param bool $force Force re-configuration from source mapper even if it wasn't changed
     */
    function setSrcMapperClass($srcMapperClass, $force = false) {
        if ($srcMapperClass !== ($oldSrcMapperClass = $this->srcMapperClass) || $force) {
            if ($this->immutable) throw self::immutableException();
            $this->srcMapperClass = $srcMapperClass;
            if (strlen($srcMapperClass) && $this->application) {
                $this->setSrcMapper(Ac_Model_Mapper::getMapper($this->srcMapperClass, $this->application));
            }
            $this->srcNNIdsRelation = false;
        }
    }

    /**
     * Returns ID of source mapper (if ID or source mapper was provided).
     * 
     * If source mapper is given, all other parameters are taken from its' members.
     * @var string|bool 
     */
    function getSrcMapperClass() {
        return $this->srcMapperClass;
    }

    /**
     * Assigns ID of destination table mapper. 
     * 
     * If destination mapper is given, all other parameters are taken from its' members.
     * 
     * @param string $destMapperClass ID of destination mapper
     * @param bool $force Force re-configuration from destination mapper even if it wasn't changed
     */
    function setDestMapperClass($destMapperClass, $force = false) {
        if ($destMapperClass !== ($oldDestMapperClass = $this->destMapperClass) || $force) {
            if ($this->immutable) throw self::immutableException();
            $this->destMapperClass = $destMapperClass;
            if (strlen($destMapperClass) && $this->application) {
                $this->setDestMapper(Ac_Model_Mapper::getMapper($this->destMapperClass, $this->application));
            }
            $this->destNNIdsRelation = false;
        }
    }

    /**
     * Returns ID of destination mapper (if ID or destination mapper was provided).
     * 
     * If destination mapper is given, all other parameters are taken from its' members.
     * @var string|bool 
     */
    function getDestMapperClass() {
        return $this->destMapperClass;
    }

    /**
     * Assigns SQL name of source table (can be Ac_Sql_Expression too)
     * 
     * Will be auto-assigned if $srcMapperClass or $srcMapper are provided
     * 
     * @param string|Ac_Sql_Expression $srcTableName
     */
    function setSrcTableName($srcTableName) {
        if ($srcTableName !== ($oldSrcTableName = $this->srcTableName)) {
            if ($this->immutable) throw self::immutableException();
            $this->srcTableName = $srcTableName;
            $this->srcNNIdsRelation = false;
        }
    }

    /**
     * Returns SQL name of source table
     * 
     * @return string|Ac_Sql_Expression
     */
    function getSrcTableName() {
        return $this->srcTableName;
    }

    /**
     * Assigns SQL name of destination table (can be Ac_Sql_Expression too)
     * 
     * Will be auto-assigned if $destMapperClass or $destMapper are provided
     * 
     * @param string|Ac_Sql_Expression $destTableName
     */
    function setDestTableName($destTableName) {
        if ($destTableName !== ($oldDestTableName = $this->destTableName)) {
            if ($this->immutable) throw self::immutableException();
            $this->destTableName = $destTableName;
            $this->destNNIdsRelation = false;
        }
    }

    /**
     * Returns SQL name of destination table
     * 
     * @return string|Ac_Sql_Expression
     */
    function getDestTableName() {
        return $this->destTableName;
    }

    /**
     * Assigns SQL name of intermediary table
     * 
     * Intermediary table is used to create N-N relations between two 'primary'
     * tables (i.e. people <-> peopleTags <-> tags). It must reference both
     * srcTable and destTable.
     * 
     * If there is no intermediary table, FALSE is provided.
     * 
     * @param string|bool|Ac_Sql_Expression $midTableName Name of intermediary table
     */
    function setMidTableName($midTableName) {
        if ($midTableName !== ($oldMidTableName = $this->midTableName)) {
            if ($this->immutable) throw self::immutableException();
            $this->midTableName = $midTableName;
            $this->srcNNIdsRelation = false;
            $this->destNNIdsRelation = false;
        }
    }

    /**
     * Returns SQL name of intermediary table.
     * If there is no intermediary table, FALSE is returned
     * 
     * @return string|bool|Ac_Sql_Expression
     */
    function getMidTableName() {
        return $this->midTableName;
    }

    /**
     * Assigns alias of intermediary table in generated queries.
     * Default alias is '_mid_'.
     * 
     * @param string $midTableAlias
     */
    function setMidTableAlias($midTableAlias) {
        if ($midTableAlias !== ($oldMidTableAlias = $this->midTableAlias)) {
            if ($this->immutable) throw self::immutableException();
            $this->midTableAlias = $midTableAlias;
        }
    }
    
    /**
     * Returns alias of intermediary table in generated queries.
     * 
     * Default alias is '_mid_'.
     * 
     * @return string
     */
    function getMidTableAlias() {
        return $this->midTableAlias;
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
            if ($this->immutable) throw self::immutableException();
            if (!$fieldLinks) throw new Ac_E_InvalidCall("\$fieldLinks must not be empty");
            $this->fieldLinks = $fieldLinks;
            $this->fieldLinksRev = array_flip($fieldLinks);
            $this->srcNNIdsRelation = false;
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
     * 
     * Only used when intermediary table was configured by setMidTableName().
     * If no intermediary table is configured, FALSE value is used.
     * 
     * @param array|bool $fieldLinks2 Fields mapping ($midTableField => $destTableField)
     */
    function setFieldLinks2($fieldLinks2) {
        if ($fieldLinks2 !== ($oldFieldLinks2 = $this->fieldLinks2)) {
            if ($this->immutable) throw self::immutableException();
            if (is_array($fieldLinks2)) {
                if (!$fieldLinks2) throw new Ac_E_InvalidCall("\$fieldLinks2 must not be empty");
                $this->fieldLinksRev2 = array_flip($fieldLinks2);
            } elseif ($fieldLinks2 === false) {
                $this->fieldLinksRev2 = false;
            } else {
                throw Ac_E_InvalidCall::wrongType('fieldLinks2', $fieldLinks2, array('array', 'false'));
            }
            $this->destNNIdsRelation = false;
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
        $srcIsUnique = (bool) $srcIsUnique;
        if ($srcIsUnique !== ($oldSrcIsUnique = $this->srcIsUnique)) {
            if ($this->immutable) throw self::immutableException();
            $this->srcIsUnique = $srcIsUnique;
            $this->srcNNIdsRelation = false;
        }
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
            if ($this->immutable) throw self::immutableException();
            $this->destIsUnique = $destIsUnique;
            $this->destNNIdsRelation = false;
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
            if ($this->immutable) throw self::immutableException();
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
            if ($this->immutable) throw self::immutableException();
            $this->srcNNIdsVarName = $srcNNIdsVarName;
            $this->srcNNIdsRelation = false;
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
            if ($this->immutable) throw self::immutableException();
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
     * Assigns name of variable in destination object that contains intermediary records
     *
     * FALSE = such variable does not exist / not used
     * 
     * @param string|bool $destNNIdsVarName
     */
    function setDestNNIdsVarName($destNNIdsVarName) {
        if ($destNNIdsVarName !== ($oldDestNNIdsVarName = $this->destNNIdsVarName)) {
            if ($this->immutable) throw self::immutableException();
            $this->destNNIdsVarName = $destNNIdsVarName;
            $this->destNNIdsRelation = false;
        }
    }

    /**
     * Returns name of variable in destination object that contains intermediary records
     * 
     * FALSE = such variable does not exist / not used
     * 
     * @return string|bool
     */
    function getDestNNIdsVarName() {
        return $this->destNNIdsVarName;
    }
    
    /**
     * Assigns if this relation is outgoing from source object (belongs to source table)
     * 
     * This property is used by Ac_Model_Mapper for keeping purposes 
     * and doesn't affect relation behavior.
     * 
     * @param bool $srcOutgoing
     */
    function setSrcOutgoing($srcOutgoing) {
        if ($srcOutgoing !== ($oldSrcOutgoing = $this->srcOutgoing)) {
            if ($this->immutable) throw self::immutableException();
            $this->srcOutgoing = $srcOutgoing;
        }
    }

    /**
     * Returns if this relation is outgoing from source object (belongs to source table)
     * 
     * This property is used by Ac_Model_Mapper for keeping purposes 
     * and doesn't affect relation behavior.
     * 
     * @return bool
     */
    function getSrcOutgoing() {
        return $this->srcOutgoing;
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
            if ($this->immutable) throw self::immutableException();
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
            if ($this->immutable) throw self::immutableException();
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
            if ($this->immutable) throw self::immutableException();
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

    /**
     * Assigns variable in DEST that denotes that SRC objects are fully loaded (For partially-loaded
     * associations feature)
     * 
     * Is only applicable when !$srcIsUnique.
     * 
     * FALSE = feature not used (any non-FALSE value of $destVarName is treated as the association
     * is fully loaded)
     * 
     * @param string|bool $destLoadedVarName
     */
    function setDestLoadedVarName($destLoadedVarName) {
        if ($destLoadedVarName !== ($oldDestLoadedVarName = $this->destLoadedVarName)) {
            if ($this->immutable) throw self::immutableException();
            $this->destLoadedVarName = $destLoadedVarName;
        }
    }

    /**
     * Returns variable in DEST that denotes that SRC objects are fully loaded (For partially-loaded
     * associations feature)
     * 
     * Is only applicable when !$srcIsUnique.
     * 
     * FALSE = feature not used (any non-FALSE value of $srcVarName is treated as the association
     * is fully loaded)
     * 
     * @return string|bool
     */
    function getDestLoadedVarName() {
        return $this->destLoadedVarName;
    }    

    /**
     * Assign database driver' instance that will be used to perform all
     * queries by a relation. 
     * 
     * In most cases, Db is auto-assigned when Application or Mapper are
     * provided.
     * 
     * @param Ac_Sql_Db $db
     */
    function setDb(Ac_Sql_Db $db) {
        if ($db !== ($oldDb = $this->db)) {
            if ($this->immutable) throw self::immutableException();
            $this->db = $db;
            $this->srcNNIdsRelation = false;
            $this->destNNIdsRelation = false;
        }
    }

    /**
     * Returns currently used database driver instance
     * 
     * @return Ac_Sql_Db
     */
    function getDb() {
        return $this->db;
    }
    
    /**
     * @deprecated
     * Use setDb()
     */
    function setDatabase(Ac_Sql_Db $db) {
        $this->setDb($db);
    }
    
    function getDatabase() {
        return $this->getDb();
    }

    /**
     * Assigns expression to sort fetched destination records before they 
     * are assigned to srcObject->srcVarName or returned
     * 
     * FALSE = don't enforce any sorting
     * 
     * @param string|Ac_Sql_Expression|bool $destOrdering
     */
    function setDestOrdering($destOrdering) {
        if ($destOrdering !== ($oldDestOrdering = $this->destOrdering)) {
            if ($this->immutable) throw self::immutableException();
            $this->destOrdering = $destOrdering;
        }
    }

    /**
     * Returns expression to sort fetched destination records before they 
     * are assigned to srcObject->srcVarName or returned
     * 
     * FALSE = no particular sort order
     * 
     * @return string|Ac_Sql_Expression|bool
     */
    function getDestOrdering() {
        return $this->destOrdering;
    }

    /**
     * Assigns expression to sort fetched source records before they 
     * are assigned to destObject->destVarName or returned
     * 
     * FALSE = don't enforce any sorting
     * 
     * @param string|Ac_Sql_Expression|bool $srcOrdering
     */
    function setSrcOrdering($srcOrdering) {
        if ($srcOrdering !== ($oldSrcOrdering = $this->srcOrdering)) {
            if ($this->immutable) throw self::immutableException();
            $this->srcOrdering = $srcOrdering;
        }
    }

    /**
     * Returns expression to sort fetched source records before they 
     * are assigned to destObject->destVarName or returned
     * 
     * FALSE = no particular sort order
     * 
     * @return string|Ac_Sql_Expression|bool
     */
    function getSrcOrdering() {
        return $this->srcOrdering;
    }

    /**
     * Sets extra expression to add to JOIN clause to source table
     * 
     * FALSE = feature is ignored
     * 
     * @param string|bool|Ac_Sql_Expression $srcExtraJoins
     */
    function setSrcExtraJoins($srcExtraJoins) {
        if ($srcExtraJoins !== ($oldSrcExtraJoins = $this->srcExtraJoins)) {
            if ($this->immutable) throw self::immutableException();
            $this->srcExtraJoins = $srcExtraJoins;
        }
    }

    /**
     * Returns extra expression to add to JOIN clause to source table
     * 
     * FALSE = feature is ignored
     * 
     * @return string|bool|Ac_Sql_Expression
     */
    function getSrcExtraJoins() {
        return $this->srcExtraJoins;
    }

    /**
     * Sets extra expression to add to JOIN clause to destination table
     * 
     * FALSE = feature is ignored
     * 
     * @param string|bool|Ac_Sql_Expression $destExtraJoins
     */
    function setDestExtraJoins($destExtraJoins) {
        if ($destExtraJoins !== ($oldDestExtraJoins = $this->destExtraJoins)) {
            if ($this->immutable) throw self::immutableException();
            $this->destExtraJoins = $destExtraJoins;
        }
    }

    /**
     * Returns extra expression to add to JOIN clause to destination table
     * 
     * FALSE = feature is ignored
     * 
     * @return string|bool|Ac_Sql_Expression
     */
    function getDestExtraJoins() {
        return $this->destExtraJoins;
    }
    
    /**
     * Assigns part of WHERE clause on source table (used when source
     * records are fetched)
     * 
     * FALSE = feature not used
     * 
     * @param string|bool|Ac_Sql_Expression $srcWhere
     */
    function setSrcWhere($srcWhere) {
        if ($srcWhere !== ($oldSrcWhere = $this->srcWhere)) {
            if ($this->immutable) throw self::immutableException();
            $this->srcWhere = $srcWhere;
        }
    }

    /**
     * Returns part of WHERE clause on source table (used when source
     * records are fetched)
     * 
     * FALSE = feature not used
     * 
     * @return string|bool|Ac_Sql_Expression
     */
    function getSrcWhere() {
        return $this->srcWhere;
    }

    /**
     * Assigns part of WHERE clause on destination table (used when destination
     * records are fetched)
     * 
     * FALSE = feature not used
     * 
     * @param string|bool|Ac_Sql_Expression $destWhere
     */
    function setDestWhere($destWhere) {
        if ($destWhere !== ($oldDestWhere = $this->destWhere)) {
            if ($this->immutable) throw self::immutableException();
            $this->destWhere = $destWhere;
        }
    }

    /**
     * Returns part of WHERE clause on destination table (used when destination
     * records are fetched)
     * 
     * FALSE = feature not used
     * 
     * @return string|bool|Ac_Sql_Expression
     */
    function getDestWhere() {
        return $this->destWhere;
    }
    
    /**
     * Assigns part of WHERE clause on intermediary table (when both source
     * or destination records are fetched). 
     * 
     * Used only when $midTableName is set.
     * FALSE = feature not used
     * 
     * @param string|bool|Ac_Sql_Expression $midWhere
     */
    function setMidWhere($midWhere) {
        if ($midWhere !== ($oldMidWhere = $this->midWhere)) {
            if ($this->immutable) throw self::immutableException();
            $this->midWhere = $midWhere;
            $this->srcNNIdsRelation = false;
            $this->destNNIdsRelation = false;
        }
    }

    /**
     * Returns part of WHERE clause on intermediary table (when both source
     * or destination records are fetched). 
     * 
     * Used only when $midTableName is set.
     * FALSE = feature not used
     * 
     * @return string|bool|Ac_Sql_Expression
     */
    function getMidWhere() {
        return $this->midWhere;
    }

    /**
     * Assigns qualifier field of source records in destination arrays.
     * 
     * Qualifier value is used to determine key in $srcObject->srcVarName array.
     * If FALSE is set (by default), qualifier is not used and array keys are 
     * simply numeric (0, 1, 2...)
     * 
     * @param string|bool $srcQualifier
     */
    function setSrcQualifier($srcQualifier) {
        if ($srcQualifier !== ($oldSrcQualifier = $this->srcQualifier)) {
            if ($this->immutable) throw self::immutableException();
            $this->srcQualifier = $srcQualifier;
        }
    }

    /**
     * Returns qualifier field of source records in destination arrays.
     * 
     * Qualifier value is used to determine key in $srcObject->srcVarName array.
     * If FALSE is returned (by default), qualifier is not used and array keys are 
     * simply numeric (0, 1, 2...)
     * 
     * @return string|bool
     */
    function getSrcQualifier() {
        return $this->srcQualifier;
    }

    /**
     * Assigns qualifier field of destinaton records in source arrays.
     * 
     * Qualifier value is used to determine key in $destObject->destVarName array.
     * If FALSE is set (by default), qualifier is not used and array keys are 
     * simply numeric (0, 1, 2...)
     * 
     * @param string|bool $destQualifier
     */
    function setDestQualifier($destQualifier) {
        if ($destQualifier !== ($oldDestQualifier = $this->destQualifier)) {
            if ($this->immutable) throw self::immutableException();
            $this->destQualifier = $destQualifier;
        }
    }

    /**
     * Returns qualifier field of destination records in source arrays.
     * 
     * Qualifier value is used to determine key in $destObject->destVarName array.
     * If FALSE is returned (by default), qualifier is not used and array keys are 
     * simply numeric (0, 1, 2...)
     * 
     * @return string|bool
     */
    function getDestQualifier() {
        return $this->destQualifier;
    }

    /**
     * Sets whether relation is immutable or not.
     * 
     * Immutable relation throws an Ac_E_InvalidUsage exception every time
     * when attempt is being made to alter its' properties.
     * 
     * @param bool $immutable
     */
    function setImmutable($immutable) {
        $this->immutable = $immutable;
    }

    /**
     * Returns whether relation is immutable or not.
     * 
     * Immutable relation throws an Ac_E_InvalidUsage exception every time
     * when attempt is being made to alter its' properties.
     * 
     * @return bool
     */
    function getImmutable() {
        return $this->immutable;
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
        $res = $this->getSrcOrDest (
            $srcData, 
            $matchMode, 
            $defaultValue, 
            $hasDef, 
            $this->fieldLinks, 
            $this->fieldLinks2, 
            $this->destIsUnique, 
            $this->destTableName, 
            'instantiateDest', 
            $this->destOrdering, 
            $this->destExtraJoins, 
            $this->destWhere,
            $this->srcNNIdsVarName
        );
        return $res;
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
        $res = $this->getSrcOrDest ($destData, 
            $matchMode, 
            $defaultValue, 
            $hasDef, 
            $this->fieldLinksRev, 
            $this->fieldLinksRev2, 
            $this->srcIsUnique, 
            $this->srcTableName, 
            'instantiateSrc', 
            $this->srcOrdering, 
            $this->srcExtraJoins, 
            $this->srcWhere,
            $this->destNNIdsVarName
        );
        return $res;
    }
    
    // ------------------------ count / delete / load methods -------
    
    function countDest ($srcData, $separate = true, $matchMode = AMR_PLAIN_RESULT) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        return $this->countSrcOrDest($srcData, $separate, $matchMode, $this->fieldLinks, $this->fieldLinks2, 
            $this->destIsUnique, $this->destTableName, $this->srcNNIdsVarName);
    }
    
    function countSrc ($destData, $separate = true, $matchMode = AMR_PLAIN_RESULT) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        return $this->countSrcOrDest($destData, $separate, $matchMode, $this->fieldLinksRev, 
            $this->fieldLinksRev2, $this->srcIsUnique, $this->srcTableName, $this->destNNIdsVarName);
    }
    
    function deleteDest (& $srcData) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        return $this->deleteSrcOrDest($srcData, $this->fieldLinks, $this->fieldLinks2, $this->destTableName, $this->destWhere);
    }
    
    function deleteSrc (& $destData) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        return $this->deleteSrcOrDest($destData, $this->fieldLinksRev, $this->fieldLinksRev2, $this->srcTableName, $this->srcWhere);
    }
    
    function loadDest (& $srcData, $dontOverwriteLoaded = true, $biDirectional = true, $returnAll = true) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        //if (!$this->srcVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $srcVarName is not set');
        $defaultValue = $this->destIsUnique? null : array();
        $biDirectional = $biDirectional && strlen($this->destVarName);

        $lvn = $this->srcLoadedVarName;
        if ($lvn !== false && !$this->destMapper) {
            trigger_error("\$srcLoadedVarName is set, but \$destMapper isn't - partially-loaded 
                associations will not be supported and \$srcLoadedVarName is ignored", E_USER_NOTICE);
            $lvn = false;
        }
        
        $res = $this->loadSrcOrDest ($srcData, $defaultValue, $this->srcVarName, $this->destVarName, 
            $dontOverwriteLoaded, $biDirectional, $this->fieldLinks, $this->fieldLinks2, $this->destIsUnique, 
                $this->srcIsUnique, $this->destTableName, 'instantiateDest', $this->destOrdering, 
                $this->destExtraJoins, $this->destWhere, $this->destQualifier, $this->srcQualifier, 
                $this->srcNNIdsVarName, $returnAll, $lvn);
        return $res;
    }
    
    function loadSrc (& $destData, $dontOverwriteLoaded = true, $biDirectional = true, $returnAll = true) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        if (!$this->destVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $destVarName is not set');
        $defaultValue = $this->srcIsUnique? null : array();
        $biDirectional = $biDirectional && strlen($this->srcVarName);
        
        if (strlen($this->midTableName)) {
            $fl1 = $this->fieldLinksRev2;
            $fl2 = $this->fieldLinksRev;
        } else {
            $fl1 = $this->fieldLinksRev;
            $fl2 = $this->fieldLinksRev2;
        }
        
        $lvn = $this->destLoadedVarName;
        if ($lvn !== false && !$this->srcMapper) {
            trigger_error("\$destLoadedVarName is set, but \$srcMapper isn't - partially-loaded 
                associations will not be supported and \$destLoadedVarName is ignored", E_USER_NOTICE);
            $lvn = false;
        }
        
        return $this->loadSrcOrDest ($destData, $defaultValue, $this->destVarName, $this->srcVarName, 
            $dontOverwriteLoaded, $biDirectional, $fl1, $fl2, $this->srcIsUnique, $this->destIsUnique, 
            $this->srcTableName, 'instantiateSrc', $this->srcOrdering, $this->srcExtraJoins, 
            $this->srcWhere, $this->srcQualifier, $this->destQualifier, $this->destNNIdsVarName, $returnAll,
            $lvn);
    }
    
    function getStrMidWhere($alias = false) {
        if (is_array($this->midWhere)) $res = $this->db->valueCriterion($this->midWhere, $alias);
        else $res = $this->midWhere;
        return $res;
    }
    
    function loadDestNNIds($srcData, $dontOverwriteLoaded = true) {
        if (!$this->midTableName) trigger_error("This function is only applicable to relations with midTableName set", E_USER_ERROR);
        if (!strlen($this->srcNNIdsVarName)) trigger_error("Property \$srcNNIdsVarName should be set to non-empty string to use this method", E_USER_ERROR); 
        if (!$this->destNNIdsRelation) {
            $relConfig = array(
                'db' => $this->db,
                'srcTableName' => $this->srcTableName,
                'srcMapperClass' => $this->srcMapperClass,
                'srcVarName' => $this->srcNNIdsVarName,

                'destTableName' => $this->midTableName,
                'destWhere' => $this->getStrMidWhere(),
                'fieldLinks' => $this->fieldLinks,

                'srcIsUnique' => $this->srcIsUnique,
                'destIsUnique' => false,
            );
            $this->destNNIdsRelation = new Ac_Model_Relation($relConfig);
        }
        $res = $this->destNNIdsRelation->loadDest($srcData, $dontOverwriteLoaded);
        $this->fixNNIds($srcData, array_keys($this->fieldLinks2), $this->srcNNIdsVarName);
    }
    
    function loadSrcNNIds($destData, $dontOverwriteLoaded = true) {
        if (!$this->midTableName) trigger_error("This function is only applicable to relations with midTableName set", E_USER_ERROR);
        if (!strlen($this->destNNIdsVarName)) trigger_error("Property \$destNNIdsVarName should be set to non-empty string to use this method", E_USER_ERROR);
        if (!$this->srcNNIdsRelation) {
            $relConfig = array(
                'db' => $this->db,
                'destTableName' => $this->destTableName,
                'destMapperClass' => $this->destMapperClass,
                'destVarName' => $this->destNNIdsVarName,

                'srcTableName' => $this->midTableName,
                'srcWhere' => $this->midWhere,
                'fieldLinks' => $this->fieldLinks2,

                'srcIsUnique' => false,
                'destIsUnique' => $this->srcIsUnique,
            );
            $this->srcNNIdsRelation = new Ac_Model_Relation($relConfig);
        }
        $this->srcNNIdsRelation->loadSrc($destData, $dontOverwriteLoaded);
        $this->fixNNIds($destData, array_values($this->fieldLinks), $this->destNNIdsVarName);
    }
    
    /**
     * Counts destination objects and stores result in $srcCountVarName of each corresponding $srcData object
     */
    function loadDestCount ($srcData, $dontOverwriteLoaded = true) {
        if (!$this->destTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent destination!');
        if (!$this->srcCountVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $srcCountVarName is not set');
        return $this->loadSrcOrDestCount ($srcData, $this->srcCountVarName, $dontOverwriteLoaded, 
            $this->fieldLinks, $this->fieldLinks2, $this->destTableName, $this->srcNNIdsVarName);
    }
    
    /**
     * Counts source objects and stores result in $destCountVarName of each corresponding $destData object
     */
    function loadSrcCount ($destData, $dontOverwriteLoaded = true) {
        if (!$this->srcTableName) trigger_error ('Can\'t '.__FUNCTION__.'() with non-persistent source!');
        if (!$this->destCountVarName)  trigger_error ('Can\'t '.__FUNCTION__.'() when $destCountVarName is not set');
        return $this->loadSrcOrDestCount ($destData, $this->destCountVarName, $dontOverwriteLoaded, 
            $this->fieldLinksRev, $this->fieldLinksRev2, $this->srcTableName, $this->destNNIdsVarName);
    }
    
    function getDestJoin ($srcAlias = false, $destAlias = false, $type = 'left', $midAlias = false) {
        if (!$this->midTableName) {
            $res = $this->getJoin($type, $srcAlias, $this->destTableName, $destAlias, $this->fieldLinks);
        } else {
            if ($midAlias === false) $midAlias = $this->midTableAlias;
            $res = $this->getJoin($type, $srcAlias, $this->midTableName, $midAlias, $this->fieldLinks, $this->getStrMidWhere($midAlias));
            $res .= ' '.$this->getJoin($joinType, $midAlias, $this->destTableName, $destAlias, $this->fieldLinks2);
        }
        return $res;
    }
    
    function getSrcJoin ($srcAlias = false, $destAlias = false, $type = 'left', $midAlias = false) {
        if (!$this->midTableName) {
            $res = $this->getJoin($type, $destAlias, $this->srcTableName, $srcAlias, $this->fieldLinksRev);
        } else {
            if ($midAlias === false) $midAlias = $this->midTableAlias;
            $res = $this->getJoin($type, $destAlias, $this->midTableName, $midAlias, $this->fieldLinksRev2, $this->getStrMidWhere($midAlias));
            $res .= ' '.$this->getJoin($joinType, $midAlias, $this->srcTableName, $srcAlias, $this->fieldLinksRev);
        }
        return $res;
    }
    
    function getCritForSrcOfDest ($dest, $srcAlias = '', $default = '0') {
        if ($this->midTableName) trigger_error ("This method cannot be used when midTableName is set!", E_USER_ERROR);
        if (!$this->srcTableName) trigger_error ("Cannot ".__FUNCTION__."() with non-persistent source!", E_USER_ERROR);
        $res = $this->makeCritForSrcOrDest($dest, $srcAlias, $this->fieldLinksRev, $default);
        return $res;        
    }
    
    function getCritForDestOfSrc ($src, $destAlias = '', $default = '0') {
        if ($this->midTableName) trigger_error ("This method cannot be used when midTableName is set!", E_USER_ERROR);
        if (!$this->destTableName) trigger_error ("Cannot ".__FUNCTION__."() with non-persistent destination!", E_USER_ERROR);
        $res = $this->makeCritForSrcOrDest($src, $destAlias, $this->fieldLinks, $default);
        return $res;        
    }
    
    // ------------------------ PRIVATE METHODS -----------------------
    /**
     * Extracts keys from source array and makes map (recordKeys => srcArrayKey). Format of $map will be array($keys, $sourceKey). By traversing the map later,
     * one can find corresponding records in the right table. 
     */
    protected function makeMapAndGetAllValues(& $source, & $map, & $values, $keys, 
        $varToCheck = false, $midIdsVar = false, &$midMap = null, & $midVals = null, $midKeys2 = array(), 
        $useKeysForMidMapWriteKeys = false, & $alreadyLoaded = null, $loadedVarName = false, 
        & $alreadyLoadedByPk = null) {
        $map = array();
        if (!is_array($midMap)) $midMap = array();
        if (!is_array($alreadyLoaded)) $alreadyLoaded = array();
        if (!is_array($alreadyLoadedByPk)) $alreadyLoadedByPk = array();
        $values = array();
        if (!is_array($midVals)) $midVals = array();
        foreach(array_keys($source) as $k) {
            $srcItem = $source[$k];
            $hasIds = false;
            $items = array();
            $pks = array();
            if ($varToCheck !== false) {
                $v = false;
                $hasVar = !$this->isVarEmpty($srcItem, $varToCheck, $v);
                if ($hasVar) {
                    if (!is_null($v) && !(is_array($v) && !count($v))) {
                        $alreadyLoaded[$k] = $v;
                    } else {
                        $v = false;
                    }
                }
                if ($hasVar) {
                    if ($loadedVarName !== false) {
                        if (is_object($v) && $v instanceof Ac_Model_Object && $v->hasFullPrimaryKey()) {
                            $pk = $v->getPrimaryKey();
                            $itemsByPk[$pk] = $alreadyLoadedByPk[$pk] = $v;
                        } elseif (is_array($v)) {
                            $items = $v;
                            foreach ($v as $item) {
                                if (is_object($item) && $item instanceof Ac_Model_Object 
                                    && $item->hasFullPrimaryKey()) {
                                    $pk = $item->getPrimaryKey();
                                    $pks[$pk] = true;
                                    $alreadyLoadedByPk[$pk] = $item;
                                }
                            }
                        }
                        if ($this->getValue($srcItem, $loadedVarName)) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }
            }
            if ($midIdsVar !== false && $midKeys2) {
                $hasIds = is_array($ids = $this->getValue($srcItem, $midIdsVar));
                if ($hasIds) {
                    $ids = $this->nnIdsToValues($ids, $midKeys2);
                    foreach ($ids as $v) {
                        if (is_array($v)) {
                            $mk = implode('-', $v);
                        }
                            else $mk = ''.$v;
                        $midVals[$mk] = $v;
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
            if (!$hasIds) {
                $vals = $this->getValues($srcItem, $keys, false, false);
                $map[] = array($vals, $k, $items, $pks);
                $values[] = $vals;
            }
        }
    }
    
    protected function getAllValues(& $source, & $values, $keys, $nnIdsVar = false, & $midVals = null, 
        $midKeys = array()) {
        
        $values = array();
        if (!is_array($midVals)) $midVals = array();
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
                        $midVals[$mk] = $v;
                    }
                }
            }
            if (!$hasIds) {
                $values[] = $this->getValues($srcItem, $keys, false, false);
            }
        }
    }
    
    protected function getSrcOrDest ($data, $matchMode, $defaultValue, $hasDefaultValue, $fieldLinks, $fieldLinks2, 
        $isUnique, $tableName, $instanceFunc, $ordering, $extraJoins, $extraWhere, $nnIdsVar = false) {
        $keys = array_keys($fieldLinks);
        $midMap = false;
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $midKeys = is_array($fieldLinks2)? array_values($fieldLinks2) : array();
            if ($matchMode & AMR_ORIGINAL_KEYS || $matchMode == AMR_RECORD_KEYS) {
                $map = array();
                $midMap = array();
                $this->makeMapAndGetAllValues($data, $map, $values, $keys, false, $nnIdsVar, 
                    $midMap, $midValues, $midKeys, $matchMode == AMR_RECORD_KEYS);
            } else {
                $this->getAllValues($data, $values, $keys, $nnIdsVar, $midValues, $midKeys);
                
            }

            $rows = array();
            $rows2 = array();

            if ($values) {
                
                $rows = $this->getWithValues($values, 
                    $this->midTableName? array($fieldLinks, $fieldLinks2) : Ac_Util::array_values($fieldLinks), 
                    true, $isUnique, $matchMode > 0, $tableName, $instanceFunc, false, false, $this->midTableName, 
                    $ordering, $extraJoins, $extraWhere, 
                    $midValues? array_values($fieldLinks2) : false, $midValues);

                if ($midValues) {
                    $tmp = $rows;
                    list ($rows, $rows2) = $tmp;
                }
                
                
                
            } else {
                if ($midValues) {
                    $rows2 = $this->getWithValues($midValues, 
                            array_values($fieldLinks2), 
                            true, $isUnique, true, $tableName, $instanceFunc, false, false, false, 
                            $ordering, $extraJoins, $extraWhere);
                } else {
                    $rows2 = array();
                }
            }

            $defaultValue = $isUnique? $defaultValue : array();
            
            if ($matchMode & AMR_ORIGINAL_KEYS) {
                $res = array();
                if ($rows) $this->unmapResult($keys, $map, $matchMode, $res, $rows, $defaultValue, $isUnique, 
                    false);
                if ($midMap) {
                    $this->unmapResult(array_values($fieldLinks2), $midMap, $matchMode, $res, $rows2, 
                        $defaultValue, $isUnique, true);                    
                }
            } else {
                $res = $rows;
                if ($matchMode === AMR_RECORD_KEYS && $midMap) {
                    $this->unmapResult(array_values($fieldLinks2), $midMap, $matchMode, $res, $rows2, 
                        $defaultValue, $isUnique, true);                    
                }
                if (!$res && $rows2) {
                    if ($isUnique) $res = array_values($rows2);
                    else foreach ($rows2 as $k => $rows) $res = array_merge($res, $rows);
                }
            }
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            $values = $this->getValues($data, $keys, false, false);
            if ($nnIdsVar && isset($data->$nnIdsVar) && is_array($ids = $data->$nnIdsVar)) {
                $ids = $this->nnIdsToValues($ids, array_values($fieldLinks2));
                $rows = $this->getWithValues($ids, array_values($fieldLinks2), 
                    true, $isUnique, false, $tableName, $instanceFunc, false, false, 
                    false, $ordering, $extraJoins, $extraWhere);
            } else {
                $rows = $this->getWithValues($values, 
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
                    if ($matchMode == AMR_ALL_ORIGINAL_KEYS) {
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
                    if ($matchMode == AMR_ALL_ORIGINAL_KEYS) {
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
    
    protected function countSrcOrDest ($data, $separate = true, $matchMode = AMR_ALL_ORIGINAL_KEYS, $fieldLinks, 
        $fieldLinks2, $isUnique, $tableName, $nnIdsVarName = false) {
        $keys = array_keys($fieldLinks);
        if (is_array($data)) { // we assume that this is array of objectn countSrcOrs or array of rows
            $values = array();
            $midKeys = is_array($fieldLinks2)? array_values($fieldLinks2) : array();
            if ($matchMode === AMR_PLAIN_RESULT && is_array($data) && $separate) {
                trigger_error("Using countSrc or countDest(array, true, AMR_PLAIN_RESULT) "
                    . "does not make sense; using AMR_ALL_ORIGINAL_KEYS instead", E_USER_NOTICE);
                $matchMode = AMR_ALL_ORIGINAL_KEYS;
            }
            if ($separate && $matchMode !== AMR_PLAIN_RESULT) {
                $map = array();
                $this->makeMapAndGetAllValues($data, $map, $values, $keys, 
                    false, $nnIdsVarName, $midMap, $midValues, $midKeys, $matchMode === AMR_RECORD_KEYS);
            } else {
                $midMap = array();
                $this->getAllValues($data, $values, $keys, $nnIdsVarName, $midValues, $midKeys);
            }
            if ($values) {
                
                $keys2 = false;
                $values2 = false;
                $countKeys = $this->midTableName? $fieldLinks2 : Ac_Util::array_values($fieldLinks);
                
                if (!$separate && $midValues) {
                    $keys2 = array_values($fieldLinks2);
                    $values2 = $midValues;
                    $countKeys = array($fieldLinks, $fieldLinks2);
                }
                
                $counts = $this->countWithValues($values, $countKeys, 
                    true, $separate, $matchMode > 0, $tableName, false, false, $this->midTableName, 
                    $this->midTableName? $fieldLinks : false, $keys2, $values2);
            } else {
                $counts = array();
                if (!$separate) {
                    if ($midValues) {
                        $counts = $this->countWithValues($midValues, array_values($fieldLinks2), true, $separate, $matchMode > 0, $tableName);
                    } else {
                        $counts = 0;
                    }
                }
            }
            if (!$separate) {
                $res = $counts;
            } else {
                if ($counts) {
                    if ($matchMode === AMR_PLAIN_RESULT) {
                        $res = $counts;
                    } else {
                        $this->unmapResult($keys, $map, $matchMode, $res, $counts, 0, true, false);
                    }
                }
                if ($midMap) {
                    $this->unmapResult(array_values($fieldLinks2), $midMap, $matchMode, $res, array(), 
                        0, false, true, true);
                }
            }
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            if ($nnIdsVarName !== false) {
                $nnIds = $this->getValue($data, $nnIdsVarName);
            } else {
                $nnIds = null;
            }
            if (is_array($nnIds)) $res = count($nnIds);
            else {
                $values = $this->getValues($data, $keys, false, false);
                $res = $this->countWithValues($values, $this->midTableName? $fieldLinks2 : Ac_Util::array_values($fieldLinks), false, false, false, $tableName, false, false, $this->midTableName, $this->midTableName? $fieldLinks : false);
            }
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    protected function deleteSrcOrDest ($data, $fieldLinks, $fieldLinks2, $tableName, $where = false) {
        $keys = array_keys($fieldLinks);
        if (is_object($data)) {
            $xd = array(& $data);
            return $this->deleteSrcOrDest($xd, $fieldLinks, $fieldLinks2, $tableName, $where);
        }
        if (is_array($data)) { // we assume that this is array of objects or array of rows
            $values = array();
            $this->getAllValues($data, $values, $keys);
            if ($this->midTableName) {
                $midValues = $this->getWithValues($values, Ac_Util::array_values($fieldLinks), true, false, false, $this->midTableName, '_rowInstance', false, false, '', false, $where );
                $rightKeyValues = array();
                $this->getAllValues($midValues, $rightKeyValues, array_keys($fieldLinks2));
                $res = true;
                $this->db->startTransaction();
                if (!$this->deleteWithValues($values, true, array_values($fieldLinks))) $res = false;
                if (!$this->deleteWithValues($midValues, true, Ac_Util::array_values($fieldLinks2))) $res = false;
                if (!$res) $this->db->rollback();
                    else $this->db->commit();
            } else {
                $res = $this->deleteWithValues($values, true, Ac_Util::array_values($fieldLinks), $tableName, false, $where);
            }
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
    }
    
    protected function loadSrcOrDest (& $data, $defaultValue, $varName, $otherVarName, $dontOverwriteLoaded, $biDirectional, 
            $fieldLinks, $fieldLinks2, $isUnique, $otherIsUnique, $tableName, $instanceFunc, $ordering = false, 
            $extraJoins = false, $extraWhere = false, $qualifier = false, $otherQualifier = false, 
            $nnIdsVar = false, $returnAll = false, $loadedVarName = false) {
        $keys = array_keys($fieldLinks);
        $res = array();
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $map = array();
            $midValues = array();
            
            $alreadyLoaded = array();
            $alreadyLoadedByPk = array();

            $varToCheck = $dontOverwriteLoaded? $varName : false;
            $this->makeMapAndGetAllValues($data, $map, $values, $keys, $varToCheck, $nnIdsVar, $midMap, 
                $midValues, is_array($fieldLinks2)? array_values($fieldLinks2) : array(), false, 
                $alreadyLoaded, $loadedVarName, $alreadyLoadedByPk);
            
            $rows = array();
                
            if ($values) {
                
                $rows = $this->getWithValues($values, 
                        $this->midTableName? array($fieldLinks, $fieldLinks2) : Ac_Util::array_values($fieldLinks), 
                        true, $isUnique, true, $tableName, $instanceFunc, false, false, $this->midTableName, 
                        $ordering, $extraJoins, $extraWhere, 
                        $midValues? array_values($fieldLinks2) : false, $midValues);
                
                // _getWithValues returns two mapped sets of same rows in that case
                
                if ($midValues) {
                    list($rows, $rows2) = $rows;
                    if ($rows2) $res = $rows2;
                }
                
                $this->unmap($keys, $map, $rows, $data, $defaultValue, $biDirectional, $varName, 
                    $otherVarName, $isUnique, $otherIsUnique, $qualifier, $otherQualifier, false, 
                    $loadedVarName);
                
                if ($midMap) {
                    if (!$midValues) $rows2 = array();
                    $this->unmap(array_values($fieldLinks2), $midMap, $rows2, $data, $defaultValue, $biDirectional, 
                        $varName, $otherVarName, $isUnique, $otherIsUnique, $qualifier, $otherQualifier, true,
                        $loadedVarName);
                }
                
                
            } elseif ($midMap) {
                // Degrade to loading dest with known values
                if ($midValues) {
                    $rows = $this->getWithValues($midValues, 
                            array_values($fieldLinks2), 
                            true, $isUnique, true, $tableName, $instanceFunc, false, false, false, 
                            $ordering, $extraJoins, $extraWhere);
                } else {
                    $rows = array();
                }
                $this->unmap(array_keys($fieldLinks2), $midMap, $rows, $data, $defaultValue, $biDirectional, 
                    $varName, $otherVarName, $isUnique, $otherIsUnique, $qualifier, $otherQualifier, true,
                    $loadedVarName);
            }
            if (!$res) $res = $rows;
            if ($returnAll && $alreadyLoaded) $res['__alreadyLoaded'] = $alreadyLoaded;
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            $loaded = false;
            $items = array();
            $pks = array();
            $skip = false;
            if ($dontOverwriteLoaded) {  
                $isEmpty = $this->isVarEmpty($data, $varName, $loaded);
                if (!$isEmpty) {
                    if ($loadedVarName !== false) {
                        if ($this->getValue($data, $loadedVarName)) {
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
                if ($nnIdsVar && isset($data->$nnIdsVar) && is_array($ids = $data->$nnIdsVar)) {
                    $values = $this->nnIdsToValues($ids, array_values($fieldLinks2));
                    $rows = $this->getWithValues($values,  array_values($fieldLinks2), 
                        true, $isUnique, false, $tableName, $instanceFunc, false, false, 
                        false, $ordering, $extraJoins, $extraWhere);
                } else {
                    $values = $this->getValues($data, $keys, false, false);
                    $rows = $this->getWithValues($values, 
                        $this->midTableName? array($fieldLinks, $fieldLinks2) : Ac_Util::array_values($fieldLinks), 
                        false, $isUnique, false, $tableName, $instanceFunc, false, false, 
                        $this->midTableName, $ordering, $extraJoins, $extraWhere);
                }
                if ($rows) {
                    $toSet = $rows;
                    if ($biDirectional) $this->linkBack($rows, $data, $otherVarName, !$isUnique, $otherIsUnique, $otherQualifier);
                } else $toSet = $defaultValue;
                if ($items && is_array($toSet)) {
                    $toSet = $this->mergeByPk($toSet, $items, $pks);
                }
                $this->setVal($data, $varName, $toSet, $qualifier);
                if ($loadedVarName !== false) {
                    $this->setVal($data, $loadedVarName, true);
                }
                if ($isUnique) $res = array(& $rows); else $res = $rows;
            } else {
                if ($returnAll && !is_null($loaded)) {
                    $res = is_array($loaded)? $loaded : array($loaded);
                } else {
                    $res = array();
                }
            }
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $res;
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
    
    protected function unmap($keys, $map, & $rows, & $data, $defaultValue, $biDirectional, $varName, $otherVarName, 
            $isUnique, $otherIsUnique, $qualifier, $otherQualifier, $mapMultiple = false, 
            $loadedVarName = false) {
        
        if (count($keys) === 1) {
            foreach ($map as $m) {
                $dataKey = $m[1];
                if ($mapMultiple && !$isUnique) {
                    $toSet = array();
                    foreach ($m[0] as $rowKey) {
                        if (isset($rows[$rowKey])) {
                            $toSet = array_merge($toSet, $rows[$rowKey]);
                            if ($biDirectional) {
                                $this->linkBack($rows[$rowKey], $data[$dataKey], $otherVarName, 
                                    !$isUnique, $otherIsUnique, $otherQualifier); 
                            }
                        }
                    }
                } else {
                    if (isset($rows[$rowKey = $m[0][0]])) {
                        $toSet = $rows[$rowKey];
                        if ($biDirectional) $this->linkBack($rows[$rowKey], $data[$dataKey], $otherVarName, 
                            !$isUnique, $otherIsUnique, $otherQualifier); 
                    } else {
                        $toSet = $defaultValue;
                    }
                }
                if (!$isUnique && $loadedVarName) {
                    $toSet = $this->mergeByPk($toSet, $m[2], $m[3]);
                }
                $this->setVal($data[$dataKey], $varName, $toSet, $qualifier);
                if ($loadedVarName !== false) {
                    $this->setVal($data[$dataKey], $loadedVarName, true);
                }
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
                           if ($biDirectional) $this->linkBack($row, $data[$dataKey], $otherVarName, 
                               !$isUnique, $otherIsUnique, $otherQualifier);
                        }
                    }
                } else {
                    $rowPath = $m[0];
                    $row = Ac_Util::simpleGetArrayByPath($rows, $rowPath, false);
                    if ($row !== false) {
                       $toSet = $row;
                       if ($biDirectional) $this->linkBack($row, $data[$dataKey], $otherVarName, !$isUnique, $otherIsUnique, $otherQualifier);
                    }
                    else {
                        $toSet = $defaultValue;
                    }
                }
                if (!$isUnique && $loadedVarName !== false) {
                    $toSet = $this->mergeByPk($toSet, $m[2], $m[3]);
                }
                $this->setVal($data[$dataKey], $varName, $toSet, $qualifier);
                if ($loadedVarName !== false) {
                    $this->setVal($data[$dataKey], $loadedVarName, true);
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
    
    protected function loadSrcOrDestCount ($data, $varName, $dontOverwriteLoaded, $fieldLinks, $fieldLinks2, 
        $tableName, $nnIdsVar = false) {
        $keys = array_keys($fieldLinks);
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $map = array();
            
            $this->makeMapAndGetAllValues($data, $map, $values, $keys, 
                $dontOverwriteLoaded? $varName : false, $nnIdsVar, $midMap, $midValues,
                is_array($fieldLinks2)? array_values($fieldLinks2) : array());
            
            if ($midMap) {
                foreach ($midMap as $m) {
                    $dataKey = $m[1];
                    $this->setVal($data[$dataKey], $varName, count($m[0])); 
                }
            }

            if ($values) {
                
                $counts = $this->countWithValues($values, 
                    $this->midTableName? $fieldLinks2 : Ac_Util::array_values($fieldLinks), 
                    true, true, true, $tableName, false, false, $this->midTableName, 
                    $fieldLinks);
                
                if (count($keys) === 1) {
                    foreach ($map as $m) {
                        $dataKey = $m[1];
                        if (isset($counts[$countKey = $m[0][0]])) {
                            $toSet = $counts[$countKey];
                        } else $toSet = 0;
                        $this->setVal($data[$dataKey], $varName, $toSet); 
                    }
                } else {
                    foreach ($map as $m) {
                        $countPath = $m[0];
                        $dataKey = $m[1];
                        $count = Ac_Util::simpleGetArrayByPath($counts, $countPath, 0);
                        $this->setVal($data[$dataKey], $varName, $count);
                    }
                }
            }
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } elseif (is_object($data)) {
            if ($nnIdsVar && is_array($v = $this->getValue($data, $nnIdsVar))) {
                $this->setVal($data, $varName, count($v));
            }
            if (!$dontOverwriteLoaded || $this->isVarEmpty($data, $varName)) {  
                $values = $this->getValues($data, $keys, false, false);
                $this->setVal($data, $varName, $k = $this->countWithValues(
                    $values, 
                    $this->midTableName? $fieldLinks2 : Ac_Util::array_values($fieldLinks), 
                    false, false, false, $tableName, false, false, $this->midTableName, $fieldLinks)
                );
            }
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
    }
    
    protected function getWithValues ($values, $keys, $multipleValues, $unique, $byKeys, $tableName, $ifun, 
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
            $lta = $this->midTableAlias.'.';
            $crit = $this->makeSqlCriteria($values, $selKeys, $this->midTableAlias);
            $extraJoinCrit = false;
            if ($keys2) {
                $join = 'RIGHT';
                $notNullC = array();
                foreach (array_keys($allKeys[1]) as $fieldName) {
                    $notNullC[$fieldName] = $this->midTableAlias.".".$fieldName." IS NOT NULL";
                }
                $notNullC = "(".implode(" AND ", $notNullC).")";
                $extraJoinCrit = $crit;
                $crit = $notNullC." OR (".$this->makeSqlCriteria($values2, $keys2, strlen($ta)? $ta : $tableName).")";
            } else {
                $join = 'INNER';
            }
            $fromWhere = ' FROM '.$this->db->n($midTableName).' AS '.$this->midTableAlias
                .' '.$this->getJoin($join, $this->midTableAlias, $tableName, $ta, $allKeys[1], $extraJoinCrit);
        } else {
            $fromWhere = ' FROM '.$this->db->n($tableName).$asTa;
            $selKeys = $keys;    
            $lta = $this->db->n($tableName).'.';
            $crit = $this->makeSqlCriteria($values, $keys, $ta);
        }
        if ($extraJoins) $fromWhere .= ' '.$extraJoins;
        $fromWhere .= ' WHERE ('.$crit.')';
        if ($extraWhere) $fromWhere .= ' AND '.$extraWhere; 
            

        foreach ($keys as $key) $qKeys[] = $lta.$this->db->n($key);
        $sKeys = implode(', ', $qKeys);
        $sql = 'SELECT ';
        if ($midTableName) {
            $sql .= 'DISTINCT '.$sKeys.', '.$this->db->n($ta? $ta: $tableName).'.*'.$fromWhere;
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
        
            $rr = $this->db->getResultResource($sql);
            $fi = $this->db->resultGetFieldsInfo($rr);
            
            $prefix = $this->db->getDbPrefix();
            $tn = str_replace('#__', $prefix, $tableName);
            if ($ta) $tn = $ta;
            
            $rows = array();
            $mid = array();

            while($row = $this->db->resultFetchAssocByTables($rr, $fi))  {
                $rows[] = $row[$tn];
                $mid[] = $row[$this->midTableAlias];
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
            $this->db->resultFreeResource($rr);
        }
        if (!$midTableName || $keys2) {
            if ($keys2) {
                $tmp = $res;
                $res = array();
                $keys = $keys2;
                // we already have $rows and $objects populated
            } else {
                $rows = $this->db->fetchArray($sql);
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
                        $this->putRowToArray($row, $objects[$i], $res, $keys, $unique);        
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
    
    protected function countWithValues($values, $keys, $multipleValues, $separateCounts, $byKeys, $tableName, 
            $orderByKeys = false, $retSql = false, $midTableName = '', $otherKeys = false, 
            $keys2 = false, $values2 = false) {
        if (!$multipleValues) $values = array($values);
            elseif (!count($values)) return $separateCounts? array() : 0;
        if ($midTableName) {
            if ($keys2 && $values2) {
                
                $allKeys = $keys;
                $selKeys = array_values($allKeys[0]);
                $keys = array_values($allKeys[0]);            
                $lta = $this->midTableAlias.'.';
                $crit = $this->makeSqlCriteria($values, $selKeys, $this->midTableAlias);
                $extraJoinCrit = false;
                if ($keys2) {
                    $join = 'RIGHT';
                    $notNullC = array();
                    foreach (array_keys($allKeys[1]) as $fieldName) {
                        $notNullC[$fieldName] = $this->midTableAlias.".".$fieldName." IS NOT NULL";
                    }
                    $notNullC = "(".implode(" AND ", $notNullC).")";
                    $extraJoinCrit = $crit;
                    $crit = $notNullC." OR (".$this->makeSqlCriteria($values2, $keys2, $tableName).")";
                }
                $fromWhere = ' FROM '.$this->db->n($midTableName).' AS '.$this->midTableAlias
                    .' '.$this->getJoin($join, $this->midTableAlias, $tableName, $tableName, $allKeys[1], $extraJoinCrit);
                
            } else {
                $fromWhere = ' FROM '.$this->db->n($midTableName).' AS '.$this->midTableAlias;
                $keys = array_values($otherKeys); 
                $selKeys = $keys;
                $lta = $this->midTableAlias.'.';
                $crit = $this->makeSqlCriteria($values, $keys, $this->midTableAlias);
                if ($this->midWhere !== false) $crit = "( $crit ) AND (".$this->getStrMidWhere($this->midTableAlias).")";
            }
        } else {
            $fromWhere = ' FROM '.$this->db->n($tableName);
            $selKeys = $keys;    
            $lta = $this->db->n($tableName).'.';
            $crit = $this->makeSqlCriteria($values, $keys, '');
        }
        $fromWhere .= ' WHERE '.$crit;
        
        if (!$separateCounts) {
            $sql = 'SELECT COUNT(*) '.$fromWhere;
            if ($retSql) return $sql;
            return $this->db->fetchValue($sql);
        }
        $qKeys = array();
        foreach ($keys as $key) $qKeys[] = $lta.$this->db->n($key);
        $sKeys = implode(', ', $qKeys);
        $i = 0;
        while(in_array($cntColumn = '__count__'.$i, $keys)) $i++; 
        $sql = 'SELECT '.$sKeys.', COUNT(*) AS '.$this->db->n($cntColumn).$fromWhere.' GROUP BY '.$sKeys;
        if ($orderByKeys) $sql .= ' ORDER BY '.$sKeys; 
        if ($retSql) return $sql;
        $res = array();
        $rr = $this->db->getResultResource($sql);
        if ($byKeys && $multipleValues) {
            if (count($selKeys) === 1) {
                $key = $selKeys[0];
                while($row = $this->db->resultFetchAssoc($rr)) {
                    $res[$row[$key]] = $row[$cntColumn];        
                }
            } else {
                while($row = $this->db->resultFetchAssoc($rr)) {
                    $this->putRowToArray($row, $row[$cntColumn], $res, $selKeys, true);        
                }
            }
        } else {
            while($row = $this->db->resultFetchAssoc($rr)) 
                $res[] = $row[$cntColumn];     
        }
        $this->db->resultFreeResource($rr);
        if (!$multipleValues) $res = $res[0];
        return $res;
    }
    
    protected function deleteWithValues($values, $multipleValues, $keys, $tableName, $retSql = false, $where = false) {
        $crit = $this->makeSqlCriteria($values, $keys);
        if (!$multipleValues) $values = array($values);
            elseif (!count($values)) return 0;
        $sql =  'DELETE FROM '.$this->db->n($tableName).' WHERE ('.$crit.')';
        if (strlen($where)) $sql .= ' AND '.$where;
        if ($retSql) return $sql;
        return $this->db->query($sql);
    }

    protected function instantiateDest(array $rows) {
        if ($this->destMapper) {
            $rows = $this->destMapper->loadFromRows($rows);
        }
        return $rows;
    }
    
    protected function instantiateSrc(array $rows) {
        if ($this->srcMapper) 
            $rows = $this->srcMapper->loadFromRows($rows);
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
    protected function makeSqlCriteria($values, $keyFields, $alias = '', $default = '0') {
        if (!count($values)) return $default;
        // TODO: Optimization 1: remove duplicates from values! (how??? sort keys??? make a tree???)
        // TODO: Optimization 2: make nested criterias depending on values cardinality
        $values = Ac_Util::array_unique($values); 
        $db = $this->db;
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
    protected function getJoin ($joinType, $leftAlias, $rightTable, $rightAlias, $fieldNames, $extraCrit = false) {
        $db = $this->db;
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
        if ($extraCrit) $on[] = "($extraCrit)";
        $res .= implode(' AND ', $on);
        return $res;
    }    
        
    protected function makeCritForSrcOrDest ($data, $otherAlias, $fieldLinks, $default) {
        $keys = array_keys($fieldLinks);
        if (is_object($data)) $d = array(& $data);
        else $d = $data;
        if (is_array($data)) { // we assume that this array is of objects or rows
            $values = array();
            $this->getAllValues($data, $values, $keys);
            $crit = $this->makeSqlCriteria($values, Ac_Util::array_values($fieldLinks), $otherAlias, $default);
        } elseif (is_a($data, 'Ac_Model_Collection')) {
            trigger_error ('Collection as $srcData/$destData is not implemented yet', E_USER_ERROR);
        } else {
            trigger_error ('$srcData/$destData must be an array, a collection or an object', E_USER_ERROR);
        }
        return $crit;
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
    
    function __clone() {
        $this->immutable = false;
    }
    
}
