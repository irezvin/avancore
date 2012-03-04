<?php

define('CALLBACK_AE_DATABASE_AFTER_QUERY', 'Ae_Legacy_Database.afterQuery');
Ae_Dispatcher::loadClass('Ae_Callbacks');

class Ae_Legacy_Database {

    protected static $defaultInstance = null;
    
    /**
     * @var Ae_Sql_Dbi_Inspector
     */
    protected $inspector = false;
    
    protected $inspectorClass = false;
    
    static function getDefaultInstance() {
        if (!self::$defaultInstance) self::$defaultInstance = Ae_Application::getDefaultInstance()->getLegacyDatabase();
        return self::$defaultInstance;
    }
    
    
    static function setDefaultInstance(Ae_Legacy_Database $defaultInstance) {
        self::$defaultInstance = $defaultInstance;
    }
    
    /**
     * @var Ae_Sql_Db
     */
    var $_sqlDb = false;
    
    var $_transactionLevel = 0;
    
    var $_tmpDir = false;
    
    var $_useShowTableStatus = false;
    
    var $_qBuf = array();
    
    var $_access = false;
    
    /**
     * @var Ae_Legacy_Config
     */
    var $_config = false;
    
    var $debug = true;
    
    var $_t = array();
    
    var $initOptions = array();

    function __construct($options = array()) {
        if (get_class($this) == 'Ae_Legacy_Database') trigger_error ("Attempt to instantiate abstract class Ae_Legacy_Database", E_USER_ERROR);
        if (isset($options['config']) && is_a($options['config'], 'Ae_Legacy_Config')) {
            $this->_config = & $options['config']; 
        } else {
            $disp = & Ae_Dispatcher::getInstance();
            $this->_config = & $disp->config;
        }
        if (isset($options['tmpDir'])) {
            $this->_tmpDir = $options['tmpDir'];
        } else {
            if ($this->_config) $this->_tmpDir = $this->_config->cachePath;            
        }
        if (isset($options['debug'])) $this->debug = $options['debug'];
        $this->initOptions = $options;
        $this->_doInitialize($options); 
    }
    
    /**
     * @access protected
     */
    function _doGetAccess() {
        return array('user' => false, 'password' => false, 'host' => false, 'db' => false, 'prefix' => false);
    }
    
    function getAccess($paramName = false, $default = false) {
        if ($this->_access === false) $this->_access = $this->_doGetAccess();
        $res = $this->_access;
        if (strlen($paramName)) $res = (isset($res[$paramName]))? $res[$paramName] : $default;
        return $res; 
    }
    
    function getMysqlArgs($withDbName = false) {
        extract($this->getAccess());
        $res = array();
        if (strlen($user)) $res['user'] = $user;
        if (strlen($password)) $res['password'] = $password;
        if (strlen($host)) {
            if (count($hostPort = explode(':', $host)) == 2) {
                list($host, $port) = $hostPort;
                $res['port'] = $port;
            }
            else $port = '';
            $res['host'] = $host;
        }
        $cmd = '';
        foreach ($res as $param => $val) $cmd .= '--'.$param.'='.escapeshellarg($val).' ';
        if ($withDbName) {
            if ($withDbName === true) $withDbName = strlen($db)? $db : false;
        }
        if (strlen($withDbName)) $cmd .= escapeshellarg($withDbName);
        return $cmd; 
    }
    
    /**
     * Template method that is called from the constructor
     * @access protected
     */
    function _doInitialize($options = array()) {
        
    }
    
