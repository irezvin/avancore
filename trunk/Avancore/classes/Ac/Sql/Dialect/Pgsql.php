<?php

class Ac_Sql_Dialect_Pgsql extends Ac_Sql_Dialect_Mysql {

    protected $nameQuoteChar = '"';    
    
    protected $inspectorClass = 'Ac_Sql_Dbi_Inspector_PgSql';
    
    function getLimitsClause($count, $offset = false, $withLimitKeyword = true) {
        $l = $withLimitKeyword? " LIMIT " : "";
        $res = $l.intval($count);
        if ($offset !== false)  $res .= " OFFSET ".intval($offset);
        return $res;
    }
    
}