<?php

class Ac_Sql_Db_Pdo extends Ac_Sql_Db {
    
    /**
     * @var PDO
     */
    protected $pdo = false;
    
    /**
     * @var Ac_Sql_Db_Quoter
     */
    protected $quoter = false;
    
    function __construct($pdo, Ac_Sql_Quoter $quoter = null) {
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
        if (is_null($quoter)) $quoter = new Ac_Sql_Db_Quoter();
        $this->quoter = $quoter;
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
        return $this->quoter->nameQuote($name);
    }
    
    protected function implNameUnquote($name) {
        return $this->quoter->nameUnquote($name);
    }
    
    protected function implIsNameQuoted($name) {
        return $this->quoter->isNameQuoted($name);
    }
    
    protected function implConcatNames($quotedNames) {
        return implode('.', $quotedNames);
    }
    
    function fetchArray($query, $keyColumn = false, $withNumericKeys = false) {
        $res = array();
        $key = -1;
        $q = $this->pdo->query($query);
        foreach ($q->fetchAll($withNumericKeys? PDO::FETCH_BOTH : PDO::FETCH_ASSOC) as $row) {
            $r = $row;
            if ($withNumericKeys) $r = array_merge($r, array_values($row));
            if ($keyColumn !== false) $key = $r[$keyColumn];
                else $key++;
            $res[$key] = $r;
        }
        return $res;
    }
    
    function fetchObjects($query, $keyColumn = false) {
        $res = array();
        foreach ($this->fetchArray($query, $keyColumn) as $key => $row) {
            $o = new stdClass();
            foreach ($row as $k => $v) $o->$k = $v;
            $res[$key] = $o;
        }
        return $res;
    }

    function fetchRow($query, $key = false, $keyColumn = false, $withNumericKeys = false, $default = false) {
        $res = $default; 
        $k = -1;
        foreach (($stmt = $this->pdo->query($query)) as $row) {
            $r = $row;
            if ($withNumericKeys) $r = array_merge($r, array_values($row));
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
        foreach ($this->pdo->query($query) as $row) {
            $r = $row;
            if ($withNumericKeys) $r = array_merge($r, array_values($row));
            if ($keyColumn !== false) $key = $r[$keyColumn];
                else $key++;
            $res[$key] = $r[$colNo];
        }
        return $res;
    }
    
    function fetchValue($query, $colNo = 0, $default = null) {
        $res = $default;
        if (is_numeric($colNo)) {
            $stmt = $this->pdo->query($query);
            $res = $stmt->fetchColumn($colNo);
            $stmt->closeCursor();
        } else {
            if (is_array($row = $this->fetchRow($query))) $res = $row[$colNo];
        }
        return $res;
    }
    
    function query($query) {
        return $this->pdo->exec($query);
    }
    
    function getErrorCode() {
        return $this->pdo->errorCode();
    }
    
    function getErrorDescr() {
        return $this->pdo->errorInfo();
    }

    function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    function getIfnullFunction() {
        return $this->quoter->getIfnullFunction();
    }
    
    function setQuoter(Ac_Sql_Quoter $quoter) {
        if ($this->quoter !== false) throw new Exception("Can setQuoter() only once!");
        $this->quoter = $quoter;
    }

    /**
     * @return Ac_Sql_Quoter
     */
    function getQuoter() {
        return $this->quoter;
    }
    
    
}