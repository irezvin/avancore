<?php

class Ac_Legacy_Database_Joomla extends Ac_Legacy_Database {

    var $dialectClass = 'Ac_Sql_Dialect_Mysql';

    protected $inspectorClass = 'Ac_Sql_Dbi_Inspector_MySql5';    
    
    /**
     * @var database
     */
    var $_db = false;
    
    function _doGetAccess() {
        return array(
            'user' => $GLOBALS['mosConfig_user'], 
            'password' => $GLOBALS['mosConfig_password'], 
            'host' => $GLOBALS['mosConfig_host'], 
            'db' => $GLOBALS['mosConfig_db'], 
            'prefix' => $GLOBALS['mosConfig_dbprefix'],
        );
    }
    
    function _doInitialize(array $options = array()) {
        if (!defined('_VALID_MOS') || !isset($GLOBALS['database']) || !is_a($GLOBALS['database'], 'database')) 
            trigger_error ('No Joomla or Joomla Db detected', E_USER_ERROR);
            
        $this->_db = $GLOBALS['database'];     
    }
    
    function Quote($string) {
        if (is_null($string)) return 'NULL';
        return $this->_db->Quote($string);
    }
    
    function NameQuote($string) {
        if (is_object($string) && $string instanceof Ac_I_Sql_Expression) return $string->nameQuote($this);
            else return $this->_db->quoteName($string);
    }
    
    function setQuery($query, $offset = 0, $limit = 0, $prefix = '#__') {
//        if (strpos($query, "''") !== false) {
//            if (!isset($GLOBALS['foo'])) {
//                $GLOBALS['foo'] = 0;
//            } else {
//                if ($GLOBALS['foo']++ > 10) trigger_error ($query, E_USER_ERROR);
//                    else trigger_error ($query, E_USER_NOTICE);
//            }
//            
//        }
        return $this->_db->setQuery($query, $offset, $limit, $prefix);
    }
    
    function loadResult() {
        $this->_debugBeforeQuery($this->_db->_sql);
        $res = $this->_db->loadResult();
        $this->_debugAfterQuery($this->_db->_sql, count($res));
        return $res;
    }
    
    function loadResultArray($numInArray = 0) {
        $this->_debugBeforeQuery($this->_db->_sql);
        $res = $this->_db->loadResultArray($numInArray);
        $this->_debugAfterQuery($this->_db->_sql, count($res));
        return $res;
    }
    
    function loadAssocList($id = '') {
        $this->_debugBeforeQuery($this->_db->_sql);
        $res = $this->_db->loadAssocList($id);
        $this->_debugAfterQuery($this->_db->_sql, count($res));
        return $res;
    }
    
    function loadObjectList($id = '') {
        $this->_debugBeforeQuery($this->_db->_sql);
        $res = $this->_db->loadObjectList($id);
        $this->_debugAfterQuery($this->_db->_sql, count($res));
        return $res;
    }
    
    function query($query = false) {
        if ($query !== false) $this->_db->setQuery($query);
        $this->_debugBeforeQuery($this->_db->_sql);
        $res = $this->_db->query();
        $this->_debugAfterQuery($this->_db->_sql, false);
        return $res;
    }
    
    function getLastInsertId() {
        return $this->_db->insertid();
    }
    
    function getResultResource($unbuffered = false) {
        $this->_debugBeforeQuery($this->_db->_sql);
        $res = $this->_db->query();
        $this->_debugAfterQuery($this->_db->_sql, false, true);
        return $res;
    }
    
    function fetchAssoc($resultResource) {
        return mysql_fetch_assoc($resultResource);
    }
    
    function fetchObject($resultResource, $className = null) {
        return $className? mysql_fetch_object($resultResource, $className) : mysql_fetch_object($resultResource);
    }
    
    function freeResultResource($resultResource) {
        if (is_resource($resultResource)) return mysql_free_result($resultResource);
            else return false;
    }
    
    function _pushQuery() {
        $this->_qBuf[] = array($this->_db->_sql, $this->_db->_errorMsg, $this->_db->_errorNum);
    }
    
    function _popQuery() {
        $c = count($this->_qBuf);
        list($this->_db->_sql, $this->_db->_errorMsg, $this->_db->_errorNum) = $this->_qBuf[$c];
        unset($this->_qBuf[$c]);
    }
    
    function getErrorMsg() {
        return $this->_db->getErrorMsg();
    }
    
    function getAffectedRows() {
        return $this->_db->getAffectedRows();
    }
    
    function getPrefix() {
        $a = $this->getAccess();
        return $a['prefix'];
    }
    
}

?>