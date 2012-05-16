<?php

class Ae_Sql_Db_Pdo extends Ae_Sql_Db {
    
    /**
     * @var PDO
     */
    protected $pdo = false;
    
    /**
     * @var Ae_Sql_Db_Quoter
     */
    protected $quoter = false;
    
    protected $nameQuoteChar = '"';
    
    function __construct(PDO $pdo, Ae_Sql_Db_Quoter $quoter = null) {
        $this->pdo = $pdo;
        if (is_null($quoter)) $quoter = new Ae_Sql_Db_Quoter();
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
        foreach ($this->pdo->query($statement) as $row) {
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
        
    }
    
    abstract function fetchColumn($query, $colNo = 0, $keyColumn = false);
    
    abstract function fetchValue($query, $colNo = 0, $default = null);
    
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
    
    
}