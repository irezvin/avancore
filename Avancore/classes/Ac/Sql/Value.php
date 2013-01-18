<?php

class Ac_Sql_Value extends Ac_Sql_Expression {
    
    /**
     * @param Ac_Legacy_Database|Ac_Sql_Db $db
     */
    function getExpression($db) {
        return $db->quote($this->expression);
    }
    
    /**
     * @param Ac_Legacy_Database|Ac_Sql_Db $db
     */
    function nameQuote($db) {
        return $db->quote($this->expression);
    }
    
}