    function Quote($string) {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function NameQuote($string) {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function setQuery($query, $offset = 0, $limit = 0, $prefix = '#__') {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function loadResult() {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function loadResultArray($numInArray = 0) {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function loadAssocList($id = '') {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function _gotoNextResultset($resource) {
        return false;
    }
    
    function loadAllResultsets($withNumericKeys = false) {
	$rs = $this->getResultResource();
        $res = array();
        do {
            $currRs = array();
            while (($row = $withNumericKeys? $this->fetchBoth($rs) : $this->fetchAssoc($rs))) {
		if (is_array($row) && !count($row)) continue;
                $currRs[] = $row;
            }
            if (count($currRs)) $res[] = $currRs;
            $r = $this->_gotoNextResultset($rs);
        } while ($r);
        $this->freeResultResource($rs);
        return $res; 
    }
    
    function loadObjectList($id = '') {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function loadObject (& $row) {
        $al = $this->loadAssocList();
        if (count($al)) {
            if (!is_object($row)) $row = new stdClass();
            Ae_Util::simpleBindAll($al[0], $row);
        } else $row = null;
        return !is_null($row);
    }
    
    function query($query = false) {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }

    function getLastInsertId() {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function getErrorMsg() {
        return "*** ERROR MSG NOT IMPLEMENTED YET ***";
    }
    
    function getResultResource($unbuffered = false) {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function fetchAssoc($resultResource) {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function fetchObject($resultResource, $className = null) {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function fetchBoth($resultResource) {
        $res = $this->fetchAssoc($resultResource);
        if (is_array($res)) $res = array_merge($res, array_values($res));
        return $res;
    }
    
    function getFieldsInfo($resultResource, $noTableKey = 0) {
        $n = mysql_num_fields($resultResource);
        for ($i = 0; $i < $n; $i++) {
            $tblName = mysql_field_table($resultResource, $i);
            if (!strlen($tblName)) $tblName = $noTableKey;
            $res[] = array($tblName, mysql_field_name($resultResource, $i));
        }
        return $res;
    }
    
    function fetchAssocByTables($resultResource, $fieldsInfo = false) {
        $row = mysql_fetch_array($resultResource, MYSQL_NUM);
        if ($row === false) return $row;
        foreach ($fieldsInfo as $i => $fi) {
            $res[$fi[0]][$fi[1]] = $row[$i];
        }
        return $res;
    }
    
    function sqlEqCriteria($ids) {
        if (!is_array($ids)) {
            $res = " = ".$this->Quote($ids);
        } else {
            foreach ($ids as $i => $id) $ids[$i] = $this->Quote($id);
            $res = " IN (".implode(", ", $ids).")";
        }
        return $res;
    }
    
    function sqlKeysCriteria($values, $keyFields, $alias = '', $default = '0') {
        if (!count($values)) return $default;
        // TODO: Optimization 1: remove duplicates from values! (how??? sort keys??? make a tree???)
        // TODO: Optimization 2: make nested criterias depending on values cardinality
        $values = Ae_Util::array_unique($values); 
        $db = & $this;
        $qAlias = strlen($alias)? $alias.'.' : $alias;
        if (is_array($keyFields)) {
            if (count($keyFields) === 1) {
                $qValues = array();
                $qKeyField = $db->NameQuote($keyFields[0]);
                foreach ($values as $val) $qValues[] = $db->Quote($val[0]);
                $qValues = array_unique($qValues);
                if (count($qValues) === 1) $res = $qAlias.$qKeyField.' = '.$qValues[0];
                    else $res = $qAlias.$qKeyField.' IN('.implode(",", $qValues).')';
            } else {
                $cKeyFields = count($keyFields);
                $bKeyFields = $cKeyFields - 1;
                $qKeyFields = array();
                foreach ($keyFields as $keyField) $qKeyFields[] = $qAlias.$db->NameQuote($keyField);
                $crit = array();
                foreach ($values as $valArray) {
                    $c = '';
                    for ($i = 0; $i < $bKeyFields; $i++) {
                         $c .= $qKeyFields[$i].'='.$db->Quote($valArray[$i]).' AND ';
                    }
                    $crit[] = $c.$qKeyFields[$bKeyFields].' = '.$db->Quote($valArray[$bKeyFields]);
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
        
    function freeResultResource($resultResource) {
        trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
    function supportsUnbufferedQueries() {
        return false;
    }
    
    function supportsTransactions() {
        return true;
    }
    
    function startTransaction() {
        $this->setQuery('START TRANSACTION');
        $this->_transactionLevel++;
        return $this->query();
    }
    
    function commit() {
        $this->setQuery('COMMIT');
        if ($this->_transactionLevel > 0) $this->_transactionLevel--;
        return $this->query();
    }
    
    function rollback() {
        $this->setQuery('ROLLBACK');
        if ($this->_transactionLevel > 0) $this->_transactionLevel--;
        return $this->query();
    }
    
    function getTransactionLevel() {
        return $this->_transactionLevel;
    }
    
    function touch($tableName) {
        if (!is_array($tableName)) $tableName = array($tableName);
        foreach($tableName as $tn) touch($this->_tmpDir.'/'.$tn);
    }
    
    
    function getTime($tableName, $default = false, $touchIfNotExists = false) {
        if (is_array($tableName)) {
            $r = array();
            foreach ($tableName as $tn) $r[] = $this->getTime($tn);
            return max($r);
        } else {
            if ($this->_useShowTableStatus) {
                $res = $this->_getTimeFromTableStatus($tableName);
                if ($res === false) {
                    $res = time();
                    if ($touchIfNotExists) $this->touch($tableName);
                }
            } else {
                if (!is_file($fname = $this->_tmpDir.'/'.$tableName)) {
                    $res = $default === false? time() : $default;
                    $this->touch($tableName);
                }
                else {
                    clearstatcache();
                    $res = filemtime($fname);
                }
            }
            return $res;
        }
    }
    
    function _pushQuery() {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    function _popQuery() {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    function _getTimeFromTableStatus($tableName) {
        $this->_pushQuery();
        $this->setQuery("SHOW TABLE STATUS LIKE ".$this->Quote($tableName));
        $r = $this->loadAssocList();
        if (!count($r)) $res = false;
            else $res = strtotime($r[0]['Update_time']);
        var_dump("*** $res ***");
        $this->_popQuery();
        return $res;
    }
    
    function replacePrefix($statement) {
        return str_replace('#__', $this->getAccess('prefix', ''), $statement);
    }
    
    function getLimitsClause($count, $offset = false, $withLimitKeyword = true) {
        if ($withLimitKeyword === false) $l = ''; else $l = ' LIMIT';
        if ($offset === false) $res = " {$l} ".intval($count);
            else $res = " {$l} ".intval($offset).", ".intval($count);
        return $res;
    }
    
    function getConcatExpression($expressions) {
        return 'CONCAT('.implode(', ', $expressions).')';
    }
    
    /**
     * @return Ae_Sql_Db
     */
    function getSqlDb() {
        if ($this->_sqlDb === false) {
            Ae_Dispatcher::loadClass('Ae_Sql_Db_Ae');
            $this->_sqlDb = new Ae_Sql_Db_Ae($this);
        }
        return $this->_sqlDb;
    }
    
    function isNameQuoted($name) {
        return strlen($name) > 2 && $name{0} == '`';
    }
    
    /**
     * @return Ae_Legacy_Database
     */
    function & cloneObject() {
    	$res = unserialize(serialize($this));
    	return $res;
    }
    
    function canCopyToDest(& $db) {
    	trigger_error ("Call to abstract method", E_USER_ERROR);
    }
    
	function copyToDest(& $db) {
		if (!$this->canCopyToDest($db)) trigger_error ("Cannot copy to destination database; use canCopyToDest() to check this next time", E_USER_ERROR);
		return $this->_doCopyToDest($db);
	}
	
	function _doCopyToDest(& $db) {
		trigger_error ("Call to abstract method", E_USER_ERROR);
	}
	
	function getIfnullFunction() {
	    return 'IFNULL';
	}
	
	function applyLimits($statement, $count, $offset, $orderBy = false) {
	    return $statement.' '.$this->getLimitsClause($count, $offset);
	}
	
	function ifStatement($if, $then, $else, $close = true) {
	    return "IF($if,$then,$else".($close? ")" : "");
	}
	
	function ifClose() {
		return ")";
	}
	
	function hasToConvertDatesOnLoad() {
	    return false;
	}
	
	function hasToConvertDatesOnStore() {
	    return false;
	}
	
	function getDateStoreFormats() {
	    return array('date' => 'Y-m-d', 'time' => 'H:i:s', 'dateTime' => 'Y-m-d H:i:s');
	}
	
	function getZeroDates() {
	    return array('date' => '0000-00-00', 'time' => '00:00', 'dateTime' => '0000-00-00 00:00:00');
	}
	
	function convertDates($row, $columnFormats) {
	    foreach ($columnFormats as $column => $format) {
	        if (isset($row[$column])) $row[$column] = Ae_Util::date($row[$column], $format); 
	    } 
	    return $row;
	}
	
	function convertDateForStore($date, $type) {
	    $dsf = $this->getDateStoreFormats();
	    $zd = $this->getZeroDates();
	    if (isset($dsf[$type])) {
	        if (is_null($date) || $date === false) $res = null;
	        else {
	            $ts = Ae_Util::date($date);
	            if ($ts === 0 && isset($zd[$type])) $res = $zd[$type];
	                else $res = Ae_Util::date($date, $dsf[$type]);
	        }
	    }
	    else {
	        trigger_error("\$type should be of of ".implode('|', array_keys($dsf)), E_USER_ERROR);
	        $res = false;
	    }
	    return $res;
	}
	
	function _time() {
	    if (function_exists('xdebug_time_index')) return xdebug_time_index();
	       else return microtime();
	}
	
	function _debugBeforeQuery($sql) {
	    if ($this->debug) {
	       $this->_t[md5($sql)] = $this->_time();
	    }
	}
	
	function _debugAfterQuery($sql, $numRows = false, $isHandleReturned = false) {
	    if ($this->debug) {
    	    $t = $this->_time();
    	    $md = md5($sql);
    	    if (isset($this->_t[$md])) {
    	        $time = $t - $this->_t[$md];
    	        unset($this->_t[$md]);
    	    }
    	    else $time = false;
    	    Ae_Callbacks::call(CALLBACK_AE_DATABASE_AFTER_QUERY, $this, $sql, $time, $numRows, $isHandleReturned);
	    }
	}
    
    /**
     * @return Ae_Sql_Dbi_Inspector
     */
    function getInspector() {
        if ($this->inspector === false) {
            $c = $this->inspectorClass;
            if (!strlen($c)) throw new Exception("\$inspectorClass not provided in ".get_class($this));
            $access = $this->_doGetAccess();
            $db = isset($access['db'])? $access['db'] : null;
            $this->inspector = new $c(new Ae_Sql_Db_Ae($this), $db);
        }
        return $this->inspector;
    }
	
}

?>