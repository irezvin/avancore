<?php

class Ac_Sql_Db_Pdo extends Ac_Sql_Db {
    
    /**
     * @var PDO
     */
    protected $pdo = false;
    
    /**
     * @var Ac_Sql_Dialect
     */
    protected $dialect = false;
    
    protected $dbPrefix = false;
    
    protected $returnsLastInsertId = false;
    
    protected $throwExceptions = true;
    
    protected $affectedRows = false;
    
    function __construct($pdo, Ac_Sql_Dialect $dialect = null) {
        
        if (is_array($pdo) && array_key_exists('pdo', $pdo) && func_num_args() == 1) { // Ac_Prototyped style init
            $proto = $pdo;
            parent::initFromPrototype($proto);
        } else {
            $this->setPdo($pdo);
            $this->dialect = $dialect;
        }
        
        if (is_null($dialect)) {
            $driverName = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);            
            $map = array(
                'mysql' => 'Ac_Sql_Dialect_Mysql',
                'dblib' => 'Ac_Sql_Dialect_Mssql',
                'sqlsrc' => 'Ac_Sql_Dialect_Mysql',
                'pgsql' => 'Ac_Sql_Dialect_Pgsql',
            );
            if (isset($map[$driverName])) {
                $c = $map[$driverName];
                $dialect = new $c;
                $this->setDialect($dialect);
            }
        }
    }

    function setDbPrefix($dbPrefix) {
        if (strlen($this->dbPrefix) && $dbPrefix !== $this->dbPrefix) 
            throw new Ac_E_InvalidUsage("Ac_Sql_Db_Pdo:: can \$setDbPrefix() only once");
        
        if ($dbPrefix !== ($oldDbPrefix = $this->dbPrefix)) {
            $this->dbPrefix = $dbPrefix;
        }
    }

    function getDbPrefix() {
        return $this->dbPrefix;
    }    
    
    function getDbName() {
        return $this->fetchValue("SELECT DATABASE()");
    }
    
    function setDbName($dbName) {
        return $this->query("USE ".$this->n($dbName));
    }
    
    protected function setPdo($pdo) {
        if (is_array($pdo)) {
            $pdoParams = $pdo;
            $rc = new ReflectionClass('PDO');
            $params = array();
            $badParams = array_diff(array_keys($pdoParams), array('dsn', 'username', 'password', 'driver_options'));
            if ($badParams) throw new Exception("Disallowed key(s) '".implode("', '", $badParams)
                    ."' in array argument \$pdo,  accepted keys are: 'dsn', 'username', 'password', 'driver_options'");
            if (isset($pdoParams['dsn'])) $args[] = $pdoParams['dsn'];
                else throw new Exception ("'dsn' key must be proveded in array \$pdo argument");
            foreach (array('username', 'password', 'driver_options') as $i => $k) {
                    if (isset($pdoParams[$k])) {
                        while (count($args) < $i + 1) $args[] = null;
                        $args[] = $pdoParams[$k]; 
                    }
            }
            $pdo = $rc->newInstanceArgs($args);
        } else {
            if (!$pdo instanceof PDO) throw new Exception("Only array or PDO instance are accepted as \$pdo argument");
        }
        $this->pdo = $pdo;
    }
    
    /**
     * @return PDO
     */
    function getPdo() {
        return $this->pdo;
    }
    
    protected function implValueQuote($value) {
        return $this->pdo->quote($value);
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
        $q = $this->queryPdo($query);
        foreach ($q->fetchAll($withNumericKeys? PDO::FETCH_BOTH : PDO::FETCH_ASSOC) as $row) {
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
        $stmt = $this->queryPdo($query);
        if (is_array($keyColumn)) throw new Exception("Array \$keyColumn is not supported by ".__METHOD__);
        while ($row = $stmt->fetch($withNumericKeys? PDO::FETCH_BOTH : PDO::FETCH_ASSOC)) {
            $r = $row;
            if ($keyColumn !== false) $k = $r[$keyColumn];
                else $k++;
            if ($key === false || $k == $key) {
                $res = $r;
                break;
            }
        }
        $stmt->closeCursor();
        return $res;
    }
    
    function fetchColumn($query, $colNo = 0, $keyColumn = false) {
        $res = array();
        $key = -1;
        $withNumericKeys = (is_numeric($colNo) && ($keyColumn === false || is_numeric($keyColumn)));
        if (is_array($keyColumn)) {
            $res = $this->indexRows($this->fetchArray($query), $keyColumn, $colNo);
        } else {
            foreach ($this->queryPdo($query) as $row) {
                $r = $row;
                if ($withNumericKeys) $r = array_merge($r, array_values($row));
                if ($keyColumn !== false) $key = $r[$keyColumn];
                    else $key++;
                $res[$key] = $r[$colNo];
            }
        }
        return $res;
    }
    
    function fetchValue($query, $colNo = 0, $default = null) {
        $res = $default;
        if (is_numeric($colNo)) {
            $stmt = $this->queryPdo($query);
            $res = $stmt->fetchColumn($colNo);
            $stmt->closeCursor();
        } else {
            if (is_array($row = $this->fetchRow($query))) $res = $row[$colNo];
        }
        return $res;
    }
    
    function query($query) {
        return $this->queryPdo($query, true);
    }
    
    /**
     * @return PDOStatement
     * @throws Ac_E_Database
     */
    protected function queryPdo($query, $exec = false) {
        $query = $this->intPreProcessQuery($query);
        $x = $exec? "exec" : "query";
        $this->affectedRows = false;
        $query = $this->replacePrefix($query);
        if ($this->throwExceptions) {
            try {
                $res = $this->pdo->$x($query);
            } catch (PDOException $e) {
                throw new Ac_E_Database('PDO Exception: '.$e->getMessage().', SQL is '.$query, $e->getCode(), $e);
            }
        } else {
            $res = $this->pdo->$x($query);
        }
        if (!$exec && $res instanceof PDOStatement) $this->affectedRows = $res->rowCount();
        elseif ($exec) $this->affectedRows = $res;
        return $res;
    }
    
    function getErrorCode() {
        return $this->pdo->errorCode();
    }
    
    function getErrorDescr() {
        return $this->pdo->errorInfo();
    }

    function getLastInsertId() {
        if ($this->returnsLastInsertId) return $this->dialect->getLastInsertId($this);
        return $this->pdo->lastInsertId();
    }
    
    function getIfnullFunction() {
        return $this->dialect->getIfnullFunction();
    }
    
    function setDialect(Ac_Sql_Dialect $dialect) {
        if ($this->dialect !== false) throw new Exception("Can setDialect() only once!");
        $this->dialect = $dialect;
        $this->returnsLastInsertId = $dialect->returnsLastInsertId();
    }

    /**
     * @return Ac_Sql_Dialect
     */
    function getDialect() {
        return $this->dialect;
    }
    
    /**
     * @return PDOStatement
     */
    function getResultResource($query) {
        return $this->queryPdo($query);
    }

    /**
     * @param PDOStatement $resultResource
     */
    function resultGetFieldsInfo($resultResource) {
        $res = array();
        $c = $resultResource->columnCount();
        for ($i = 0; $i < $c; $i++) {
            $col = $resultResource->getColumnMeta($i);
            $res[] = array($col['table'], $col['name']);
        }
        return $res;
    }
    
    /**
     * @param PDOStatement $resultResource
     */
    function resultFetchAssocByTables($resultResource, array & $fieldsInfo = array()) {
        $row = $resultResource->fetch(PDO::FETCH_NUM);
        if ($row === false) $res = $row;
        else {
            if (!$fieldsInfo) $fieldsInfo = $this->getFieldsInfo($resultResource);
            foreach ($fieldsInfo as $i => $fi) {
                $res[$fi[0]][$fi[1]] = $row[$i];
            }
        }
        return $res;
    }
    
    /**
     * @param PDOStatement $resultResource
     */
    function resultFetchAssoc($resultResource) {
        $res = $resultResource->fetch(PDO::FETCH_ASSOC);
        return $res;
    }
    
    /**
     * @param PDOStatement $resultResource
     */
    function resultFreeResource($resultResource) {
    }
    
    function getAffectedRows() {
        return $this->affectedRows;
    }
    
    
}