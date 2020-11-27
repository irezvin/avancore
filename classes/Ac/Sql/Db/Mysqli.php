<?php

class Ac_Sql_Db_Mysqli extends Ac_Sql_Db {
    
    /**
     * @var mysqli
     */
    protected $link = null;
    
    /**
     * @var Ac_Sql_Dialect
     */
    protected $dialect = false;
    
    protected $dbPrefix = false;
    
    protected $dbName = false;
    
    protected $returnsLastInsertId = false;
    
    protected $affectedRows = false;
    
    protected $host = null;

    protected $username = null;

    protected $password = null;

    protected $port = null;

    protected $socket = null;

    protected $flags = null;

    function setHost($host) {
        $this->host = $host;
    }

    function getHost() {
        return $this->host;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function getUsername() {
        return $this->username;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function getPassword() {
        return $this->password;
    }

    function setDbName($dbName) {
        if ($this->link && $this->getDialect()) {
            $this->dbName = $dbName;
            $this->query("USE ".$this->n($dbName));
        } else {
            $this->dbName = $dbName;
        }
    }

    function getDbName() {
        if ($this->dbName) {
            return $this->dbName;
        } else {
            return $this->fetchValue("SELECT DATABASE()");
        }
    }

    function setPort($port) {
        $this->port = $port;
    }

    function getPort() {
        return $this->port;
    }

    function setSocket($socket) {
        $this->socket = $socket;
    }

    function getSocket() {
        return $this->socket;
    }

    function setFlags($flags) {
        $this->flags = $flags;
    }

    function getFlags() {
        return $this->flags;
    }    
    
    /**
     * @var string
     */
    protected $initCommand = false;

    /**
     * @param string $initCommand
     */
    function setInitCommand($initCommand) {
        $this->initCommand = $initCommand;
    }

    /**
     * @return string
     */
    function getInitCommand() {
        return $this->initCommand;
    }    
    
    function __construct($options = []) {
        if (is_array($options) && func_num_args() == 1) { // Ac_Prototyped style init
            $proto = $options;
            parent::initFromPrototype($proto);
        } elseif ($options instanceof mysqli) {
            $this->setLink($options);
        } else {
            throw new Ac_E_InvalidCall("\$link must be an Array or mysqli instance; provided: ".Ac_E_InvalidCall::getType($options));
        }
        
        if (!$this->dialect) {
            $dialect = new Ac_Sql_Dialect_Mysql;
            $this->setDialect($dialect);
        }
    }
    
    function setLink(mysqli $link) {
        if ($link !== ($oldLink = $this->link)) {
            $this->link = $link;
        }
    }

    /**
     * @return mysqli
     */
    function getLink($dontConnect = false) {
        if (!$this->link && !$dontConnect) {
            $this->link = mysqli_init();
            $host = $this->host;
            $socket = null;
            if (preg_match("/\.sock$/", $host)) {
                $socket = preg_replace("#^(.*:)/#", "/", $host);
                $host = preg_replace("#:/.*$#", "", $host);
            }
            $this->host = 'p:localhost';
            $this->socket = '/var/run/mysqld/mysqld.sock';
            $res = mysqli_real_connect(
                $this->link, $host, $this->username,
                $this->password, $this->dbName, $this->port, $socket,
                $this->flags
            );
            if (!$res) {
                throw new Ac_E_Database("Cannot connect to mysql: '".mysqli_error($this->link), mysqli_errno($this->link));
            }
            $this->runInitCommands();
        }
        return $this->link;
    }
    
    function setDbPrefix($dbPrefix) {
        if ($dbPrefix !== ($oldDbPrefix = $this->dbPrefix)) {
            $this->dbPrefix = $dbPrefix;
        }
    }

    function getDbPrefix() {
        return $this->dbPrefix;
    }    
    
    protected function implValueQuote($value) {
        return "'".mysqli_escape_string($this->getLink(), $value)."'";
    }
    
    protected function implNameQuote($name) {
        return $this->dialect->nameQuote($name);
    }
    
    protected function implNameUnquote($name) {
        return $this->dialect->nameUnquote($name);
    }
    
    protected function implIsNameQuoted($name) {
        return $this->dialect->isNameQuoted($name);
    }
    
    protected function implConcatNames($quotedNames) {
        return implode('.', $quotedNames);
    }
    
    function fetchArray($query, $keyColumn = false, $withNumericKeys = false) {
        $res = array();
        $key = -1;
        $q = $this->queryDb($query);
        foreach ($q->fetch_all($withNumericKeys? MYSQLI_BOTH : MYSQLI_ASSOC) as $row) {
            $r = $row;
            if ($withNumericKeys) $r = array_merge($r, array_values($row));
            if ($keyColumn !== false && !is_array($keyColumn)) $key = $r[$keyColumn];
                else $key++;
            $res[$key] = $r;
        }
        if (is_array($keyColumn)) $res = $this->indexRows ($res, $keyColumn);
        return $res;
    }
    
    function fetchObjects($query, $keyColumn = false) {
        $res = array();
        $kc = false;
        if (is_array($keyColumn)) {
            $kc = $keyColumn;
            $keyColumn = false;
        }
        foreach ($this->fetchArray($query, $keyColumn) as $key => $row) {
            $o = new stdClass();
            foreach ($row as $k => $v) $o->$k = $v;
            $res[$key] = $o;
        }
        if ($kc) $res = $this->indexRows($res, is_array($keyColumn)? $keyColumn : array($keyColumn, true));
        return $res;
    }

    function fetchRow($query, $key = false, $keyColumn = false, $withNumericKeys = false, $default = false) {
        $res = $default; 
        $k = -1;
        $stmt = $this->queryDb($query);
        if (is_array($keyColumn)) throw new Exception("Array \$keyColumn is not supported by ".__METHOD__);
        while ($row = $stmt->fetch_array($withNumericKeys? MYSQLI_BOTH : MYSQLI_ASSOC)) {
            $r = $row;
            if ($keyColumn !== false) $k = $r[$keyColumn];
                else $k++;
            if ($key === false || $k == $key) {
                $res = $r;
                break;
            }
        }
        $stmt->free();
        return $res;
    }
    
    function fetchColumn($query, $colNo = 0, $keyColumn = false) {
        $res = array();
        $key = -1;
        $withNumericKeys = (is_numeric($colNo) && ($keyColumn === false || is_numeric($keyColumn)));
        if (is_array($keyColumn)) {
            $res = $this->indexRows($this->fetchArray($query), $keyColumn, $colNo);
        } else {
            $stmt = $this->queryDb($query);
            while ($r = $stmt->fetch_array($withNumericKeys? MYSQLI_BOTH : MYSQLI_ASSOC)) {
                if ($keyColumn !== false) $key = $r[$keyColumn];
                    else $key++;
                $res[$key] = $r[$colNo];
            }
        }
        return $res;
    }
    
    function fetchValue($query, $colNo = 0, $default = null) {
        $res = $default;
        $stmt = $this->queryDb($query);
        $row = $stmt->fetch_array(is_numeric($colNo)? MYSQLI_NUM : MYSQLI_ASSOC);
        if (!$row || !array_key_exists($colNo, $row)) $res = $default;
          else $res = $row[$colNo];
        $stmt->free();
        return $res;
    }
    
    function query($query) {
        return $this->queryDb($query, true);
    }
    
    /**
     * @return mysqli_result
     * @throws Ac_E_Database
     */
    protected function queryDb($query) {
        $query = $this->intPreProcessQuery($query);
        $this->affectedRows = false;
        $res = mysqli_query($this->getLink(), $query);
        if ($res === false) {
            throw new Ac_E_Database('Mysqli exception: '.mysqli_error($this->link)
                .', SQL is '.$query, mysqli_errno($this->link));
        }
        if ($res === true) $this->affectedRows = mysqli_affected_rows($this->link);
        return $res;
    }
    
    function getErrorCode() {
        return mysqli_errno($this->link);
    }
    
    function getErrorDescr() {
        return mysqli_error($this->link);
    }

    function getLastInsertId() {
        return mysqli_insert_id($this->link);
    }
    
    function getIfnullFunction() {
        return $this->dialect->getIfnullFunction();
    }
    
    function setDialect(Ac_Sql_Dialect $dialect) {
        if ($this->dialect !== false) throw new Exception("Can setDialect() only once!");
        if (!$dialect instanceof Ac_Sql_Dialect_Mysql) {
            throw Ac_E_InvalidCall::wrongClass('dialect', $dialect, 'Ac_Sql_Dialect_Mysql');
        }
        $this->dialect = $dialect;
        if ($this->dbName && $this->getDialect()) $this->setDbName ($this->dbName);
    }

    /**
     * @return Ac_Sql_Dialect
     */
    function getDialect() {
        return $this->dialect;
    }
    
    /**
     * @return mysqli_result
     */
    function getResultResource($query) {
        return mysqli_query($this->getLink(), $this->preProcessQuery($query));
    }

    function resultGetFieldsInfo($resultResource) {
        $res = [];
        $resultResource->field_seek(0);
        while($col = $resultResource->fetch_field()) {
            $res[] = [$col['table'], $col['name']];
        }
        return $res;
    }
    
    /**
     * @param mysqli_result $resultResource
     */
    function resultFetchAssocByTables($resultResource, array & $fieldsInfo = null) {
        $row = $resultResource->fetch_array(MYSQLI_NUM);
        if (!$row) return false;
        if (!$fieldsInfo) $fieldsInfo = $this->getFieldsInfo($resultResource);
        foreach ($fieldsInfo as $i => $fi) {
            $res[$fi[0]][$fi[1]] = $row[$i];
        }
        return $res;
    }
    
    function getFieldsInfo($resultResource) {
        $res = [];
        foreach($resultResource->fetch_fields() as $fieldInfo) {
            $res[] = [$fieldInfo->table, $fieldInfo->name];
        }
        return $res;
    }
    
    /**
     * @param mysqli_result $resultResource
     */
    function resultFetchAssoc($resultResource) {
        $res = $resultResource->fetch(MYSQLI_ASSOC);
        return $res;
    }
    
    /**
     * @param mysqli_result $resultResource
     */
    function resultFreeResource($resultResource) {
        $resultResource->free();
    }
    
    function getAffectedRows() {
        return $this->affectedRows;
    }
    
    function __clone() {
        $this->link = null;
    }
    
}