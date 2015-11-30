<?php

abstract class Ac_Model_Relation_Abstract extends Ac_Prototyped {
    
    /**
     * result rows will be returned as plain numeric array
     * @see Ac_Model_Relation::getDest()
     * @see Ac_Model_Relation::getSrc()
     */
    const RESULT_PLAIN = 0;
    
    /**
     * result rows will have DB keys of source records 
     * @see Ac_Model_Relation::getDest()
     * @see Ac_Model_Relation::getSrc()
     */
    const RESULT_RECORD_KEYS = 1;
    
    /**
     * result rows will have keys of original array 
     * @see Ac_Model_Relation::getDest()
     * @see Ac_Model_Relation::getSrc()
     */
    const RESULT_ORIGINAL_KEYS = 2;
    
    /**
     * results rows will have keys of original array and all keys from original array will be in result array.
     * Values in places of missing result rows will have default value.
     *  
     * @see Ac_Model_Relation::getDest()
     * @see Ac_Model_Relation::getSrc()
     */
    const RESULT_ALL_ORIGINAL_KEYS = 3;
    
    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    /**
     * Describes cardinality of source table (true if source fields point to unique record)
     * @var bool 
     */
    protected $srcIsUnique = null;
    
    /**
     * Describes cardinality of destination table (true if destination fields point to unique record)
     */    
    protected $debug = false;
    
    protected $immutable = false;

    protected $destIsUnique = null;
    
    protected $srcImpl = false;
    
    protected $destImpl = false;
    
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
        
        $this->doConstruct($prototype);
        
