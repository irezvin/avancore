<?php

class Ac_Sql_Expression_Column implements Ac_I_Sql_Expression {
    
    var $colName = '';
    
    var $alias = '';
    
    function getExpression($db) {
        return $this->nameQuote($db);
    }
    
    function nameQuote($db) {
        $c = [$this->colName];
        if ($this->alias) array_unshift ($c, $this->alias);
        return $db->nameQuote($c);
    }
    
}