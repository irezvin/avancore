<?php

class Ac_Etl_Db extends Ac_Sql_Db {
    
    /**
     * @var Ac_Sql_Db
     */
    protected $db = false;
    
    /**
     * @var Ac_Etl_I_Logger
     */
    protected $logger = false;
    
    protected $nextTags = array();

    /**
     * One or more tags for next query (property will be erased after query execution)
     * String values can have several tags separated by spaces, semicolons or commas
     * 
     * @param string|array $nextTags 
     */
    function setNextTags($nextTags) {
        if (!is_array($nextTags)) $nextTags = preg_split("/[ ;,]+/", $nextTags);
        $this->nextTags = $nextTags;
    }
    
    function getNextTags() {
        return $this->nextTags;
    }
    
    function setLogger(Ac_Etl_I_Logger $logger) {
        $this->logger = $logger;
    }

    /**
     * @return Ac_Etl_I_Logger
     */
    function getLogger() {
        return $this->logger;
    }

    function setDb(Ac_Sql_Db $db) {
        $this->db = $db;
    }

    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        return $this->db;
    }
    
    function _implValueQuote($value) {
        return $this->db->_implValueQuote($value);
    }
    
    function _implNameQuote($name) {
        return $this->db->_implNameQuote($name);
    }
    
    function _implNameUnquote($name) {
        return $this->db->_implNameUnquote($name);
    }
    
    function _implIsNameQuoted($name) {
        return $this->db->_implIsNameQuoted($name);
    }
    
    function _implConcatNames($quotedNames) {
        return $this->db->_implConcatNames($quotedNames);
    }
    
    function fetchArray($query, $keyColumn = false, $withNumericKeys = false) {
        $this->begin($query, $t);
        $res = $this->db->fetchArray($query, $keyColumn, $withNumericKeys);
        $this->end($query, $t);
        return $res;
    }
    
    function fetchObjects($query, $keyColumn = false) {
        $this->begin($query, $t);
        $res = $this->db->fetchObjects($query, $keyColumn);
        $this->end($query, $t);
        return $res;
    }
    
    function fetchRow($query, $key = false, $keyColumn = false, $withNumericKeys = false, $default = false) {
        $this->begin($query, $t);
        $res = $this->db->fetchRow($query, $key, $keyColumn, $withNumericKeys, $default);
        $this->end($query, $t);
        return $res;
    }
    
    function fetchColumn($query, $colNo = 0, $keyColumn = false) {
        $this->begin($query, $t);
        $res = $this->db->fetchColumn($query, $colNo, $keyColumn);
        $this->end($query, $t);
        return $res;
    }
    
    function fetchValue($query, $colNo = 0, $default = null) {
        $this->begin($query, $t);
        $res = $this->db->fetchValue($query, $colNo, $default);
        if (strlen($res) < 100) $logResult = $res;
            else $logResult = false;
        $this->end($query, $t, $logResult);
        return $res;
    }
    
    protected function extractTags(& $query, $useNextTags = false) {
        if (preg_match ("/^--\s*tags:\s*(.*)$/m", $query, $matches)) {
            $tags = preg_split('/\s+/', trim($matches[1]));
            $query = preg_replace('/'.preg_quote($matches[0], '/').'[\n\r]*/', '', $query);
        } else {
            $tags = array();
        }
        if ($useNextTags) {
            $tags = array_unique(array_merge($this->nextTags, $tags));
            $this->nextTags = array();
        }
        if (!count($tags)) $tags = array('untagged');
        $tags[] = 'queries';
        return $tags;
    }
    
    protected $began = 0;
    
    protected function begin($query, & $t) {
        if (!$this->began) {
            $t = microtime(true);
        }
        $this->began++;
    }
    
    protected function end($query, $t, $result = false) {
        $this->began--;
        if (!$this->began) {
            $t1 = microtime(true);
            if ($this->logger) {
                $tags = $this->extractTags($query, true);
                $info = false; // TODO
                //$info = mysqli_info();
                if ($result !== false) {
                    $query .= "\n-- Result value: ".$result;
                }
                if ($info !== false) {
                    $query .= "\n-- ".$info;
                }
                $logItem = new Ac_Etl_Log_Query(''.$query, 'profile', $tags, array(
                    'spentTime' => $t1 - $t
                ));
                $this->logger->acceptItem($logItem);
            }
        }
    }
    
    function query($query) {
        $this->begin($query, $t);
        $res = $this->db->query($query);
        $this->end($query, $t);
        return $res;
    }
    
    function getErrorCode() {
        return $this->db->getErrorCode();
    }
    
    function getErrorDescr() {
        return $this->db->getErrorDescr();
    }

    function getLastInsertId() {
        return $this->db->getLastInsertId();
    }
    
    function getAffectedRows() {
        return $this->db->getAffectedRows();
    }
    
    function getDbName() {
        return $this->db->getDbName();
    }

    protected function implConcatNames($quotedNames) {
        return $this->db->implConcatNames($quotedNames);
    }

    protected function implIsNameQuoted($name) {
        return $this->db->implIsNameQuoted($name);
    }

    protected function implNameQuote($name) {
        return $this->db->implNameQuote($name);
    }

    protected function implNameUnquote($name) {
        return $this->db->implNameUnquote($name);
    }

    protected function implValueQuote($value) {
        return $this->db->implValueQuote($value);
    }

    public function getDbPrefix() {
        return $this->db->getDbPrefix();
    }

    public function getDialect() {
        return $this->db->getDialect();
    }

    public function getIfnullFunction() {
        return $this->db->getIfnullFunction();
    }

    public function getResultResource($query) {
        return $this->db->getResultResource($query);
    }

    public function resultFetchAssoc($resultResource) {
        return $this->db->resultFetchAssoc($resultResource);
    }

    public function resultFetchAssocByTables($resultResource, array &$fieldsInfo = array()) {
        return $this->db->resultFetchAssocByTables($resultResource, $fieldsInfo);
    }

    public function resultFreeResource($resultResource) {
        return $this->db->resultFreeResource($resultResource);
    }

    public function resultGetFieldsInfo($resultResource) {
        return $this->db->resultGetFieldsInfo($resultResource);
    }
        
    /**
     * @param mixed $args
     * @return Ac_Sql_Db
     */
    function args($args = array()) {
        $f = __FUNCTION__;
        $args = func_get_args();
        return call_user_func_array(array($this->db, $f), $args);
    }
    
    /**
     * @return Ac_Sql_Db
     */
    function argsArray(array $args) {
        $f = __FUNCTION__;
        $args = func_get_args();
        return call_user_func_array(array($this->db, $f), $args);
    }
    
    function dumpNext($options = self::DUMP_OB_STOP) {
        $f = __FUNCTION__;
        $args = func_get_args();
        return call_user_func_array(array($this->db, $f), $args);
    }

    

}