        if ($imm) $this->immutable = true;            
        
    }
    
    protected function doConstruct(array $prototype = array()) {
        
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
        
    }
    
    function hasPublicVars() {
        return false;
    }
    
    function setDebug($debug) {
        $this->debug = (bool) $debug;
    }

    function getDebug() {
        return $this->debug;
    }

    function setApplication(Ac_Application $application) {
        if ($application !== ($oldApplication = $this->application)) {
            if ($this->immutable) throw self::immutableException();
            $this->application = $application;
            $this->doOnSetApplication($oldApplication);
            $this->reset();
        }
    }
    
    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }
    
    protected function doOnSetApplication($oldApplication) {
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
            $this->reset();
        }
    }

    /**
     * Returns whether source records are uniquely identified by their foreign keys
     * 
     * @return bool
     */
    function getSrcIsUnique() {
        if ($this->srcIsUnique === null) {
            $this->srcIsUnique = $this->doGetSrcIsUnique();
        }
        return $this->srcIsUnique;
    }
    
    abstract protected function doGetSrcIsUnique();
    
    abstract protected function doGetDestIsUnique();

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
            $this->reset();
        }
    }

    /**
     * Returns whether destination records are uniquely identified by their foreign keys
     * @return bool
     */
    function getDestIsUnique() {
        if ($this->destIsUnique === null) {
            $this->destIsUnique = $this->doGetDestIsUnique();
        }
        return $this->destIsUnique;
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

    /**
     * Returns one or more destination objects for given source object
     * @param Ac_Model_Data|object $srcData
     * @param int $matchMode How keys of result array are composed (can be Ac_Model_Relation_Abstract::RESULT_PLAIN, Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS, Ac_Model_Relation_Abstract::RESULT_ORIGINAL_KEYS, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS) -- only if srcData is array
     * @param mixed $defaultValue Value to be returned for missing rows when $matchMode is Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS or when $srcData is an object. By default it is null if $this->destIsUnique and empty array() if not.
     * @return Ac_Model_Data|array
     */
    function getDest ($srcData, $matchMode = Ac_Model_Relation_Abstract::RESULT_PLAIN, $defaultValue = null) {
        $impl = $this->getDestImpl();
        if (func_num_args() >= 3) $res = $impl->getDest($srcData, $matchMode, $defaultValue);
            else $res = $impl->getDest($srcData, $matchMode);
        return $res;
    }
    
    /**
     * Returns one or more source objects for given destination object
     * @param Ac_Model_Data|object $destData
     * @param int $matchMode How keys of result array are composed (can be Ac_Model_Relation_Abstract::RESULT_PLAIN, Ac_Model_Relation_Abstract::RESULT_RECORD_KEYS, Ac_Model_Relation_Abstract::RESULT_ORIGINAL_KEYS, Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS) -- only if srcData is array
     * @param mixed $defaultValue Value to be returned for missing rows when $matchMode is Ac_Model_Relation_Abstract::RESULT_ALL_ORIGINAL_KEYS or when $srcData is an object. By default it is null if $this->destIsUnique and empty array() if not.
     * @return Ac_Model_Data|array
     */ 
    function getSrc ($destData, $matchMode = Ac_Model_Relation_Abstract::RESULT_PLAIN, $defaultValue = null) {
        $impl = $this->getSrcImpl();
        if (func_num_args() >= 3) $res = $impl->getDest($srcData, $matchMode, $defaultValue);
            else $res = $impl->getDest($destData, $matchMode);
        return $res;
    }
    
    function countDest ($srcData, $separate = true, $matchMode = Ac_Model_Relation_Abstract::RESULT_PLAIN) {
        $impl = $this->getDestImpl();
        $res = $impl->countDest($srcData, $separate, $matchMode);
        return $res;
    }
    
    function countSrc ($destData, $separate = true, $matchMode = Ac_Model_Relation_Abstract::RESULT_PLAIN) {
        $impl = $this->getSrcImpl();
        $res = $impl->countDest($destData, $separate, $matchMode);
        return $res;
    }
    
    function loadDest (& $srcData, $dontOverwriteLoaded = true, $biDirectional = true, $returnAll = true) {
        $impl = $this->getDestImpl();
        $res = $impl->loadDest($srcData, $dontOverwriteLoaded, $biDirectional, $returnAll);
        return $res;
    }
    
    function loadSrc (& $destData, $dontOverwriteLoaded = true, $biDirectional = true, $returnAll = true) {
        $impl = $this->getSrcImpl();
        $res = $impl->loadDest($destData, $dontOverwriteLoaded, $biDirectional, $returnAll);
        return $res;
    }
    
    /**
     * Counts destination objects and stores result in $srcCountVarName of each corresponding $srcData object
     */
    function loadDestCount ($srcData, $dontOverwriteLoaded = true) {
        $impl = $this->getDestImpl();
        $res = $impl->loadDestCount($srcData, $dontOverwriteLoaded);
        return $res;
    }
    
    /**
     * Counts source objects and stores result in $destCountVarName of each corresponding $destData object
     */
    function loadSrcCount ($destData, $dontOverwriteLoaded = true) {
        $impl = $this->getSrcImpl();
        $res = $impl->loadDestCount($srcData, $dontOverwriteLoaded);
        return $res;
    }
    
    function __clone() {
        $this->immutable = false;
        if ($this->srcImpl) $this->srcImpl = clone $this->srcImpl;
        if ($this->destImpl) $this->destImpl = clone $this->destImpl;
    }

    function __get($var) {
        if (method_exists($this, $m = 'get'.$var)) return $this->$m();
        else Ac_E_InvalidCall::noSuchProperty ($this, $var);
    }

    function __set($var, $value) {
        if (method_exists($this, $m = 'set'.$var)) $this->$m($value);
        else throw Ac_E_InvalidCall::noSuchProperty ($this, $var);
    }
    
    protected static function immutableException() {
        return new Ac_E_InvalidUsage("Cannot modify ".__CLASS__." with \$immutable == true");
    }    
    
    protected function getSrcImpl() {
        if ($this->srcImpl === false) {
            $this->srcImpl = Ac_Prototyped::factory($this->getSrcImplPrototype());
        }
        return $this->srcImpl;
    }
    
    protected function getDestImpl() {
        if ($this->destImpl === false) {
            $this->destImpl = Ac_Prototyped::factory($this->getDestImplPrototype());
        }
        return $this->destImpl;
    }
    
    protected function getSrcImplPrototype() {
        $res = array(
            'application' => $this->application,
            'srcIsUnique' => $this->getDestIsUnique(),
            'destIsUnique' => $this->getSrcIsUnique(),
        );
        return $res;
    }
    
    protected function getDestImplPrototype() {
        $res = array(
            'application' => $this->application,
            'srcIsUnique' => $this->getSrcIsUnique(),
            'destIsUnique' => $this->getDestIsUnique(),
        );
        return $res;
    }
    
    protected function reset() {
        $this->srcImpl = false;
        $this->destImpl = false;
    }
    
}