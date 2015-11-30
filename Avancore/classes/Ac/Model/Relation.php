<?php

define('AMR_PLAIN_RESULT', Ac_Model_Relation_Abstract::RESULT_PLAIN);
define('AMR_RECORD_KEYS', Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS);
define('AMR_ORIGINAL_KEYS', Ac_Model_Relation_Abstract::RESULT_ORIGINAL_KEYS);
define('AMR_ALL_ORIGINAL_KEYS', Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS);

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
     * @var bool This relation is outgoing from source object (belongs to source table)
     */
    protected $srcOutgoing = false;
    
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

    protected $destProvider = false;

    protected $srcProvider = false;
    
    protected $fullDestProps = false;
    
    protected $fullSrcProps = false;
    
    protected function doConstruct(array $prototype = array()) {
        
        if (isset($prototype['db']) && $prototype['db']) {
            $this->setDb($prototype['db']);
            unset ($prototype['db']);
        }
        
        parent::doConstruct($prototype);
        
        if (($this->srcTableName === false) && strlen($this->srcMapperClass)) {
            $this->setSrcMapper(Ac_Model_Mapper::getMapper($this->srcMapperClass, $this->application? $this->application : null));
        }
        if (($this->destTableName === false) && strlen($this->destMapperClass)) {
            $this->setDestMapper(Ac_Model_Mapper::getMapper($this->destMapperClass, $this->application? $this->application : null));
        }
        
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
            if ($this->srcOrdering === false) $this->srcOrdering = $this->srcMapper->getDefaultSort();
            $this->reset();
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
            if ($this->destOrdering === false) $this->destOrdering = $this->destMapper->getDefaultSort();
            $this->reset();
        }
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    function getDestMapper() {
        return $this->destMapper;
    }
    
    protected function doOnSetApplication($oldApplication) {
        $this->setDb($this->application->getDb());
        if (!$oldApplication) {
            if ($this->srcMapperClass && !$this->srcMapper) $this->setSrcMapperClass($this->srcMapperClass, true);
            if ($this->destMapperClass && !$this->destMapper) $this->setDestMapper($this->destMapper, true);
        }
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
    
    protected function doGetSrcIsUnique() {
        $res = false;
        if ($m = $this->getSrcMapper()) {
            if (!$this->fieldLinks) 
                throw new Ac_E_InvalidUsage("Cannot ".__METHOD__."() before setFieldLinks()");
            if ($m) $res = 
                !$this->midTableName && $m->identifiesRecordBy(array_keys($this->fieldLinks));
        }
        return $res;
    }
    
    protected function doGetDestIsUnique() {
        $res = false;
        if ($m = $this->getDestMapper()) {
            if (!$this->fieldLinks) 
                throw new Ac_E_InvalidUsage("Cannot ".__METHOD__."() before setFieldLinks()");
            if ($m) $res = 
                !$this->midTableName && $m->identifiesRecordBy (array_values($this->fieldLinks));
        }
        return $res;
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
            $this->reset();
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
    
    // ----------------------- Payload methods ---------------------
    
    function loadDestNNIds($srcData, $dontOverwriteLoaded = true) {
        $impl = $this->getDestImpl();
        $res = $impl->loadDestNNIds($srcData, $dontOverwriteLoaded);
        return $res;
    }
    
    function loadSrcNNIds($destData, $dontOverwriteLoaded = true) {
        $impl = $this->getSrcImpl();
        $res = $impl->loadDestNNIds($destData, $dontOverwriteLoaded);
        return $res;
    }
    
    protected function calcNNIdsImpl(array $props) {
        if ($props['midTableName']) {
            $res = array(
                'db' => $this->db,
                'srcVarName' => $props['srcNNIdsVarName'],
                'destMapperClass' => false,
                'destTableName' => $props['midTableName'],
                'destWhere' => $this->midWhere,
                'fieldLinks' => $props['fieldLinks'],
                'srcIsUnique' => $this->getSrcIsUnique(),
                'destIsUnique' => false,
            );
            // evaluate the provider
            $app = $this->application? $this->application: Ac_Application::getDefaultInstance();
            if ($app) {
                $provider = $app->getService('Ac_Model_Relation_Provider_Evaluator')->evaluateProvider($res);
                if ($provider) $res['provider'] = $provider;
            }
        } else {
            $res = false;
        }
        foreach (self::$depecatedImplProps as $p) unset($res[$p]);
        return $res;
    }
    
    protected function getFullDestProps() {
        if ($this->fullDestProps === false) {
            $this->fullDestProps = array(
                'destMapperClass' => $this->destMapperClass,
                'destTableName' => $this->destTableName,
                'midTableName' => $this->midTableName,
                'midTableAlias' => $this->midTableAlias,
                'fieldLinks' => $this->fieldLinks,
                'fieldLinks2' => $this->fieldLinks2,
                'srcVarName' => $this->srcVarName,
                'srcNNIdsVarName' => $this->srcNNIdsVarName,
                'destVarName' => $this->destVarName,
                'srcCountVarName' => $this->srcCountVarName,
                'destCountVarName' => $this->destCountVarName,
                'srcLoadedVarName' => $this->srcLoadedVarName,
                'destOrdering' => $this->destOrdering,
                'destExtraJoins' => $this->destExtraJoins,
                'destWhere' => $this->destWhere,
                'midWhere' => $this->midWhere,
                'srcQualifier' => $this->srcQualifier,
                'destQualifier' => $this->destQualifier,
                'destMapper' => $this->getDestMapper(),
                'destIsUnique' => $this->getDestIsUnique(),
                'db' => $this->db,
            );
        }
        return $this->fullDestProps;
        
    }
    
    protected function getFullSrcProps() {
        if ($this->fullSrcProps === false) {
            $this->fullSrcProps = array(
                'destMapperClass' => $this->srcMapperClass,
                'destTableName' => $this->srcTableName,
                'midTableName' => $this->midTableName,
                'midTableAlias' => $this->midTableAlias,
                'fieldLinks' => $this->midTableName? $this->fieldLinksRev2 : $this->fieldLinksRev,
                'fieldLinks2' => $this->midTableName? $this->fieldLinksRev : false,
                'srcVarName' => $this->destVarName,
                'srcNNIdsVarName' => $this->destNNIdsVarName,
                'destVarName' => $this->srcVarName,
                'srcCountVarName' => $this->destCountVarName,
                'destCountVarName' => $this->srcCountVarName,
                'srcLoadedVarName' => $this->destLoadedVarName,
                'destOrdering' => $this->srcOrdering,
                'destExtraJoins' => $this->srcExtraJoins,
                'destWhere' => $this->srcWhere,
                'midWhere' => $this->midWhere,
                'srcQualifier' => $this->destQualifier,
                'destQualifier' => $this->srcQualifier,
                'destMapper' => $this->getSrcMapper(),
                'destIsUnique' => $this->getSrcIsUnique(),
                'db' => $this->db,
            );
        }
        return $this->fullSrcProps;
    }
    
    protected static $depecatedImplProps = array(
        'midTableName', 'midTableAlias', 'destTableName', 'destMapperClass', 
        'destMapper', 'destExtraJoins', 'db', 'destOrdering', 'destExtraJoins', 
        'destWhere', 'midWhere', 'srcQualifier', 'destQualifier',
    );
    
    protected function getDestImplPrototype() {
        $fullDestProps = $this->getFullDestProps();
        $res = array_merge(parent::getDestImplPrototype(), array(
            'class' => 'Ac_Model_Relation_Impl',
            'provider' => $this->getDestProvider(),
            'destNNIdsImpl' => $this->calcNNIdsImpl($fullDestProps),
        ), $fullDestProps);
        foreach (self::$depecatedImplProps as $p) unset($res[$p]);
        return $res;
    }
    
    /**
     * @return Ac_Model_Relation_Impl
     */
    protected function getSrcImplPrototype() {
        $fullSrcProps = $this->getFullSrcProps();
        $res = array_merge($proto = parent::getSrcImplPrototype(), array(
            'class' => 'Ac_Model_Relation_Impl',
            'provider' => $this->getSrcProvider(),
            'destNNIdsImpl' => $this->calcNNIdsImpl($fullSrcProps),
        ), $fullSrcProps);
        foreach (self::$depecatedImplProps as $p) unset($res[$p]);
        return $res;
    }

    function getDestProvider($asIs = false) {
        if (!$asIs && $this->destProvider === false) {
            $app = $this->application? $this->application: Ac_Application::getDefaultInstance();
            if ($app) $this->destProvider = $app->getService('Ac_Model_Relation_Provider_Evaluator')->evaluateProvider($this->getFullDestProps());
        }
        return $this->destProvider;
    }
    
    function setDestProvider($destProvider) {
        $this->destProvider = $destProvider;
    }

    function setSrcProvider($srcProvider) {
        $this->srcProvider = $srcProvider;
    }

    /**
     * @param bool $asIs Dont create instance
     * @return Ac_Model_Relation_Provider (when $asIs === true, result is mixed)
     */
    function getSrcProvider($asIs = false) {
        if (!$asIs && $this->srcProvider === false) {
            $app = $this->application? $this->application: Ac_Application::getDefaultInstance();
            if ($app) $this->srcProvider = $app->getService('Ac_Model_Relation_Provider_Evaluator')->evaluateProvider($this->getFullSrcProps());
        }
        return $this->srcProvider;
    }    

    function __clone() {
        parent::__clone();
        if ($this->srcProvider) {
            if (is_object($this->srcProvider)) $this->srcProvider = clone $this->srcProvider;
            if ($this->srcImpl) $this->srcImpl->setProvider($this->srcProvider);
        }
        if ($this->destProvider) {
            if (is_object($this->destProvider)) $this->destProvider = clone $this->destProvider;
            if ($this->destImpl) $this->destImpl->setProvider($this->destProvider);
        }
    }
    
    protected function reset() {
        parent::reset();
        $this->srcProvider = false;
        $this->destProvider = false;
        $this->fullSrcProps = false;
        $this->fullDestProps = false;
    }
    
}